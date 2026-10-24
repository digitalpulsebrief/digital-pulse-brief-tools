# Windows Wi-Fi Diagnostics Tool

**Digital Pulse Brief** · `v1.0.0-rc2` · MIT-licensed · **Beta: initial real Windows manual tests passed; broader validation pending**

A small, read-only PowerShell utility to help explain why Wi-Fi is connected but Internet access is unreliable or missing. No third-party apps, paid services, or administrator permissions are required for typical use.

## Release-candidate changes

- **rc2:** Fixed a startup crash in the one-click launcher caused by resolving `$PSScriptRoot` inside the `ReportDirectory` parameter default. The default report path is now resolved after parameter binding. Broader Windows runtime QA remains pending.

## Checks included

| Check | What it tells you |
|---|---|
| Wi-Fi adapter | Whether a wireless adapter can be found |
| WLAN AutoConfig | Whether Microsoft's Wi-Fi service is running |
| Wi-Fi link | Whether the adapter reports Up |
| IPv4/DHCP | Whether IPv4 exists and whether Windows reports a 169.254.x.x self-assigned address |
| Gateway | Presence of an IPv4 gateway and response to an ICMP ping |
| DNS configuration | Whether the adapter has DNS servers configured |
| DNS lookup | Whether Windows can resolve `example.com` |
| Outbound TCP | Whether Windows can connect to `1.1.1.1:443` within 3 seconds |

**Do not treat ping failure as proof a router is broken.** Some routers block ICMP. DNS/TCP tests can use Ethernet or VPN even when a Wi-Fi adapter is selected. IPv6-only networks are not fully covered.

## How to run

1. Download and extract the ZIP to a folder on a trusted Windows computer.
2. Read `WiFi-Diagnostics.ps1` and `Run-WiFi-Diagnostics.cmd` before running.
3. Double-click `Run-WiFi-Diagnostics.cmd`.
4. Read the PASS/WARN/FAIL/SKIP messages. Open local `reports/*.txt` for recommendations.

The launcher invokes `powershell.exe -NoProfile -ExecutionPolicy Bypass` **only for that launched process**, not as a permanent system setting. On corporate/managed devices, follow IT policy. Do not run unknown downloaded scripts.

**Alternative, inside Windows PowerShell 5.1:**

```powershell
.\WiFi-Diagnostics.ps1
.\WiFi-Diagnostics.ps1 -SaveReport
.\WiFi-Diagnostics.ps1 -ListAdapters
.\WiFi-Diagnostics.ps1 -InterfaceAlias 'Wi-Fi 2' -SaveReport
.\WiFi-Diagnostics.ps1 -SaveReport -IncludeNetworkDetails
```

## Privacy

- No saved Wi-Fi profiles or passwords; no SSID, MAC address, username, computer name or remote telemetry is intentionally collected.
- The launcher saves **local** `.txt` and `.json` reports under `reports/`.
- Local IPv4, gateway and DNS IP addresses are hidden **by default**, including in reports. Use `-IncludeNetworkDetails` only when you need those addresses in reports.
- Diagnostic requests go to `example.com` (DNS) and `1.1.1.1:443` (TCP) if connected; the gateway may also receive an ICMP ping. Those servers can see normal network traffic from your computer.
- Adapter names and diagnostic findings are saved; review before sharing.

## What it DOES NOT do

Never resets Winsock, changes DNS, flushes a cache, removes a driver, changes an adapter, enables/disables Wi-Fi, exports passwords, edits the registry, or installs programs. It does **not** automatically repair Wi-Fi.

## Compatibility and limitations

- Intended for Windows 11 and Windows 10 with **Windows PowerShell 5.1** and built-in NetAdapter/DnsClient commands. Windows 10's standard support ended in October 2025 (ESU/LTSC exceptions exist).
- May fail on systems where adapter tools are disabled by company policy.
- Nonstandard adapter names may require `-ListAdapters` and `-InterfaceAlias`.
- Does not measure speed, signal strength or packet loss trends and does not inspect captive portals.
- **Manual runtime testing on 24 October 2026:** The user tested the RC2 launcher on a Windows computer with Wi-Fi connected and deliberately disconnected. In the connected test, adapter, service, link, IPv4, gateway, DNS, and TCP checks passed, and a local report was saved. In the disconnected test, the tool flagged the disconnected adapter and skipped network probes. OS edition, non-admin execution, and more failure conditions have not been verified. See [manual test results](tests/MANUAL_TEST_RESULTS_2026-10-24.md) and [remaining QA checklist](tests/WINDOWS_TEST_PLAN.md). This is a beta, not a fully validated diagnostic solution.

## Exit codes

- `0`: Basic checks found no obvious issue.
- `1`: A check reported a warning or problem.
- `2`: The tool could not finish, e.g. adapter enumeration blocked or selected name not found.

## Official references

- [Microsoft: Fix Wi-Fi connection issues](https://support.microsoft.com/en-us/windows/experience/connectivity-networking/fix-wi-fi-connection-issues-in-windows)
- [Microsoft Learn: Wireless troubleshooting](https://learn.microsoft.com/en-us/troubleshoot/windows-client/networking/wireless-network-connectivity-issues-troubleshooting)
- [Microsoft Learn: Get-NetAdapter](https://learn.microsoft.com/en-us/powershell/module/netadapter/get-netadapter)
- [Microsoft Learn: Resolve-DnsName](https://learn.microsoft.com/en-us/powershell/module/dnsclient/resolve-dnsname)
- [Microsoft Learn: Windows 10 end of support](https://learn.microsoft.com/en-us/lifecycle/announcements/windows-10-end-of-support)

## License

MIT; source provided as-is, without warranty. Not affiliated with Microsoft or Cloudflare.
