param([string]$Base='http://localhost/CODIGOPCP/MODCOMER2/MODCOMCER/beta-hosting/public/')
$ErrorActionPreference='Stop'
$session=New-Object Microsoft.PowerShell.Commands.WebRequestSession
function Get-Page($path){Invoke-WebRequest -UseBasicParsing -Uri ($Base+$path) -WebSession $session}
function Token($html){[regex]::Match($html,'name="csrf" value="([^"]+)"').Groups[1].Value}
function Post-Page($body){Invoke-WebRequest -UseBasicParsing -Uri ($Base+'index.php') -Method Post -Body $body -WebSession $session}
function Api($action,$body,$csrf){Invoke-RestMethod -Uri ($Base+'api.php?action='+$action) -Method Post -ContentType 'application/json' -Headers @{'X-CSRF-Token'=$csrf} -Body ($body|ConvertTo-Json) -WebSession $session}
$page=Get-Page 'setup.php'
if($page.Content -notmatch 'Prepara tu beta'){throw 'Esta prueba requiere almacenamiento de beta nuevo.'}
$key=[regex]::Match((Get-Content "$PSScriptRoot/../private/config.local.php" -Raw),"'setup_key' => '([^']+)'").Groups[1].Value
$password='Prueba-'+[guid]::NewGuid().ToString('N')
$page=Invoke-WebRequest -UseBasicParsing -Uri ($Base+'setup.php') -Method Post -WebSession $session -Body @{csrf=(Token $page.Content);key=$key;username='beta_test';password=$password;company='Aceros - prueba local'}
if($page.Content -notmatch 'Selecciona tu empresa'){throw 'Fallo setup'}
$state=Get-Content "$PSScriptRoot/../private/data/state.json" -Raw | ConvertFrom-Json
$company=($state.companies.PSObject.Properties | Select-Object -First 1).Name
$token=[regex]::Match($page.Content,'Token de vinculaci.n<input readonly value="([^"]+)"').Groups[1].Value
if($token.Length -ne 64){throw 'No se obtuvo token de prueba'}
$page=Post-Page @{action='logout';csrf=(Token $page.Content)}
$page=Post-Page @{action='login';csrf=(Token $page.Content);username='beta_test';password='incorrecta'}
if($page.Content -notmatch 'incorrectos'){throw 'Login incorrecto no rechazado'}
$page=Post-Page @{action='login';csrf=(Token $page.Content);username='beta_test';password=$password}
if($page.Content -notmatch 'Selecciona tu empresa'){throw 'Fallo login'}
$page=Post-Page @{action='select';csrf=(Token $page.Content);company=$company}
if($page.Content -notmatch 'id="report"'){throw 'Fallo selector'}
$csrf=[regex]::Match($page.Content,'data-csrf="([^"]+)"').Groups[1].Value
$job=Api 'enqueue' @{company=$company;inicio='2026-01-01';fin='2026-01-10'} $csrf
$pair=@{Endpoint=($Base+'api.php');Company=$company;Token=$token;Job=$job.id}
[IO.File]::WriteAllText((Join-Path $PSScriptRoot '../private/data/test-pairing.json'),($pair|ConvertTo-Json),(New-Object Text.UTF8Encoding($false)))
Write-Output 'OK instalacion, login invalido/valido, selector y solicitud HTTP.'
try {Api 'enqueue' @{company=$company;inicio='2026-01-01';fin='2026-01-10'} 'incorrecto' | Out-Null;throw 'CSRF aceptado'} catch {if($_.Exception.Response.StatusCode.value__ -ne 403){throw}}
Write-Output 'OK CSRF rechazado.'
try {Invoke-WebRequest -UseBasicParsing -Uri ($Base+'../private/data/state.json') | Out-Null;throw 'Datos privados expuestos'} catch {if($_.Exception.Response.StatusCode.value__ -ne 403){throw}}
Write-Output 'OK almacenamiento privado no accesible por HTTP.'
