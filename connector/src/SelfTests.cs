using System.Security.Cryptography;
using System.Text;
using System.Text.Json;
namespace Modcomercial;
static class SelfTests
{
    public static void Run()
    {
        TransportTests.Run();
        using var rsa = RSA.Create(2048);
        var bytes = Encoding.UTF8.GetBytes("{\"id\":\"utilidad_documentos\",\"version\":1,\"engine\":1,\"sql\":\"SELECT 1\"}");
        string Envelope(byte[] data) => JsonSerializer.Serialize(new { payload=Convert.ToBase64String(data),signature=Convert.ToBase64String(rsa.SignData(bytes,HashAlgorithmName.SHA256,RSASignaturePadding.Pss)) });
        var pack=Packages.Verify(Envelope(bytes),rsa.ExportSubjectPublicKeyInfoPem());
        if(pack.Version!=1) throw new Exception("Paquete válido rechazado");
        bool rejected=false;
        try { Packages.Verify(Envelope(Encoding.UTF8.GetBytes("alterado")),rsa.ExportSubjectPublicKeyInfoPem()); }
        catch(InvalidDataException) { rejected=true; }
        if(!rejected) throw new Exception("Firma alterada aceptada");
        rejected=false;
        try { Settings.Validate(new Settings { Endpoint="http://example.com/api",Token=new string('x',32),Server="server",Database="db" }); }
        catch(InvalidOperationException) { rejected=true; }
        if(!rejected) throw new Exception("HTTP remoto aceptado");
        File.WriteAllText(Path.Combine(AppContext.BaseDirectory,"self-test-result.txt"),"OK: paquete firmado, rechazo de alteraciones y HTTPS remoto obligatorio.");
    }
}

