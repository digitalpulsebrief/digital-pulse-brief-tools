# Manual test observations — 24 October 2026

RC2 was tested on a real Windows computer by the project owner. This is an account of observed screenshots, not a full QA certificate.

## Connected Wi-Fi (passed)
- One-click launcher displayed RC2 version and did not show the RC1 path-initialization error.
- Adapter identified as `Wi-Fi`; WLAN AutoConfig running; link `Up`.
- IPv4 assigned; gateway ping responded; DNS servers configured; `example.com` resolved; outbound TCP connection to `1.1.1.1:443` succeeded.
- Overall message: `NO OBVIOUS ISSUE FOUND`.
- `.txt` report screenshot confirmed expected findings and address redaction.

## Deliberately disconnected Wi-Fi (passed)
- Same launcher showed adapter status `Disconnected` as `FAIL`.
- Network probes skipped. Overall: `ACTION NEEDED`.
- A report path was printed. The disconnected report file itself has not yet been reviewed.

## Important testing boundaries
- Exact Windows version not recorded.
- The first screenshot showed an Administrator console; non-admin functionality remains unverified.
- Genuine DHCP, DNS, gateway, VPN-routing, firewall or IPv6-only failure scenarios remain untested.
- No claim is made that the tool fixes connectivity; it only diagnoses selected symptoms.
- Do not commit screenshots showing private Windows folder paths or user details without careful cropping/redaction.
