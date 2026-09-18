param([Parameter(Mandatory=$true)][string]$PrivateKeyPath)
$ErrorActionPreference='Stop'
Push-Location $PSScriptRoot
try {
    & dotnet publish src/Connector.csproj -c Release -r win-x64 --self-contained true -o publish
    if ($LASTEXITCODE -ne 0) { throw 'Falló la compilación. Instala el SDK .NET 10.' }
    Copy-Item -LiteralPath 'install-service.ps1' -Destination 'publish\install-service.ps1'
    $sign = Start-Process -FilePath "$PSScriptRoot\publish\Modcomercial.Connector.exe" -ArgumentList @('--sign',('"'+$PSScriptRoot+'\report.json"'),('"'+$PrivateKeyPath+'"')) -Wait -PassThru -WindowStyle Hidden
    if ($sign.ExitCode -ne 0) { throw 'No se pudo firmar el reporte.' }
    Copy-Item -LiteralPath (Join-Path (Split-Path $PrivateKeyPath) 'report-public.pem') -Destination 'publish\report-public.pem'
    & 'C:\Program Files (x86)\Inno Setup 6\ISCC.exe' /Qp installer.iss
    if ($LASTEXITCODE -ne 0) { throw 'Falló la generación del instalador.' }
} finally { Pop-Location }
