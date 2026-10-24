# Privacy and security

The utility performs read-only network queries and optional local report creation. It does not automatically repair Wi-Fi, request admin elevation, change system settings or retrieve Wi-Fi passwords.

When troubleshooting, avoid posting personal IP addresses, passwords, MAC addresses, SSIDs or organization details. By default local network IP addresses are hidden; advanced users can opt in to `-IncludeNetworkDetails` for their own locally stored reports. Adapter names remain visible.

It contacts `example.com` for DNS and `1.1.1.1:443` for a basic TCP probe; it may ping the local gateway. A probe might leave over a VPN/Ethernet connection rather than Wi-Fi. The command launcher uses a temporary, process-only PowerShell execution-policy override. Read the code before running and comply with organization policy.
