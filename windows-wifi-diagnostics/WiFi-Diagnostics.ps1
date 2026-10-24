#Requires -Version 5.1
<#
.SYNOPSIS
 Digital Pulse Brief free, read-only Windows Wi-Fi diagnostics.
.DESCRIPTION
 Checks wireless adapter, WLAN AutoConfig, IPv4/DHCP, gateway, DNS, and basic
 outbound connectivity. It never changes network configuration.
#>
[CmdletBinding()]
param(
    [switch]$SaveReport,
    [switch]$IncludeNetworkDetails,
    [switch]$ListAdapters,
    [string]$InterfaceAlias,
    [string]$ReportDirectory
)
# Script-location variables can be empty while PowerShell evaluates parameter defaults.
# Resolve the default report path only after parameter binding has completed.
if ([string]::IsNullOrWhiteSpace($ReportDirectory)) {
    $scriptFolder = $PSScriptRoot
    if ([string]::IsNullOrWhiteSpace($scriptFolder) -and $PSCommandPath) {
        $scriptFolder = Split-Path -Path $PSCommandPath -Parent
    }
    if ([string]::IsNullOrWhiteSpace($scriptFolder)) {
        Write-Error 'Could not locate the script folder. Provide -ReportDirectory explicitly.'
        exit 2
    }
    $ReportDirectory = Join-Path -Path $scriptFolder -ChildPath 'reports'
}
Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'
$script:Checks = New-Object 'System.Collections.Generic.List[object]'
$script:AdapterName = ''

function Add-Check {
    param(
        [string]$Code,
        [ValidateSet('PASS','WARN','FAIL','INFO','SKIP')][string]$Status,
        [string]$Finding,
        [string]$Action = ''
    )
    $entry = [pscustomobject]@{ Code=$Code; Status=$Status; Finding=$Finding; Action=$Action }
    [void]$script:Checks.Add($entry)
    $color = switch ($Status) { 'PASS' { 'Green' } 'FAIL' { 'Red' } 'WARN' { 'Yellow' } default { 'Gray' } }
    Write-Host ('[{0}] {1}' -f $Status, $Finding) -ForegroundColor $color
    if ($Action) { Write-Host ('       Suggested step: ' + $Action) }
}
function Hide-IP {
    param([string]$Value)
    if (-not $Value) { return 'Not configured' }
    if ($IncludeNetworkDetails) { return $Value }
    return '[hidden]'
}
function Test-ExternalTcp {
    param([string]$HostIP='1.1.1.1',[int]$Port=443,[int]$TimeoutMs=3000)
    $client = New-Object System.Net.Sockets.TcpClient
    $asyncResult = $null
    try {
        $asyncResult = $client.BeginConnect($HostIP,$Port,$null,$null)
        if (-not $asyncResult.AsyncWaitHandle.WaitOne($TimeoutMs,$false)) { return $false }
        $client.EndConnect($asyncResult)
        return [bool]$client.Connected
    }
    catch { return $false }
    finally {
        if ($null -ne $asyncResult) { $asyncResult.AsyncWaitHandle.Close() }
        $client.Close()
    }
}
function Get-Result {
    if (@($script:Checks | Where-Object Status -eq 'FAIL').Count -gt 0) { return 'ACTION NEEDED' }
    if (@($script:Checks | Where-Object Status -eq 'WARN').Count -gt 0) { return 'CHECK WARNINGS' }
    return 'NO OBVIOUS ISSUE FOUND'
}
function Save-Report {
    try {
        $folder = [System.IO.Path]::GetFullPath($ReportDirectory)
        if (-not (Test-Path -LiteralPath $folder -PathType Container)) {
            [void](New-Item -ItemType Directory -Path $folder -Force)
        }
        $base = Join-Path $folder ('wifi-diagnostic-' + (Get-Date -Format 'yyyyMMdd-HHmmss'))
        $report = [ordered]@{
            Tool='Digital Pulse Brief Wi-Fi Diagnostics'
            Version='1.0.0-rc2'
            GeneratedUTC=(Get-Date).ToUniversalTime().ToString('o')
            Adapter=$script:AdapterName
            Overall=(Get-Result)
            LocalAddressesHidden=(-not $IncludeNetworkDetails)
            Warning='External DNS/TCP probes may use Ethernet/VPN. A warning is not proof of a specific failure.'
            Checks=@($script:Checks.ToArray())
        }
        $report | ConvertTo-Json -Depth 6 | Set-Content -LiteralPath ($base+'.json') -Encoding UTF8
        $lines = @('DIGITAL PULSE BRIEF - WI-FI DIAGNOSTICS',('Overall: '+$report.Overall),('Adapter: '+$report.Adapter),'')
        foreach ($entry in $script:Checks) {
            $lines += ('[{0}] {1}' -f $entry.Status,$entry.Finding)
            if ($entry.Action) { $lines += ('  Suggested step: '+$entry.Action) }
        }
        $lines += @('',$report.Warning,'Review reports before sharing. No Wi-Fi passwords are collected.')
        $lines | Set-Content -LiteralPath ($base+'.txt') -Encoding UTF8
        Write-Host ''
        Write-Host ('Local reports created: '+$base+'.txt / .json') -ForegroundColor Cyan
    }
    catch { Write-Warning ('Report saving failed: '+$_.Exception.Message) }
}
function Finish {
    param([int]$ExitCode)
    Write-Host ''
    Write-Host ('OVERALL: '+(Get-Result)) -ForegroundColor Cyan
    Write-Host 'Public DNS/TCP tests can use Ethernet or a VPN, not necessarily Wi-Fi.'
    if ($SaveReport) { Save-Report }
    exit $ExitCode
}

