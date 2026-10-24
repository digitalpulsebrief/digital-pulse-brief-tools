# Windows QA scenarios — partial real-device testing (24 October 2026)

- [x] Confirm RC2 opens via `Run-WiFi-Diagnostics.cmd` from a path containing spaces and does **not** crash at `ReportDirectory` initialization.
- [ ] Parse with Windows PowerShell 5.1 AST parser, verify no syntax errors.
- [x] On a Windows computer with connected Wi-Fi, verify statuses and report generation. **Exact OS edition not yet verified.**
- [x] On a Windows computer with disconnected Wi-Fi, show failure and skip internet probes. **Exact OS edition not yet verified.**
- [x] Disconnect Wi-Fi deliberately: no crash and no automatic reconnection observed.
- [ ] Simulate/observe missing DHCP IP or 169.254.x.x: correctly report failure.
- [ ] Missing DNS or gateway: show warnings without crashes.
- [ ] Firewall blocks gateway ping: don't claim router is broken.
- [ ] Ethernet and VPN active: no claim external probe specifically tested Wi-Fi.
- [ ] Rename adapter to non-English name: -ListAdapters / -InterfaceAlias fallback works.
- [ ] Audit both saved default report formats for personal data (the connected TXT screenshot shows masked IP and gateway values).
- [ ] Check IncludeNetworkDetails opt-in behavior.
- [ ] Run one-click CMD on non-admin account; verify reports and return codes.
- [ ] Test on Windows 10 if available; document lifecycle limitations.
- [ ] Capture genuine before/after screenshots for Digital Pulse Brief article.

Do not publish untested screenshots, pretend all diagnostics are accurate, or describe this as an automatic repair tool.
