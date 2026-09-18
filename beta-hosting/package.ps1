$ErrorActionPreference='Stop'
$root=$PSScriptRoot
$dist=Join-Path $root 'dist'
New-Item -ItemType Directory -Force $dist | Out-Null
Set-Content -LiteralPath (Join-Path $dist '.htaccess') -Value 'Require all denied' -Encoding ASCII
$stage=Join-Path $dist ('package-'+[guid]::NewGuid().ToString('N'))
New-Item -ItemType Directory -Force (Join-Path $stage 'private') | Out-Null
Copy-Item -LiteralPath (Join-Path $root 'public') -Destination $stage -Recurse
foreach($name in @('.htaccess','bootstrap.php','layout.php','report.signed.json','config.example.php')) {
 Copy-Item -LiteralPath (Join-Path $root ('private/'+$name)) -Destination (Join-Path $stage 'private')
}
$bytes=New-Object byte[] 32
$rng=[Security.Cryptography.RandomNumberGenerator]::Create()
$rng.GetBytes($bytes);$rng.Dispose()
$key=([BitConverter]::ToString($bytes)).Replace('-','').ToLowerInvariant()
$config=(Get-Content (Join-Path $root 'private/config.example.php') -Raw).Replace('REEMPLAZAR_POR_UNA_CLAVE_ALEATORIA_DE_64_CARACTERES',$key)
[IO.File]::WriteAllText((Join-Path $stage 'private/config.local.php'),$config,(New-Object Text.UTF8Encoding($false)))
Copy-Item -LiteralPath (Join-Path $root 'INSTALACION.md') -Destination $stage
$zip=Join-Path $dist 'MODCOMERCIAL-Beta-Hosting.zip'
Add-Type -AssemblyName System.IO.Compression.FileSystem
if(Test-Path -LiteralPath $zip){Remove-Item -LiteralPath $zip}
[IO.Compression.ZipFile]::CreateFromDirectory($stage,$zip)
$resolvedStage=[IO.Path]::GetFullPath($stage)
if(!$resolvedStage.StartsWith([IO.Path]::GetFullPath($dist)+[IO.Path]::DirectorySeparatorChar)){throw 'Ruta de staging no valida'}
Remove-Item -LiteralPath $resolvedStage -Recurse -Force
Write-Output $zip