Write-Host ''
Write-Host 'Digital Pulse Brief | Wi-Fi Diagnostics 1.0.0-rc2' -ForegroundColor Cyan
Write-Host 'Read-only. No network changes; no administrator privileges requested.'
Write-Host 'Outgoing probes: example.com (DNS) and 1.1.1.1:443 (TCP), if connected.'
Write-Host ''
if ($PSVersionTable.PSEdition -eq 'Core') {
    if ([System.Environment]::OSVersion.Platform -ne [System.PlatformID]::Win32NT) {
        Write-Host 'This utility only supports Windows.' -ForegroundColor Red
        exit 2
    }
}
try { $allAdapters = @(Get-NetAdapter -ErrorAction Stop) }
catch {
    Add-Check 'ADAPTER_ACCESS' 'FAIL' 'Unable to query network adapters.' 'Try Windows PowerShell 5.1 on a Windows 10/11 device.'
    Finish 2
}
if ($ListAdapters) {
    $allAdapters | Select-Object Name,Status,InterfaceDescription,ifIndex | Format-Table -AutoSize
    exit 0
}

# Adapter metadata varies by driver and language. Permit explicit -InterfaceAlias.
$wireless = @($allAdapters | Where-Object {
    $parts = New-Object 'System.Collections.Generic.List[string]'
    foreach ($propertyName in @('Name','InterfaceDescription','NdisPhysicalMedium','PhysicalMediaType')) {
        $property = $_.PSObject.Properties[$propertyName]
        if ($null -ne $property -and $null -ne $property.Value) {
            [void]$parts.Add([string]$property.Value)
        }
    }
    ($parts -join ' ') -match '(?i)Wi[- ]?Fi|WLAN|Wireless|802\.11|Native802_11'
})
$adapter = $null
if ($InterfaceAlias) {
    $selected = @($allAdapters | Where-Object { $_.Name -eq $InterfaceAlias })
    if ($selected.Count -ne 1) {
        Add-Check 'ADAPTER' 'FAIL' ('No adapter with name: '+$InterfaceAlias) 'Use -ListAdapters to find its exact name.'
        Finish 2
    }
    $adapter = $selected[0]
    if (@($wireless | Where-Object { $_.ifIndex -eq $adapter.ifIndex }).Count -eq 0) {
        Add-Check 'ADAPTER_TYPE' 'WARN' 'Chosen interface is not recognized as wireless.' 'Double-check the selected name.'
    }
}
else {
    $connected = @($wireless | Where-Object { $_.Status -eq 'Up' } | Select-Object -First 1)
    if ($connected.Count -gt 0) { $adapter = $connected[0] }
    elseif ($wireless.Count -gt 0) { $adapter = $wireless[0] }
}
if ($null -eq $adapter) {
    Add-Check 'ADAPTER' 'FAIL' 'Could not identify a Wi-Fi adapter.' 'Check Device Manager > Network adapters; use -ListAdapters for unusual adapter names.'
    Finish 1
}
$script:AdapterName = [string]$adapter.Name
Add-Check 'ADAPTER' 'PASS' ('Found adapter: '+$adapter.Name)
try {
    $wlanService = Get-Service 'WlanSvc' -ErrorAction Stop
    if ($wlanService.Status -eq 'Running') { Add-Check 'WLAN_SERVICE' 'PASS' 'WLAN AutoConfig is running.' }
    else { Add-Check 'WLAN_SERVICE' 'WARN' ('WLAN AutoConfig state: '+$wlanService.Status) 'Check the service before changing settings.' }
}
catch { Add-Check 'WLAN_SERVICE' 'WARN' 'Could not check WLAN AutoConfig status.' }
if ($adapter.Status -ne 'Up') {
    Add-Check 'LINK' 'FAIL' ('Wi-Fi adapter not connected: '+$adapter.Status) 'Check Wi-Fi toggle, Airplane mode and connection to your router.'
    Add-Check 'PROBES' 'SKIP' 'Skipped network probes until Wi-Fi is connected.'
    Finish 1
}
Add-Check 'LINK' 'PASS' 'Wi-Fi adapter reports connected (Up).'

