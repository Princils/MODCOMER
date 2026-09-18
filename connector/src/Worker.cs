using System.Data;
using System.Net.Http.Headers;
using System.Security.Cryptography;
using System.Text.Json;
using Microsoft.Data.SqlClient;

namespace Modcomercial;

public sealed class ReportPackage
{
    public string Id { get; set; } = "";
    public int Version { get; set; }
    public int Engine { get; set; }
    public string Sql { get; set; } = "";
}
public sealed class Job
{
    public string Id { get; set; } = "";
    public string Report { get; set; } = "";
    public string Lease { get; set; } = "";
    public string Company { get; set; } = "";
    public string Inicio { get; set; } = "";
    public string Fin { get; set; } = "";
}
public static class Packages
{
    public static ReportPackage Verify(string envelope, string publicKey)
    {
        using var doc = JsonDocument.Parse(envelope);
        var bytes = Convert.FromBase64String(doc.RootElement.GetProperty("payload").GetString()!);
        var signature = Convert.FromBase64String(doc.RootElement.GetProperty("signature").GetString()!);
        using var rsa = RSA.Create();
        rsa.ImportFromPem(publicKey);
        if (!rsa.VerifyData(bytes, signature, HashAlgorithmName.SHA256, RSASignaturePadding.Pss))
            throw new InvalidDataException("Firma de reporte inválida.");
        var report = JsonSerializer.Deserialize<ReportPackage>(bytes, Settings.Json)!;
        if (report.Engine != 1 || report.Version < 1 || !System.Text.RegularExpressions.Regex.IsMatch(report.Id, "^[a-z_]{1,64}$"))
            throw new InvalidDataException("Reporte incompatible.");
        return report;
    }
}
public sealed class ConnectorWorker(ConnectorState state) : BackgroundService
{
    private readonly HttpClient http = new(new HttpClientHandler { AllowAutoRedirect = false }) { Timeout = TimeSpan.FromSeconds(20) };
    protected override async Task ExecuteAsync(CancellationToken stop)
    {
        while (!stop.IsCancellationRequested)
        {
            try
            {
                if (!File.Exists(Settings.FilePath)) {
                    state.Set(message: "Falta configurar la conexión. Abre Configurar como administrador.", sqlStatus: "Sin configurar", cloudStatus: "Sin configurar");
                } else {
                    var settings = Settings.Load();
                    Settings.Validate(settings);
                    state.Set(tenant: settings.Company);
                    await Poll(settings, stop);
                }
            }
            catch (OperationCanceledException) when (stop.IsCancellationRequested) { break; }
            catch (Exception ex) {
                state.Set(message: ex is SqlException ? "No se pudo acceder a SQL Server. Revisa la configuración." : "No se pudo completar la comunicación. Se reintentará automáticamente.",
                    sqlStatus: ex is SqlException ? "Error de conexión" : null,
                    cloudStatus: ex is HttpRequestException || ex is TaskCanceledException ? "Sin conexión" : null);
            }
            try { await Task.Delay(TimeSpan.FromSeconds(5), stop); } catch (OperationCanceledException) { break; }
        }
    }
    private async Task<JsonElement> Request(Settings s, string action, object body, CancellationToken ct)
    {
        using var request = new HttpRequestMessage(HttpMethod.Post, s.Endpoint + "?action=" + action);
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", s.Token);
        request.Content = JsonContent.Create(body);
        using var response = await http.SendAsync(request, ct);
        response.EnsureSuccessStatusCode();
        using var json = JsonDocument.Parse(await response.Content.ReadAsStringAsync(ct));
        return json.RootElement.Clone();
    }
    private static SqlConnection Connection(Settings s) => new(new SqlConnectionStringBuilder {
        DataSource = s.Server, InitialCatalog = s.Database, UserID = s.IntegratedSecurity ? "" : s.User,
        Password = s.IntegratedSecurity ? "" : s.Password, IntegratedSecurity = s.IntegratedSecurity,
        Encrypt = SqlConnectionEncryptOption.Mandatory, TrustServerCertificate = s.TrustServerCertificate,
        ConnectTimeout = 8, ApplicationName = "MODCOMERCIAL Connector", Pooling = true
    }.ConnectionString);
    private async Task Poll(Settings s, CancellationToken ct)
    {
        // Entregas persistentes: si se cae Internet, se conserva el resultado protegido y se reintenta.
        string pending = Path.Combine(Settings.DataPath, "pending.protected");
        if (File.Exists(pending)) {
            var data = ProtectedData.Unprotect(File.ReadAllBytes(pending), null, DataProtectionScope.LocalMachine);
            using var doc = JsonDocument.Parse(data);
            await Request(s, "complete", doc.RootElement, ct);
            File.Delete(pending);
        }
        var response = await Request(s, "claim", new { company = s.Company, version = "0.1.0" }, ct);
        state.Set(cloudStatus: "Conectado", message: "Esperando reportes");
        await using var connection = Connection(s);
        await connection.OpenAsync(ct);
        state.Set(sqlStatus: "Conectado");
        if (response.GetProperty("job").ValueKind == JsonValueKind.Null) return;
        var job = response.GetProperty("job").Deserialize<Job>(Settings.Json)!;
        if (job.Company != s.Company || !s.AllowedReports.Contains(job.Report)) throw new InvalidDataException("Trabajo no autorizado.");
        object result;
        try {
            var packageJson = await Request(s, "package", new { report = job.Report }, ct);
            var pack = Packages.Verify(packageJson.GetRawText(), File.ReadAllText(Path.Combine(AppContext.BaseDirectory, "report-public.pem")));
            if (pack.Id != job.Report) throw new InvalidDataException("El paquete no corresponde al reporte solicitado.");
            string packageFile = Path.Combine(Settings.DataPath, "report-" + pack.Id + ".json");
            if (File.Exists(packageFile)) {
                var previous = Packages.Verify(File.ReadAllText(packageFile), File.ReadAllText(Path.Combine(AppContext.BaseDirectory, "report-public.pem")));
                if (pack.Version < previous.Version) throw new InvalidDataException("No se admite una versión anterior del reporte.");
            }
            File.WriteAllText(packageFile + ".tmp", packageJson.GetRawText());
            File.Move(packageFile + ".tmp", packageFile, true);
            if (!DateTime.TryParseExact(job.Inicio, "yyyy-MM-dd", null, System.Globalization.DateTimeStyles.None, out var inicio)
                || !DateTime.TryParseExact(job.Fin, "yyyy-MM-dd", null, System.Globalization.DateTimeStyles.None, out var fin)
                || fin < inicio || (fin - inicio).TotalDays > 366)
                throw new InvalidDataException("Fechas inválidas.");
            state.Set(message: "Consultando Utilidad por Documentos", job: job.Id);
            await using var command = new SqlCommand(pack.Sql, connection) { CommandTimeout = 90 };
            command.Parameters.Add("@inicio", SqlDbType.Date).Value = inicio;
            command.Parameters.Add("@fin", SqlDbType.Date).Value = fin;
            var rows = new List<Dictionary<string, object?>>();
            await using var reader = await command.ExecuteReaderAsync(ct);
            while (await reader.ReadAsync(ct)) {
                if (rows.Count >= 20000) throw new InvalidOperationException("El resultado supera el límite de la demostración.");
                var row = new Dictionary<string, object?>();
                for (int i=0;i<reader.FieldCount;i++) row[reader.GetName(i)] = reader.IsDBNull(i) ? null : reader.GetValue(i);
                rows.Add(row);
            }
            result = new { id = job.Id, lease = job.Lease, company = s.Company, status = "completed", rows, packageVersion = pack.Version };
            state.Set(message: "Reporte terminado: " + rows.Count + " filas", job: job.Id);
        } catch (Exception ex) when (ex is not OperationCanceledException) {
            result = new { id = job.Id, lease = job.Lease, company = s.Company, status = "failed", error = ex is InvalidDataException ? ex.Message : "No se pudo ejecutar el reporte. Revisa el servicio y la base de datos." };
            state.Set(message: "Falló el reporte; consulta la página de demostración", job: job.Id);
        }
        File.WriteAllBytes(pending + ".tmp", ProtectedData.Protect(JsonSerializer.SerializeToUtf8Bytes(result), null, DataProtectionScope.LocalMachine));
        File.Move(pending + ".tmp", pending, true);
        await Request(s, "complete", result, ct);
        File.Delete(pending);
    }
}
