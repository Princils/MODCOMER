using System.Security.Cryptography;
using System.Text;
using System.Text.Json;
using Microsoft.Extensions.Hosting.WindowsServices;
using Modcomercial;

internal static class Program
{
    [STAThread]
    static void Main(string[] args)
    {
        if (args.Contains("--status"))
        {
            ApplicationConfiguration.Initialize();
            Application.Run(new MonitorForm());
            return;
        }
        if (args.Contains("--configure"))
        {
            ApplicationConfiguration.Initialize();
            Application.Run(new ConfigurationForm());
            return;
        }
        if (args.Length == 2 && args[0] == "--import")
        {
            Settings.Save(JsonSerializer.Deserialize<Settings>(args[1] == "-" ? Console.In.ReadToEnd() : File.ReadAllText(args[1]), Settings.Json)!);
            return;
        }
        if (args.Length == 3 && args[0] == "--sign")
        {
            using var rsa = RSA.Create(3072);
            string key = args[2];
            if (File.Exists(key)) rsa.ImportFromPem(File.ReadAllText(key));
            else File.WriteAllText(key, rsa.ExportPkcs8PrivateKeyPem());
            byte[] payload = File.ReadAllBytes(args[1]);
            File.WriteAllText(args[1] + ".signed.json", JsonSerializer.Serialize(new {
                payload = Convert.ToBase64String(payload),
                signature = Convert.ToBase64String(rsa.SignData(payload, HashAlgorithmName.SHA256, RSASignaturePadding.Pss))
            }));
            File.WriteAllText(Path.Combine(Path.GetDirectoryName(key)!, "report-public.pem"), rsa.ExportSubjectPublicKeyInfoPem());
            return;
        }
        if (args.Contains("--self-test"))
        {
            SelfTests.Run();
            return;
        }
        var builder = WebApplication.CreateBuilder(new WebApplicationOptions {
            Args = args, ContentRootPath = AppContext.BaseDirectory
        });
        builder.Host.UseWindowsService(options => options.ServiceName = "MODCOMERCIALConnector");
        builder.WebHost.UseUrls("http://127.0.0.1:17643");
        builder.Logging.ClearProviders();
        builder.Services.AddSingleton<ConnectorState>();
        builder.Services.AddHostedService<ConnectorWorker>();
        var app = builder.Build();
        app.MapGet("/status", (ConnectorState state) => Results.Json(state.Snapshot()));
        app.Run();
    }
}