$ips = @()
$gateways = @()
try {
    $config = Get-NetIPConfiguration -InterfaceIndex $adapter.ifIndex -ErrorAction Stop
    $ips = @($config.IPv4Address | ForEach-Object { $_.IPAddress } | Where-Object { $_ })
    $gateways = @($config.IPv4DefaultGateway | ForEach-Object { $_.NextHop } | Where-Object { $_ })
}
catch { Add-Check 'IP_READING' 'WARN' 'Windows IP configuration query failed.' 'Inspect network hardware properties in Windows.' }
$validIps = @($ips | Where-Object { $_ -notmatch '^169\.254\.' -and $_ -ne '0.0.0.0' })
if ($ips.Count -eq 0) {
    Add-Check 'IP' 'WARN' 'Wi-Fi has no IPv4 address (IPv6-only networks may be valid).' 'Check DHCP if your router is expected to assign IPv4.'
}
elseif ($validIps.Count -eq 0) {
    Add-Check 'IP' 'FAIL' 'Wi-Fi has only a 169.254.x.x or invalid IPv4 address (possible DHCP issue).' 'Check the router DHCP service and reconnect Wi-Fi.'
}
else { Add-Check 'IP' 'PASS' ('IPv4 address assigned: '+(Hide-IP ([string]$validIps[0]))) }
if ($gateways.Count -eq 0) {
    Add-Check 'GATEWAY' 'WARN' 'No IPv4 default gateway detected on this adapter.' 'Check router settings if Internet access is expected.'
}
else {
    $gw = [string]$gateways[0]
    Add-Check 'GATEWAY' 'INFO' ('Gateway: '+(Hide-IP $gw))
    try {
        $pingOk = [bool](Test-Connection -ComputerName $gw -Count 1 -Quiet -ErrorAction SilentlyContinue)
        if ($pingOk) { Add-Check 'GATEWAY_PING' 'PASS' 'Gateway responded to an ICMP ping.' }
        else { Add-Check 'GATEWAY_PING' 'WARN' 'Gateway did not respond; ICMP might be blocked.' 'Do not assume your router is broken solely from this ping.' }
    }
    catch { Add-Check 'GATEWAY_PING' 'WARN' 'Gateway ping could not complete.' }
}
try {
    $dnsSettings = @(Get-DnsClientServerAddress -InterfaceIndex $adapter.ifIndex -ErrorAction Stop)
    $dnsServers = @($dnsSettings | ForEach-Object { $_.ServerAddresses } | Where-Object { $_ } | Select-Object -Unique)
    if ($dnsServers.Count -eq 0) { Add-Check 'DNS_CONFIG' 'WARN' 'No DNS server addresses returned.' 'Inspect DNS configuration in Windows.' }
    elseif ($IncludeNetworkDetails) { Add-Check 'DNS_CONFIG' 'PASS' ('DNS servers: '+($dnsServers -join ', ')) }
    else { Add-Check 'DNS_CONFIG' 'PASS' ('DNS server addresses configured ('+$dnsServers.Count+'); values hidden.') }
}
catch { Add-Check 'DNS_CONFIG' 'WARN' 'Could not query DNS configuration.' }

# Avoid misleading internet results when this Wi-Fi adapter has no usable IPv4.
if ($validIps.Count -eq 0) {
    Add-Check 'PROBES' 'SKIP' 'External probes skipped because Wi-Fi has no usable IPv4 address.'
}
else {
    try {
        $answers = @(Resolve-DnsName -Name 'example.com' -Type A -DnsOnly -QuickTimeout -ErrorAction Stop | Where-Object { $_.Type -eq 'A' })
        if ($answers.Count -gt 0) { Add-Check 'DNS_QUERY' 'PASS' 'DNS lookup for example.com succeeded.' }
        else { Add-Check 'DNS_QUERY' 'WARN' 'No IPv4 DNS response for example.com.' 'Check configured DNS, filtering and captive portals.' }
    }
    catch { Add-Check 'DNS_QUERY' 'WARN' 'DNS lookup for example.com failed.' 'Check configured DNS, filtering and captive portals.' }
    if (Test-ExternalTcp -HostIP '1.1.1.1' -Port 443 -TimeoutMs 3000) {
        Add-Check 'TCP' 'PASS' 'Outbound TCP probe to 1.1.1.1:443 succeeded.'
    }
    else {
        Add-Check 'TCP' 'WARN' 'TCP probe to 1.1.1.1:443 failed or timed out.' 'Could be routing, VPN, firewall, ISP or the endpoint; test another website.'
    }
}
if ((Get-Result) -eq 'NO OBVIOUS ISSUE FOUND') { Finish 0 }
Finish 1
