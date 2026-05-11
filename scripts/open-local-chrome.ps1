param(
    [string] $Url = 'http://127.0.0.1:8000',
    [int] $DelaySeconds = 2
)

$chromeCandidates = @(
    "$env:ProgramFiles\Google\Chrome\Application\chrome.exe",
    "${env:ProgramFiles(x86)}\Google\Chrome\Application\chrome.exe",
    "$env:LOCALAPPDATA\Google\Chrome\Application\chrome.exe"
) | Where-Object { $_ -and (Test-Path $_) }

$chromePath = $chromeCandidates | Select-Object -First 1

Start-Job -ScriptBlock {
    param($BrowserPath, $TargetUrl, $Delay)

    Start-Sleep -Seconds $Delay

    if ($BrowserPath) {
        Start-Process -FilePath $BrowserPath -ArgumentList $TargetUrl
    } else {
        Start-Process $TargetUrl
    }
} -ArgumentList $chromePath, $Url, $DelaySeconds | Out-Null
