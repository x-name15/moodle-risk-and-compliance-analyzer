<h1><img src="pix/icon.svg" width="40" height="40"> Moodle Risk & Compliance Analyzer</h1>

[![Moodle](https://img.shields.io/badge/Moodle-4.1%2B-orange)](https://moodle.org)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-blue)](https://php.net)
[![License](https://img.shields.io/badge/License-GPL%20v3-green)](LICENSE)

## Overview

MRCA is a multi-layered risk analysis engine for Moodle. It scans installed plugins for security risks, privacy compliance gaps, outdated dependencies, and permission misconfigurations — providing administrators with a unified **Site Risk Index** and actionable alerts.

By default, MRCA only scans **third-party plugins**. Core Moodle modules are excluded to avoid false positives.

## Why MRCA?

Moodle is the most widely adopted LMS in the world, with strong presence in **European universities and institutions** where **GDPR (General Data Protection Regulation)** compliance is mandatory. Yet there is no built-in mechanism to audit installed plugins for privacy risks, deprecated code, or permission misconfigurations.

MRCA was built to fill this gap — giving administrators a **proactive compliance tool** instead of relying on reactive audits.

## Who Is It For?

- **European universities and schools** subject to GDPR
- **Corporate Moodle administrators** managing large plugin ecosystems
- **IT compliance teams** needing automated risk assessments
- **Moodle hosting providers** offering security guarantees
- **Any institution** in the EU/EEA, UK, or jurisdictions with similar data protection laws (LOPDGDD in Spain, CNIL in France, etc.)

## Features

| Feature | Description |
|---------|-------------|
| **Privacy Scanner** | PII detection, Privacy API compliance, encryption checks |
| **Dependency Scanner** | Version compatibility, deprecated APIs, outdated plugins |
| **Structural Scanner** | Code quality, unsafe PHP functions, plugin structure |
| **Capability Scanner** | Role permissions, privilege escalation risks |
| **Correlation Engine** | Cross-layer systemic risk detection |
| **Site Risk Index** | Normalized 0–100 score with 5-level classification |
| **Dashboard** | Interactive charts, trends, heatmaps, alerts |
| **Reports** | PDF, CSV, and JSON export |
| **Integrations** | Webhook and MIH support for SIEM/SOC |
| **Privacy API** | GDPR-compliant data handling |

## Installation

1. Copy `mrca/` to `local/mrca/` in your Moodle directory.
2. Run `php admin/cli/upgrade.php` or visit **Site Administration → Notifications**.
3. Go to **Site Administration → Server → MRCA → Dashboard**.

## Quick Start

```bash
# CLI scan
php local/mrca/cli/run_scan_cli.php

# Or use the dashboard: click "Scan Now"
```

Scheduled scans run daily at 2 AM via Moodle cron.

## Configuration

**Site Administration → Server → MRCA → Settings:**

| Setting | Description | Default |
|---------|-------------|---------|
| Auto-scan new plugins | Scan on plugin install/enable | Off |
| Scan core plugins | Include Moodle HQ modules | Off |
| High risk threshold | Score for "high risk" | 60 |
| Medium risk threshold | Score for "medium risk" | 30 |
| Integration method | Webhook / MIH / Disabled | Disabled |

## Documentation

Full documentation in English and Spanish is available in the [`docs/`](docs/) directory:

- 📖 [English Documentation](docs/en/README.md)
- 📖 [Documentación en Español](docs/es/README.md)

## License

MIT License. See [LICENSE](LICENSE).
