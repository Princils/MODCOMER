$ErrorActionPreference = 'Stop'
$serviceName = 'MODCOMERCIALConnector'
$executable = Join-Path $PSScriptRoot 'Modcomercial.Connector.exe'
$dataPath = Join-Path $env:ProgramData 'MODCOMERCIAL\Connector'
New-Item -ItemType Directory -Force -Path $dataPath | Out-Null
& icacls.exe $dataPath /inheritance:r /grant:r '*S-1-5-18:(OI)(CI)F' '*S-1-5-32-544:(OI)(CI)F' '*S-1-5-19:(OI)(CI)M' | Out-Null
if ($LASTEXITCODE -ne 0) { throw 'No se pudieron proteger los archivos de configuración.' }
$service = Get-Service $serviceName -ErrorAction SilentlyContinue
if ($service) {
    Stop-Service $serviceName -Force
    $service.WaitForStatus('Stopped', [TimeSpan]::FromSeconds(30))
} else {
    New-Service -Name $serviceName -BinaryPathName ('"' + $executable + '"') -DisplayName 'MODCOMERCIAL Connector' -StartupType Automatic | Out-Null
}
& sc.exe config $serviceName obj= 'NT AUTHORITY\LocalService' start= delayed-auto | Out-Null
if ($LASTEXITCODE -ne 0) { throw 'No se pudo configurar la identidad del servicio.' }
& sc.exe failure $serviceName reset= 86400 actions= restart/10000/restart/30000/restart/60000 | Out-Null
Start-Service $serviceName
(Get-Service $serviceName).WaitForStatus('Running', [TimeSpan]::FromSeconds(30))
