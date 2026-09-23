using System.Net;
namespace Modcomercial;
internal static class TransportTests
{
    internal static void Run()
    {
        const string challenge="<script>document.cookie = \"humans_21909=1\"; document.location.reload(true)</script>";
        var settings=new Settings { Endpoint="https://a.example/api.php",Token="test-token" };
        int calls=0;
        using var transport=new PlatformTransport(new Stub(request=>{
            calls++;
            if(request.Headers.Authorization?.Parameter!="test-token" || request.Content?.Headers.ContentLength is null)throw new Exception("Token o Content-Length ausente");
            bool cookie=request.Headers.Contains("Cookie");
            if(calls==1){if(cookie)throw new Exception("Cookie prematura");return Reply(HttpStatusCode.Conflict,challenge);}
            if(calls<=3 && !cookie)throw new Exception("No conserva cookie");
            if(calls==4 && cookie)throw new Exception("Cookie filtrada a otro origen");
            return Reply(HttpStatusCode.OK,"{\"ok\":true}");
        }));
        transport.Request(settings,"package",new {},default).GetAwaiter().GetResult();
        transport.Request(settings,"package",new {},default).GetAwaiter().GetResult();
        settings.Endpoint="https://b.example/api.php";
        transport.Request(settings,"package",new {},default).GetAwaiter().GetResult();
        foreach(var scenario in new[]{(challenge,2),("{\"error\":\"conflicto real\"}",1)}) {
            int count=0;
            using var failing=new PlatformTransport(new Stub(_=>{count++;return Reply(HttpStatusCode.Conflict,scenario.Item1);}));
            try {failing.Request(settings,"claim",new {},default).GetAwaiter().GetResult();throw new Exception("409 aceptado");}
            catch(HttpRequestException ex) when(ex.StatusCode==HttpStatusCode.Conflict) {}
            if(count!=scenario.Item2)throw new Exception("Numero incorrecto de reintentos");
        }
    }
    static HttpResponseMessage Reply(HttpStatusCode status,string body)=>new(status){Content=new StringContent(body)};
    sealed class Stub(Func<HttpRequestMessage,HttpResponseMessage> send):HttpMessageHandler
    {
        protected override Task<HttpResponseMessage> SendAsync(HttpRequestMessage request,CancellationToken cancellationToken)=>Task.FromResult(send(request));
    }
}
