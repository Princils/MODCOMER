using System.Net;
using System.Net.Http.Headers;
using System.Text;
using System.Text.Json;

namespace Modcomercial;

// Compatibilidad limitada con el aviso de cookie del hosting; no ejecuta JavaScript.
internal sealed class PlatformTransport : IDisposable
{
    private readonly HttpClient http;
    private readonly HashSet<string> confirmedOrigins = new(StringComparer.OrdinalIgnoreCase);
    public PlatformTransport(HttpMessageHandler? handler = null) => http = new(handler ??
        new HttpClientHandler { AllowAutoRedirect = false, UseCookies = false }) { Timeout = TimeSpan.FromSeconds(20) };

    public async Task<JsonElement> Request(Settings settings, string action, object body, CancellationToken ct)
    {
        var uri = new Uri(settings.Endpoint + "?action=" + action);
        string origin = uri.GetLeftPart(UriPartial.Authority);
        string payload = JsonSerializer.Serialize(body);
        for (int attempt = 0; attempt < 2; attempt++) {
            using var request = new HttpRequestMessage(HttpMethod.Post, uri);
            request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", settings.Token);
            if (confirmedOrigins.Contains(origin)) request.Headers.Add("Cookie", "humans_21909=1");
            // Longitud definida: ModSecurity puede rechazar envíos chunked.
            request.Content = new StringContent(payload, Encoding.UTF8, "application/json");
            using var response = await http.SendAsync(request, ct);
            string content = await response.Content.ReadAsStringAsync(ct);
            const string challenge = "<script>document.cookie = \"humans_21909=1\"; document.location.reload(true)</script>";
            if (attempt == 0 && uri.Scheme == "https" && response.StatusCode == HttpStatusCode.Conflict
                && content.Trim() == challenge) {
                confirmedOrigins.Add(origin);
                continue;
            }
            response.EnsureSuccessStatusCode();
            using var json = JsonDocument.Parse(content);
            return json.RootElement.Clone();
        }
        throw new InvalidOperationException("No se pudo completar la solicitud.");
    }
    public void Dispose() => http.Dispose();
}
