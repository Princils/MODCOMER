[Setup]
AppId={{98CF7BC6-25BA-4ED4-A370-837F90DF5D11}
AppName=MODCOMERCIAL Conector
AppVersion=0.1.2
AppPublisher=Soluciones COPROI
DefaultDirName={autopf}\MODCOMERCIAL Connector
DefaultGroupName=MODCOMERCIAL
OutputDir=dist
OutputBaseFilename=MODCOMERCIAL-Conector-Setup
Compression=lzma2
SolidCompression=yes
PrivilegesRequired=admin
ArchitecturesAllowed=x64compatible
ArchitecturesInstallIn64BitMode=x64compatible
UninstallDisplayIcon={app}\Modcomercial.Connector.exe
CloseApplications=yes

[Languages]
Name: "spanish"; MessagesFile: "compiler:Languages\Spanish.isl"

[Files]
Source: "publish\*"; DestDir: "{app}"; Flags: ignoreversion recursesubdirs createallsubdirs

[Dirs]
Name: "{commonappdata}\MODCOMERCIAL\Connector"

[Icons]
Name: "{group}\Estado del conector"; Filename: "{app}\Modcomercial.Connector.exe"; Parameters: "--status"
Name: "{commondesktop}\MODCOMERCIAL Conector"; Filename: "{app}\Modcomercial.Connector.exe"; Parameters: "--status"

[Run]
Filename: "{app}\Modcomercial.Connector.exe"; Parameters: "--status"; Description: "Abrir estado del conector"; Flags: postinstall nowait skipifsilent runasoriginaluser

[Code]
function PrepareToInstall(var NeedsRestart: Boolean): String;
var ResultCode: Integer;
begin
  Result := '';
  if not Exec(ExpandConstant('{sys}\WindowsPowerShell\v1.0\powershell.exe'), '-NoProfile -Command "$ErrorActionPreference = ''Stop''; $s = Get-Service MODCOMERCIALConnector -ErrorAction SilentlyContinue; if ($s) { Stop-Service MODCOMERCIALConnector -Force; $s.WaitForStatus(''Stopped'', [TimeSpan]::FromSeconds(30)) }"', '', SW_HIDE, ewWaitUntilTerminated, ResultCode) or (ResultCode <> 0) then
    Result := 'No se pudo detener el servicio anterior para actualizarlo.';
end;

procedure CurStepChanged(CurStep: TSetupStep);
var ResultCode: Integer;
begin
  if CurStep = ssPostInstall then begin
    if not Exec(ExpandConstant('{sys}\WindowsPowerShell\v1.0\powershell.exe'), '-NoProfile -ExecutionPolicy Bypass -File "' + ExpandConstant('{app}\install-service.ps1') + '"', '', SW_HIDE, ewWaitUntilTerminated, ResultCode) or (ResultCode <> 0) then
      RaiseException('No se pudo iniciar el servicio. Revisa los permisos de administrador y el registro de instalación.');
  end;
end;
[UninstallRun]
Filename: "{sys}\sc.exe"; Parameters: "stop MODCOMERCIALConnector"; Flags: runhidden waituntilterminated; RunOnceId: "StopService"
Filename: "{sys}\sc.exe"; Parameters: "delete MODCOMERCIALConnector"; Flags: runhidden waituntilterminated; RunOnceId: "DeleteService"
