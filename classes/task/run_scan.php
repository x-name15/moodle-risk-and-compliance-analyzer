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
 * Scheduled scan task — the main MRCA scanning engine.
 *
 * Runs all scanner layers and stores results.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\task;

defined('MOODLE_INTERNAL') || die();

use core\task\scheduled_task;
use local_mrca\scanners\privacy_scanner;
use local_mrca\scanners\dependency_scanner;
use local_mrca\scanners\capability_scanner;
use local_mrca\scanners\structural_scanner;
use local_mrca\engine\risk_engine;
use local_mrca\engine\correlation_engine;
use local_mrca\models\plugin_risk;
use local_mrca\models\role_risk;
use local_mrca\models\site_risk;
use local_mrca\util\core_plugin_helper;

class run_scan extends scheduled_task
{

    /**
     * Get the name of the task.
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('pluginname', 'local_mrca') . ' - Scheduled Scan';
    }

    /**
     * Execute the complete MRCA scan.
     */
    public function execute()
    {
        global $DB;

        mtrace("MRCA: Starting Scheduled Risk & Compliance Scan...");

        $privacy_scanner = new privacy_scanner();
        $dependency_scanner = new dependency_scanner();
        $capability_scanner = new capability_scanner();
        $structural_scanner = new structural_scanner();
        $risk_engine = new risk_engine();
        $correlation_engine = new correlation_engine();
        $crypto_analyzer = new \local_mrca\heuristics\crypto_analyzer();

        $plugins = $privacy_scanner->get_all_plugins();
        $total_score = 0;
        $plugins_scanned = 0;
        $core_skipped = 0;

        // Check if core plugins should be scanned (default: no).
        $scan_core = (bool)get_config('local_mrca', 'scan_core_plugins');

        // Create a new scan record.
        $scan = new \stdClass();
        $scan->timecreated = time();
        $scan->timemodified = time();
        $scan->status = 0; // Running.
        $scan->total_score = 0;
        $scan->site_risk_index = 0;
        $scan->plugins_scanned = 0;
        $scan->roles_scanned = 0;
        $scan->id = $DB->insert_record('local_mrca_scans', $scan);

        $all_plugin_risks = [];

        // ====== LAYER 1: Scan all plugins ======
        foreach ($plugins as $type => $plugins_by_type) {
            foreach ($plugins_by_type as $pluginname => $plugininfo) {
                $component = $plugininfo->component;

                // Skip core (standard) Moodle plugins unless explicitly enabled.
                if (!$scan_core && core_plugin_helper::is_core_plugin($component)) {
                    $core_skipped++;
                    continue;
                }

                $plugins_scanned++;

                mtrace("MRCA: Scanning $component...");

                // Privacy layer.
                $api_check = $privacy_scanner->scan_plugin($component);
                $db_check = $privacy_scanner->scan_db_structure($component);

                if (!empty($db_check)) {
                    $db_check = $privacy_scanner->check_content_analysis($db_check, $crypto_analyzer);
                }

                foreach ($db_check as &$finding) {
                    if (!isset($finding['component'])) {
                        $finding['component'] = $component;
                    }
                }

                $privacy_score = $risk_engine->calculate_privacy_score($api_check, $db_check);

                // Dependency layer.
                $dep_findings = $dependency_scanner->scan($component);
                $dependency_score = $risk_engine->calculate_dependency_score($dep_findings);

                // Structural layer.
                $struct_findings = $structural_scanner->scan($component);
                $structural_score = $struct_findings['structural_score'] ?? 0;

                // Capability definition layer — check if plugin defines dangerous capabilities.
                $capability_score = 0;
                $plugin_cap_findings = $this->scan_plugin_capabilities($component);
                if (!empty($plugin_cap_findings)) {
                    $capability_score = $risk_engine->calculate_capability_score($plugin_cap_findings);
                }

                // Total plugin risk combines all layers.
                $plugin_total = $privacy_score + $dependency_score + $capability_score + $structural_score;

                // Save legacy scan results (backward compat).
                if ($plugin_total > 0) {
                    $result = new \stdClass();
                    $result->scanid = $scan->id;
                    $result->plugin = $component;
                    $result->risk_score = $plugin_total;
                    $result->has_privacy_provider = $api_check['has_privacy_provider'] ? 1 : 0;
                    $result->details = json_encode([
                        'db_findings' => $db_check,
                        'dep_findings' => $dep_findings,
                        'struct_findings' => $struct_findings,
                    ]);
                    $DB->insert_record('local_mrca_scan_results', $result);
                }

                // Save multi-score plugin risk.
                $pr = new plugin_risk();
                $pr->scanid = $scan->id;
                $pr->component = $component;
                $pr->privacy_score = $privacy_score;
                $pr->dependency_score = $dependency_score;
                $pr->capability_score = $capability_score;
                $pr->save();

                $all_plugin_risks[$component] = [
                    'privacy_score' => $privacy_score,
                    'dependency_score' => $dependency_score,
                    'capability_score' => $capability_score,
                    'structural_score' => $structural_score,
                    'has_privacy_provider' => $api_check['has_privacy_provider'],
                    'deprecated_calls' => count($struct_findings['deprecated_calls'] ?? []),
                    'dep_findings' => $dep_findings,
                ];

                $total_score += $plugin_total;

                // Trigger event if risk is high.
                if ($plugin_total >= 61) {
                    \local_mrca\event\high_risk_detected::create([
                        'context' => \context_system::instance(),
                        'objectid' => $scan->id,
                        'other' => [
                            'plugin' => $component,
                            'score' => $plugin_total,
                        ],
                    ])->trigger();
                }
            }
        }

        // ====== LAYER 2: Scan roles ======
        mtrace("MRCA: Scanning role permissions...");
        $role_results = $capability_scanner->scan_roles();
        $total_role_risk = 0;
        $roles_scanned = count($role_results);
        $all_role_risks = [];

        foreach ($role_results as $roleid => $role_data) {
            $rr = new role_risk();
            $rr->scanid = $scan->id;
            $rr->roleid = $roleid;
            $rr->risk_score = $role_data['risk_score'];
            $rr->critical_cap_count = $role_data['critical_cap_count'];
            $rr->save();

            $total_role_risk += $role_data['risk_score'];
            $all_role_risks[$roleid] = $role_data;
        }

        // ====== LAYER 3: Correlation engine ======
        mtrace("MRCA: Running correlation analysis...");
        $alerts = $correlation_engine->evaluate($all_plugin_risks, $all_role_risks, $scan->id);
        foreach ($alerts as $alert) {
            $DB->insert_record('local_mrca_alerts', $alert);
        }

        // ====== LAYER 4: Calculate site risk index ======
        $site_risk = site_risk::calculate(
            $total_score,
            $total_role_risk,
            $plugins_scanned,
            $roles_scanned
        );

        // Update scan record.
        $scan->status = 1; // Completed.
        $scan->total_score = $total_score;
        $scan->site_risk_index = $site_risk->index;
        $scan->plugins_scanned = $plugins_scanned;
        $scan->roles_scanned = $roles_scanned;
        $scan->timemodified = time();
        $DB->update_record('local_mrca_scans', $scan);

        mtrace("MRCA: Scan completed. Site Risk Index: {$site_risk->index}/100 ({$site_risk->classification})");
        mtrace("MRCA: Plugins scanned: {$plugins_scanned}" . ($core_skipped > 0 ? " (core plugins skipped: {$core_skipped})" : ""));
        mtrace("MRCA: Alerts generated: " . count($alerts));

        // ====== LAYER 5: External integration dispatch ======
        $method = get_config('local_mrca', 'integration_method');
        $trigger = get_config('local_mrca', 'report_trigger') ?: 'always';
        $payloadtype = get_config('local_mrca', 'report_payload') ?: 'full';

        // Check trigger condition.
        $should_dispatch = true;
        if ($trigger === 'critical_only' && empty($alerts)) {
            $should_dispatch = false;
            mtrace("MRCA: Skipping dispatch — report_trigger=critical_only and no alerts found.");
        }

        if ($should_dispatch && $method === 'mih') {
            if (\core\component::get_component_directory('local_integrationhub') &&
            class_exists('\local_integrationhub\mih')) {
                $slug = get_config('local_mrca', 'mih_service_slug');
                if (!empty($slug)) {
                    mtrace("MRCA: Dispatching results to Integration Hub (Service: $slug)...");
                    try {
                        $payload = ($payloadtype === 'summary')
                            ? $this->build_summary_payload($scan, $alerts)
                            : $this->build_payload($scan);
                        \local_integrationhub\mih::request($slug, '/', $payload, 'POST');
                        mtrace("MRCA: MIH Dispatch Successful.");
                    }
                    catch (\Exception $e) {
                        mtrace("MRCA: MIH Dispatch Failed: " . $e->getMessage());
                    }
                }
                else {
                    mtrace("MRCA: MIH Service Slug not configured.");
                }
            }
            else {
                mtrace("MRCA: Integration Hub selected but not installed.");
            }
        }
        elseif ($should_dispatch && $method === 'webhook') {
            $url = get_config('local_mrca', 'webhook_url');
            if (!empty($url)) {
                mtrace("MRCA: Dispatching results to Webhook ($url)...");
                $token = get_config('local_mrca', 'webhook_token');
                $service = new \local_mrca\service\webhook_service();
                $payload = ($payloadtype === 'summary')
                    ? $this->build_summary_payload($scan, $alerts)
                    : $this->build_payload($scan);
                $service->send_report($payload, $url, $token);
            }
            else {
                mtrace("MRCA: Webhook URL not configured.");
            }
        }
    }

