<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English language strings for MRCA.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['alert_deprecated_exposure'] = 'HIGH: Plugin "{$a}" uses deprecated functions AND has unprotected PII exposure. Unmaintained plugin handling sensitive data.';
$string['alert_high_risk_unstable_deps'] = 'HIGH: Plugin "{$a}" has high total risk AND unstable dependencies. Update or replace recommended.';
$string['alert_multi_role_escalation'] = 'CRITICAL: {$a} non-admin roles have excessive critical capabilities. Systemic permission model misconfiguration.';
$string['alert_outdated_pii'] = 'HIGH: Plugin "{$a}" is outdated AND handles PII data. Unpatched vulnerabilities may affect personal data.';
$string['alert_privacy_capability_correlation'] = 'CRITICAL: Plugin "{$a}" has high privacy risk, no Privacy API, AND defines capabilities. Potential data exposure through permission inheritance.';
$string['alert_structural_privacy'] = 'HIGH: Plugin "{$a}" has structural issues AND privacy gaps without Privacy API. Likely non-compliant.';
$string['alert_systemic_risk'] = 'CRITICAL: Plugin "{$a->plugin}" (high risk) combined with Role ID {$a->roleid} (high permission exposure) creates systemic failure probability.';
$string['alerts'] = 'Correlation Alerts';
$string['alerts_desc'] = 'Systemic risks detected by cross-referencing findings across layers.';
$string['autoscan_new_plugins'] = 'Auto-scan new plugins';
$string['autoscan_new_plugins_desc'] = 'Automatically run a risk scan when a new plugin is installed or enabled.';
$string['capability_scanner'] = 'Capability & Permission Layer';
$string['capability_scanner_desc'] = 'Role capability analysis and privilege escalation risk detection.';
$string['capability_score'] = 'Capability Score';
$string['confirm_scan'] = 'This will run a complete risk & compliance scan. Continue?';
$string['correlation_engine'] = 'Correlation Engine';
$string['correlation_engine_desc'] = 'Systemic risk detection by correlating findings across all layers.';
$string['critical_caps'] = 'Critical Capabilities';
$string['dashboard_desc'] = 'Comprehensive risk analysis for your Moodle installation.';
$string['dashboard_title'] = 'MRCA Dashboard';
$string['dep_core_mismatch'] = 'Core version mismatch — plugin requires a newer Moodle version.';
$string['dep_deprecated_apis'] = '{$a} deprecated API call(s) detected.';
$string['dep_issues'] = 'Issues';
$string['dep_missing'] = 'Missing dependency: {$a}';
$string['dep_outdated'] = 'Plugin version is outdated (not updated in 2+ years).';
$string['dependency_audit'] = 'Dependency Audit';
$string['dependency_scanner'] = 'Dependency & Compatibility Layer';
$string['dependency_scanner_desc'] = 'Plugin requirements, core compatibility, and deprecated API detection.';
$string['dependency_score'] = 'Dependency Score';
$string['details'] = 'Details';
$string['detected_pii_fields'] = 'Detected PII fields';
$string['download_csv'] = 'Download CSV';
$string['download_json'] = 'Download JSON';
$string['download_pdf'] = 'Download PDF';
$string['event_high_risk_detected'] = 'High risk detected';
$string['event_high_risk_detected_desc'] = 'User {$a->userid} scan detected high risk on plugin "{$a->plugin}" with score {$a->score}.';
$string['integration_heading'] = 'External Integration';
$string['integration_heading_desc'] = 'Configure how MRCA dispatches results to external systems.';
$string['integration_method'] = 'Integration Method';
$string['integration_method_desc'] = 'Choose how to send scan results externally.';
$string['integration_method_disabled'] = 'Disabled';
$string['integration_method_mih'] = 'Moodle Integration Hub';
$string['integration_method_webhook'] = 'Generic Webhook';
$string['last_scan'] = 'Last Scan';
$string['mih_missing_note'] = 'Moodle Integration Hub (local_integrationhub) is not installed. <a href="https://github.com/x-name15/moodle-integration-hub/releases/tag/1.0.0" target="_blank">Download it from GitHub</a> for advanced integration capabilities (recommended).';
$string['mih_service_slug'] = 'MIH Service Slug';
$string['mih_service_slug_desc'] = 'The service slug configured in the Integration Hub for MRCA data.';
$string['mrca'] = 'MRCA';
$string['mrca:configure'] = 'Configure MRCA Settings';
$string['mrca:manage_scans'] = 'Manage MRCA Scans';
$string['mrca:view'] = 'View MRCA Dashboard';
$string['no_alerts'] = 'No correlation alerts. System looks healthy.';
$string['no_pii_detected'] = 'No PII fields detected.';
$string['no_scans_yet'] = 'No scans have been run yet. Click "Scan Now" to start your first analysis.';
$string['plugin'] = 'Plugin';
$string['plugin_risk_details'] = 'Plugin Risk Details';
$string['pluginname'] = 'Moodle Risk & Compliance Analyzer';
$string['plugins_scanned'] = 'Plugins Scanned';
$string['privacy:metadata:whitelist'] = 'Records of fields whitelisted by administrators during risk scans.';
$string['privacy:metadata:whitelist:component'] = 'The plugin component the whitelisted field belongs to.';
$string['privacy:metadata:whitelist:field_name'] = 'The name of the whitelisted database field.';
$string['privacy:metadata:whitelist:table_name'] = 'The database table containing the whitelisted field.';
$string['privacy:metadata:whitelist:timecreated'] = 'The time when the field was whitelisted.';
$string['privacy:metadata:whitelist:userid'] = 'The ID of the user who whitelisted the field.';
$string['privacy_api'] = 'Privacy API';
$string['privacy_api_no'] = '✗ Missing';
$string['privacy_api_yes'] = '✓ Implemented';
$string['privacy_scanner'] = 'Privacy & Data Layer';
$string['privacy_scanner_desc'] = 'PII detection, Privacy API compliance, and encryption analysis.';
$string['privacy_score'] = 'Privacy Score';
$string['reason_keyword_match'] = 'Field name matches PII keyword';
$string['recommended'] = 'Recommended';
$string['report_dispatch_heading'] = 'Report Dispatch Options';
$string['report_dispatch_heading_desc'] = 'Control when and what is sent to external integrations after each scan.';
$string['report_payload'] = 'Report payload';
$string['report_payload_desc'] = 'How much data to include in dispatched reports.';
$string['report_payload_full'] = 'Full report (all plugins + details)';
$string['report_payload_summary'] = 'Summary only (totals + alerts)';
$string['report_send_failed'] = 'Failed to send report.';
$string['report_sent'] = 'Report sent successfully.';
$string['report_title'] = 'MRCA — Risk & Compliance Report';
$string['report_trigger'] = 'Report trigger';
$string['report_trigger_always'] = 'Always (after every scan)';
$string['report_trigger_critical'] = 'Only when critical/high alerts are found';
$string['report_trigger_desc'] = 'When to dispatch reports to the external integration.';
$string['risk_critical'] = 'Critical';
$string['risk_distribution'] = 'Risk Distribution';
$string['risk_healthy'] = 'Healthy';
$string['risk_high'] = 'High';
$string['risk_level'] = 'Risk Level';
$string['risk_low'] = 'Low';
$string['risk_medium'] = 'Medium';
$string['risk_moderate'] = 'Moderate';
$string['risk_score'] = 'Risk Score';
$string['risk_thresholds_heading'] = 'Risk Thresholds';
$string['risk_thresholds_heading_desc'] = 'Configure the score thresholds for risk level classification.';
$string['risk_trend'] = 'Risk Trend';
$string['role'] = 'Role';
$string['role_heatmap'] = 'Role Risk Heatmap';
$string['role_heatmap_desc'] = 'Visual overview of role permission exposure across all system roles.';
$string['role_risk_score'] = 'Role Risk Score';
$string['roles_scanned'] = 'Roles Scanned';
$string['scan_completed'] = 'Risk & compliance scan completed successfully.';
$string['scan_core_plugins'] = 'Scan core Moodle plugins';
$string['scan_core_plugins_desc'] = 'Include standard Moodle plugins (maintained by Moodle HQ) in scans. Disabled by default since core plugins are not third-party and are updated with Moodle itself. Enable only if you need a full audit including core modules.';
$string['scan_date'] = 'Scan Date';
$string['scan_now'] = 'Scan Now';
$string['send_report'] = 'Send Report';
$string['settings'] = 'MRCA Settings';
$string['site_risk_index'] = 'Site Risk Index';
$string['site_risk_index_desc'] = 'Normalized 0-100 score combining all risk layers.';
$string['structural_legacy_cron'] = 'Uses deprecated $plugin->cron. Should use Task API.';
$string['structural_no_directory'] = 'Plugin directory not found.';
$string['structural_no_lang'] = 'No language directory found.';
$string['structural_no_maturity'] = 'No maturity declaration in version.php.';
$string['structural_no_readme'] = 'No README file found.';
$string['structural_no_tests'] = 'No tests directory — plugin has no unit tests.';
$string['structural_no_version'] = 'Missing version.php — plugin cannot be validated.';
$string['threshold_high'] = 'High Risk Threshold';
$string['threshold_high_desc'] = 'Plugins with scores at or above this value are classified as High risk.';
$string['threshold_medium'] = 'Medium Risk Threshold';
$string['threshold_medium_desc'] = 'Plugins with scores at or above this value are classified as Medium risk.';
$string['top_risky_plugins'] = 'Top 5 Risky Plugins';
$string['top_risky_roles'] = 'Top 5 Risky Roles';
$string['total_score'] = 'Total Score';
$string['verified_encrypted'] = 'Content verified as encrypted';
$string['verified_plaintext'] = 'Content detected as plaintext';
$string['webhook_token'] = 'Webhook Token';
$string['webhook_token_desc'] = 'Bearer token for authenticating webhook requests.';
$string['webhook_url'] = 'Webhook URL';
$string['webhook_url_desc'] = 'URL to send scan results to (HTTP POST with JSON body).';
$string['whitelist'] = 'Whitelist';
$string['whitelist_add'] = 'Add to Whitelist';
$string['whitelist_added'] = 'Field added to whitelist successfully.';
$string['whitelist_component'] = 'Component';
$string['whitelist_desc'] = 'Fields marked as safe will be excluded from future scans.';
$string['whitelist_empty'] = 'No whitelisted fields.';
$string['whitelist_field'] = 'Field';
$string['whitelist_remove'] = 'Remove from Whitelist';
$string['whitelist_removed'] = 'Field removed from whitelist.';
$string['whitelist_table'] = 'Table';
$string['whitelist_this_field'] = 'Add this field to whitelist';
