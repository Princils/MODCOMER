using System.Security.Cryptography;
using System.Text;
using System.Text.Json;

namespace Modcomercial;

public sealed class Settings
{
    public string Endpoint { get; set; } = "http://localhost/CODIGOPCP/MODCOMER2/MODCOMCER/conector-demo/api.php";
    public string Token { get; set; } = "";
    public string Company { get; set; } = "demo";
    public string Server { get; set; } = "";
    public string Database { get; set; } = "";
    public string User { get; set; } = "";
    public string Password { get; set; } = "";
    public bool IntegratedSecurity { get; set; }
    public bool TrustServerCertificate { get; set; }
    public string[] AllowedReports { get; set; } = ["utilidad_documentos"];
    public static readonly JsonSerializerOptions Json = new() { PropertyNameCaseInsensitive = true, WriteIndented = true };
    public static string DataPath => Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.CommonApplicationData), "MODCOMERCIAL", "Connector");
    public static string FilePath => Path.Combine(DataPath, "settings.protected");
    public static void Validate(Settings s)
    {
        var uri = new Uri(s.Endpoint);
        if (uri.Scheme != "https" && !(uri.Scheme == "http" && uri.IsLoopback))
            throw new InvalidOperationException("Usa HTTPS. HTTP solo está permitido en la demostración local.");
        if (s.Token.Length < 32 || string.IsNullOrWhiteSpace(s.Server) || string.IsNullOrWhiteSpace(s.Database))
            throw new InvalidOperationException("Completa servidor, base y token de vinculación (mínimo 32 caracteres).");
    }
    public static Settings Load() => JsonSerializer.Deserialize<Settings>(
        ProtectedData.Unprotect(File.ReadAllBytes(FilePath), null, DataProtectionScope.LocalMachine), Json)!;
    public static void Save(Settings settings)
    {
        Validate(settings);
        Directory.CreateDirectory(DataPath);
        byte[] bytes = ProtectedData.Protect(JsonSerializer.SerializeToUtf8Bytes(settings), null, DataProtectionScope.LocalMachine);
        File.WriteAllBytes(FilePath + ".tmp", bytes);
        File.Move(FilePath + ".tmp", FilePath, true);
    }
}

public sealed class ConnectorState
{
    private readonly object gate = new();
    private string sql = "Pendiente", cloud = "Pendiente", detail = "Iniciando", lastJob = "Ninguno", company = "";
    private DateTimeOffset? lastContact;
    public void Set(string? sqlStatus = null, string? cloudStatus = null, string? message = null, string? job = null, string? tenant = null)
    {
        lock (gate) {
            if (sqlStatus != null) sql = sqlStatus;
            if (cloudStatus != null) { cloud = cloudStatus; if (cloudStatus == "Conectado") lastContact = DateTimeOffset.Now; }
            if (message != null) detail = message;
            if (job != null) lastJob = job;
            if (tenant != null) company = tenant;
        }
    }
    public object Snapshot() { lock (gate) return new { service = "Activo", version = "0.1.2", sql, cloud, detail, lastJob, company, lastContact }; }
}