    /**
     * Scans capabilities defined by a specific plugin for risk indicators.
     *
     * @param string $component Component name.
     * @return array Capability findings for scoring.
     */
    private function scan_plugin_capabilities(string $component): array
    {
        $dir = \core_component::get_component_directory($component);
        $accessfile = $dir . '/db/access.php';

        $findings = [
            'critical_caps_non_admin' => [],
            'suspicious_overrides' => [],
        ];

        if (!file_exists($accessfile)) {
            return $findings;
        }

        // Critical capability patterns that a plugin should not normally define.
        $dangerous_patterns = [
            'delete', 'config', 'override', 'assign',
            'managecourse', 'manageactivities', 'backup',
        ];

        // Read the access.php and parse capabilities.
        $capabilities = [];
        // This safely loads the file's $capabilities array.
        include($accessfile);

        if (empty($capabilities)) {
            return $findings;
        }

        foreach ($capabilities as $capname => $capdef) {
            $riskbitmask = $capdef['riskbitmask'] ?? 0;

            // Check for RISK_XSS, RISK_CONFIG, RISK_PERSONAL, RISK_MANAGETRUST.
            if ($riskbitmask & (RISK_XSS | RISK_CONFIG | RISK_PERSONAL | RISK_MANAGETRUST)) {
                $findings['critical_caps_non_admin'][] = [
                    'capability' => $capname,
                    'risk' => $riskbitmask,
                ];
            }

            // Check capability name for dangerous patterns.
            foreach ($dangerous_patterns as $pattern) {
                if (stripos($capname, $pattern) !== false) {
                    $findings['suspicious_overrides'][] = [
                        'capability' => $capname,
                        'reason' => 'Capability name matches dangerous pattern: ' . $pattern,
                    ];
                    break;
                }
            }
        }

        return $findings;
    }

