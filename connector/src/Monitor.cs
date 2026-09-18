using System.Diagnostics;
using System.Security.Principal;
using System.Text.Json;

namespace Modcomercial;

public sealed class MonitorForm : Form
{
    readonly Label headline = new() { Text = "Consultando servicio…", AutoSize = false, Height = 50, Dock = DockStyle.Top, Font = new Font("Segoe UI", 18, FontStyle.Bold) };
    readonly Label details = new() { Dock = DockStyle.Fill, Font = new Font("Segoe UI", 12), Padding = new Padding(0, 20, 0, 0) };
    readonly HttpClient http = new() { Timeout = TimeSpan.FromSeconds(2) };
    readonly System.Windows.Forms.Timer timer = new() { Interval = 3000 };
    readonly NotifyIcon tray = new() { Icon = SystemIcons.Application, Text = "MODCOMERCIAL · Conector", Visible = true };
    bool refreshing;
    public MonitorForm()
    {
        Text = "MODCOMERCIAL · Estado del conector";
        Size = new Size(640, 460); StartPosition = FormStartPosition.CenterScreen;
        BackColor = Color.FromArgb(242,247,249); ForeColor = Color.FromArgb(19,54,75); Padding = new Padding(26);
        var buttons = new FlowLayoutPanel { Dock = DockStyle.Bottom, Height = 65 };
        var configure = new Button { Text = "Configurar", Width = 150, Height = 38 };
        configure.Click += (_,_) => {
            try { Process.Start(new ProcessStartInfo(Environment.ProcessPath!, "--configure") { UseShellExecute = true, Verb = "runas" }); }
            catch { MessageBox.Show("La configuración requiere permisos de administrador."); }
        };
        var refresh = new Button { Text = "Actualizar estado", Width = 155, Height = 38 };
        refresh.Click += async (_,_) => await RefreshState();
        buttons.Controls.AddRange([refresh, configure]);
        Controls.Add(details); Controls.Add(headline); Controls.Add(buttons);
        tray.DoubleClick += (_,_) => { Show(); WindowState = FormWindowState.Normal; Activate(); };
        var menu = new ContextMenuStrip();
        menu.Items.Add("Abrir estado", null, (_,_)=> { Show(); Activate(); });
        menu.Items.Add("Cerrar monitor (el servicio sigue activo)",null,(_,_)=> Close());
        tray.ContextMenuStrip = menu;
        Resize += (_,_) => { if (WindowState == FormWindowState.Minimized) Hide(); };
        FormClosed += (_,_) => { timer.Stop(); tray.Dispose(); http.Dispose(); };
        timer.Tick += async (_,_) => await RefreshState();
        Shown += async (_,_) => { timer.Start(); await RefreshState(); };
    }
    async Task RefreshState()
    {
        if (refreshing) return;
        refreshing = true;
        try {
            using var doc = JsonDocument.Parse(await http.GetStringAsync("http://127.0.0.1:17643/status"));
            var s = doc.RootElement;
            headline.Text = "● Servicio activo";
            headline.ForeColor = Color.FromArgb(8,127,153);
            details.Text = $"Empresa: {s.GetProperty("company").GetString()}\n\nSQL Server: {s.GetProperty("sql").GetString()}\nPlataforma PHP: {s.GetProperty("cloud").GetString()}\n\n{s.GetProperty("detail").GetString()}\nÚltimo trabajo: {s.GetProperty("lastJob").GetString()}\nVersión: {s.GetProperty("version").GetString()}";
        } catch {
            headline.Text = "● Servicio no disponible";
            headline.ForeColor = Color.Firebrick;
            details.Text = "No se pudo contactar al servicio local.\nComprueba MODCOMERCIALConnector en Servicios de Windows.\n\nCerrar esta ventana no detiene el conector.";
        } finally { refreshing = false; }
    }
}
public sealed class ConfigurationForm : Form
{
    readonly Dictionary<string, TextBox> fields = new();
    readonly CheckBox integrated = new() { Text = "Autenticación Windows (identidad del servicio)", AutoSize = true };
    readonly CheckBox trust = new() { Text = "Confiar en certificado SQL local (solo entorno controlado)", AutoSize = true };
    public ConfigurationForm()
    {
        Text = "MODCOMERCIAL · Configuración"; Size = new Size(720, 610); StartPosition = FormStartPosition.CenterScreen;
        var panel = new TableLayoutPanel { Dock = DockStyle.Fill, Padding = new Padding(20), ColumnCount = 2, AutoScroll = true };
        panel.ColumnStyles.Add(new ColumnStyle(SizeType.Absolute,150)); panel.ColumnStyles.Add(new ColumnStyle(SizeType.Percent,100));
        Settings s = File.Exists(Settings.FilePath) ? Settings.Load() : new();
        foreach (var item in new[] { ("Endpoint","Dirección plataforma",s.Endpoint),("Company","Empresa",s.Company),("Token","Token de vinculación",s.Token),("Server","Servidor SQL",s.Server),("Database","Base de datos",s.Database),("User","Usuario SQL",s.User),("Password","Contraseña SQL",s.Password) }) {
            var input = new TextBox { Text = item.Item3, Dock = DockStyle.Top, UseSystemPasswordChar = item.Item1 is "Password" or "Token" };
            fields[item.Item1]=input; panel.Controls.Add(new Label { Text = item.Item2, AutoSize=true }); panel.Controls.Add(input);
        }
        integrated.Checked=s.IntegratedSecurity; trust.Checked=s.TrustServerCertificate;
        panel.Controls.Add(new Label()); panel.Controls.Add(integrated);
        panel.Controls.Add(new Label()); panel.Controls.Add(trust);
        var save = new Button { Text="Guardar configuración", Width=190, Height=38 };
        save.Click += (_,_) => {
            try {
                Settings.Save(new Settings { Endpoint=fields["Endpoint"].Text.Trim(), Company=fields["Company"].Text.Trim(), Token=fields["Token"].Text.Trim(),
                    Server=fields["Server"].Text.Trim(), Database=fields["Database"].Text.Trim(), User=fields["User"].Text,
                    Password=fields["Password"].Text, IntegratedSecurity=integrated.Checked, TrustServerCertificate=trust.Checked });
                MessageBox.Show("Configuración protegida. El servicio la tomará en su siguiente ciclo."); Close();
            } catch(Exception ex) { MessageBox.Show(ex is InvalidOperationException ? ex.Message : "No se pudo guardar. Abre Configurar como administrador."); }
        };
        panel.Controls.Add(new Label()); panel.Controls.Add(save); Controls.Add(panel);
    }
}

