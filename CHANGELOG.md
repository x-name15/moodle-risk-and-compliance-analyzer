# Changelog

## [1.1.0] - 2026-02-23

### Fixed
- **Scanners:** Fixed a false-positive issue where "ghost plugins" (plugins with records in the database but removed from the file system) were incorrectly flagged with high structural and privacy risks. The scanner now properly ignores plugins without a valid physical directory (Thanks to @ewallah).
- **Code Quality:** Massive refactoring to meet Moodle's strict coding standards (Moodle CS). Resolved over 150+ code style violations, removed underscores from local variables, added missing docblocks, and significantly reduced cyclomatic complexity in the main scanning engine (`run_scan`).

## [1.0.0] - 2026-02-20

### Initial Stable Release

#### 🔍 Multi-Layer Risk Analysis
- **Privacy Scanner:** PII detection, Privacy API compliance checking, encryption verification.
- **Dependency Scanner:** Plugin requirements, core version compatibility, deprecated API detection, outdated plugin flagging.
- **Structural Scanner:** Code quality checks, deprecated/unsafe PHP function detection, plugin structure validation.
- **Capability Scanner:** Role permission analysis, critical capability detection, privilege escalation risk assessment.
- **Correlation Engine:** Cross-layer systemic risk detection combining findings from all scanners.

#### 📊 Site Risk Index
- Normalized 0-100 score combining all risk layers.
- Classifications: Healthy, Low, Moderate, High, Critical.

#### 🛡️ Core Plugin Filtering
- Standard Moodle plugins (maintained by Moodle HQ) excluded from scans by default.
- Admin setting to opt-in to core plugin scanning for full audits.
- Uses `core_plugin_manager::standard_plugins_list()` for accurate detection.

#### 📈 Dashboard
- Interactive dashboard with risk distribution charts (Chart.js).
- Top 5 riskiest plugins and roles.
- Risk trend over last 10 scans.
- Dependency audit panel.
- Role permission heatmap.
- Correlation alerts panel.

#### 📤 Reports & Integration
- Export in PDF, CSV, and JSON formats.
- Webhook integration for external SIEM/SOC systems.
- Moodle Integration Hub (MIH) support.

#### 🔐 Privacy & Compliance
- Full Moodle Privacy API implementation (GDPR).
- PII field whitelist management.
- Encryption detection in database content.

#### ⚙️ Architecture
- Engine layer: `risk_engine`, `scoring_model`, `correlation_engine`.
- Scanners: `privacy_scanner`, `dependency_scanner`, `capability_scanner`, `structural_scanner`.
- Models: `plugin_risk`, `role_risk`, `site_risk`.
- Reporting: `dashboard`, `pdf_generator`, `csv_generator`, `export_json`.
- Unit tests for core engine classes.

#### 🌐 Localization
- Complete English, Spanish, Italian, French and Portuguese language packs.