    /**
     * Builds a lightweight summary payload (scan totals + alerts only).
     *
     * @param \stdClass $scan The scan record.
     * @param array $alerts The correlation alerts generated.
     * @return array
     */
    private function build_summary_payload(\stdClass $scan, array $alerts): array
    {
        return [
            'type' => 'summary',
            'scan_id' => $scan->id,
            'timestamp' => $scan->timecreated,
            'total_score' => $scan->total_score,
            'site_risk_index' => $scan->site_risk_index,
            'plugins_scanned' => $scan->plugins_scanned,
            'roles_scanned' => $scan->roles_scanned,
            'alert_count' => count($alerts),
            'alerts' => array_map(function ($a) {
            return [
                    'type' => $a->type,
                    'severity' => $a->severity,
                    'component' => $a->component,
                    'description' => $a->description,
                ];
        }, $alerts),
        ];
    }

    /**
     * Builds the integration payload for a scan.
     *
     * @param \stdClass $scan The scan record.
     * @return array
     */
    private function build_payload(\stdClass $scan): array
    {
        global $DB;

        $payload = [
            'scan_id' => $scan->id,
            'timestamp' => $scan->timecreated,
            'total_score' => $scan->total_score,
            'site_risk_index' => $scan->site_risk_index,
            'plugins_scanned' => $scan->plugins_scanned,
            'roles_scanned' => $scan->roles_scanned,
            'results' => [],
        ];

        $results = $DB->get_records('local_mrca_scan_results', ['scanid' => $scan->id]);
        foreach ($results as $res) {
            $payload['results'][] = [
                'plugin' => $res->plugin,
                'risk_score' => $res->risk_score,
                'details' => json_decode($res->details),
            ];
        }

        return $payload;
    }
}