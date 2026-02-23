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
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\task;

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

/**
 * Runs all scanner layers and stores results.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class run_scan extends scheduled_task {
    /**
     * Get the name of the task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'local_mrca') . ' - Scheduled Scan';
    }

    /**
     * Execute the complete MRCA scan.
     */
    public function execute() {
        global $DB;

        mtrace("MRCA: Starting Scheduled Risk & Compliance Scan...");

        $scancore = (bool)get_config('local_mrca', 'scan_core_plugins');

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

        // ====== LAYER 1: Scan all plugins ======
        $pluginresults = $this->process_plugins($scan, $scancore);

        // ====== LAYER 2: Scan roles ======
        $roleresults = $this->process_roles($scan);

        // ====== LAYER 3: Correlation engine ======
        mtrace("MRCA: Running correlation analysis...");
        $correlationengine = new correlation_engine();
        $alerts = $correlationengine->evaluate($pluginresults['pluginrisks'], $roleresults['rolerisks'], $scan->id);

        foreach ($alerts as $alert) {
            $DB->insert_record('local_mrca_alerts', $alert);
        }

        // ====== LAYER 4: Calculate site risk index ======
        $siterisk = site_risk::calculate(
            $pluginresults['totalscore'],
            $roleresults['totalrisk'],
            $pluginresults['scanned'],
            $roleresults['scanned']
        );

        // Update scan record.
        $scan->status = 1; // Completed.
        $scan->total_score = $pluginresults['totalscore'];
        $scan->site_risk_index = $siterisk->index;
        $scan->plugins_scanned = $pluginresults['scanned'];
        $scan->roles_scanned = $roleresults['scanned'];
        $scan->timemodified = time();
        $DB->update_record('local_mrca_scans', $scan);

        mtrace("MRCA: Scan completed. Site Risk Index: {$siterisk->index}/100 ({$siterisk->classification})");
        mtrace("MRCA: Plugins scanned: {$pluginresults['scanned']}" . ($pluginresults['skipped'] > 0 ? " (core plugins skipped: {$pluginresults['skipped']})" : ""));
        mtrace("MRCA: Alerts generated: " . count($alerts));

        // ====== LAYER 5: External integration dispatch ======
        $this->dispatch_report($scan, $alerts);
    }

    /**
     * Scans all plugins.
     *
     * @param \stdClass $scan Scan record.
     * @param bool $scancore Whether to scan core plugins.
     * @return array Array containing scan metrics and risks.
     */
    private function process_plugins(\stdClass $scan, bool $scancore): array {
        global $DB;

        $privacyscanner = new privacy_scanner();
        $dependencyscanner = new dependency_scanner();
        $structuralscanner = new structural_scanner();
        $riskengine = new risk_engine();
        $cryptoanalyzer = new \local_mrca\heuristics\crypto_analyzer();

        $plugins = $privacyscanner->get_all_plugins();
        $totalscore = 0;
        $pluginsscanned = 0;
        $coreskipped = 0;
        $allpluginrisks = [];

        foreach ($plugins as $type => $pluginsbytype) {
            foreach ($pluginsbytype as $pluginname => $plugininfo) {
                $component = $plugininfo->component;

                // Skip core (standard) Moodle plugins unless explicitly enabled.
                if (!$scancore && core_plugin_helper::is_core_plugin($component)) {
                    $coreskipped++;
                    continue;
                }
                
                // Verify plugin directory exists before scanning.
                $plugindir = \core_component::get_component_directory($component);
                if (empty($plugindir) || !is_dir($plugindir)) {
                    continue;
                }

                $pluginsscanned++;
                mtrace("MRCA: Scanning $component...");

                // Privacy layer.
                $apicheck = $privacyscanner->scan_plugin($component);
                $dbcheck = $privacyscanner->scan_db_structure($component);

                if (!empty($dbcheck)) {
                    $dbcheck = $privacyscanner->check_content_analysis($dbcheck, $cryptoanalyzer);
                }

                foreach ($dbcheck as &$finding) {
                    if (!isset($finding['component'])) {
                        $finding['component'] = $component;
                    }
                }

                $privacyscore = $riskengine->calculate_privacy_score($apicheck, $dbcheck);

                // Dependency layer.
                $depfindings = $dependencyscanner->scan($component);
                $dependencyscore = $riskengine->calculate_dependency_score($depfindings);

                // Structural layer.
                $structfindings = $structuralscanner->scan($component);
                $structuralscore = $structfindings['structural_score'] ?? 0;

                // Capability definition layer.
                $capabilityscore = 0;
                $plugincapfindings = $this->scan_plugin_capabilities($component);
                if (!empty($plugincapfindings)) {
                    $capabilityscore = $riskengine->calculate_capability_score($plugincapfindings);
                }

                // Total plugin risk combines all layers.
                $plugintotal = $privacyscore + $dependencyscore + $capabilityscore + $structuralscore;

                // Save legacy scan results.
                if ($plugintotal > 0) {
                    $result = new \stdClass();
                    $result->scanid = $scan->id;
                    $result->plugin = $component;
                    $result->risk_score = $plugintotal;
                    $result->has_privacy_provider = $apicheck['has_privacy_provider'] ? 1 : 0;
                    $result->details = json_encode([
                        'db_findings' => $dbcheck,
                        'dep_findings' => $depfindings,
                        'struct_findings' => $structfindings,
                    ]);
                    $DB->insert_record('local_mrca_scan_results', $result);
                }

                // Save multi-score plugin risk.
                $pr = new plugin_risk();
                $pr->scanid = $scan->id;
                $pr->component = $component;
                $pr->privacy_score = $privacyscore;
                $pr->dependency_score = $dependencyscore;
                $pr->capability_score = $capabilityscore;
                $pr->save();

                $allpluginrisks[$component] = [
                    'privacy_score' => $privacyscore,
                    'dependency_score' => $dependencyscore,
                    'capability_score' => $capabilityscore,
                    'structural_score' => $structuralscore,
                    'has_privacy_provider' => $apicheck['has_privacy_provider'],
                    'deprecated_calls' => count($structfindings['deprecated_calls'] ?? []),
                    'dep_findings' => $depfindings,
                ];

                $totalscore += $plugintotal;

                // Trigger event if risk is high.
                if ($plugintotal >= 61) {
                    \local_mrca\event\high_risk_detected::create([
                        'context' => \context_system::instance(),
                        'objectid' => $scan->id,
                        'other' => [
                            'plugin' => $component,
                            'score' => $plugintotal,
                        ],
                    ])->trigger();
                }
            }
        }

        return [
            'totalscore' => $totalscore,
            'scanned' => $pluginsscanned,
            'skipped' => $coreskipped,
            'pluginrisks' => $allpluginrisks,
        ];
    }

    /**
     * Scans roles and saves their risk profiles.
     *
     * @param \stdClass $scan Scan record.
     * @return array Array containing scan metrics and risks.
     */
    private function process_roles(\stdClass $scan): array {
        $capabilityscanner = new capability_scanner();
        mtrace("MRCA: Scanning role permissions...");

        $roleresults = $capabilityscanner->scan_roles();
        $totalrolerisk = 0;
        $rolesscanned = count($roleresults);
        $allrolerisks = [];

        foreach ($roleresults as $roleid => $roledata) {
            $rr = new role_risk();
            $rr->scanid = $scan->id;
            $rr->roleid = $roleid;
            $rr->risk_score = $roledata['risk_score'];
            $rr->critical_cap_count = $roledata['critical_cap_count'];
            $rr->save();

            $totalrolerisk += $roledata['risk_score'];
            $allrolerisks[$roleid] = $roledata;
        }

        return [
            'totalrisk' => $totalrolerisk,
            'scanned' => $rolesscanned,
            'rolerisks' => $allrolerisks,
        ];
    }

    /**
     * Handles the dispatch of reports via MIH or Webhook.
     *
     * @param \stdClass $scan The scan record.
     * @param array $alerts Alert records.
     * @return void
     */
    private function dispatch_report(\stdClass $scan, array $alerts): void {
        $method = get_config('local_mrca', 'integration_method');
        $trigger = get_config('local_mrca', 'report_trigger') ?: 'always';
        $payloadtype = get_config('local_mrca', 'report_payload') ?: 'full';

        $shoulddispatch = true;
        if ($trigger === 'critical_only' && empty($alerts)) {
            $shoulddispatch = false;
            mtrace("MRCA: Skipping dispatch — report_trigger=critical_only and no alerts found.");
        }

        if (!$shoulddispatch) {
            return;
        }

        if ($method === 'mih') {
            $this->dispatch_to_mih($scan, $alerts, $payloadtype);
        } else if ($method === 'webhook') {
            $this->dispatch_to_webhook($scan, $alerts, $payloadtype);
        }
    }

    /**
     * Dispatches the report to the Integration Hub.
     *
     * @param \stdClass $scan The scan record.
     * @param array $alerts Alert records.
     * @param string $payloadtype Summary or full.
     * @return void
     */
    private function dispatch_to_mih(\stdClass $scan, array $alerts, string $payloadtype): void {
        if (\core_component::get_component_directory('local_integrationhub') && class_exists('\local_integrationhub\mih')) {
            $slug = get_config('local_mrca', 'mih_service_slug');
            if (!empty($slug)) {
                mtrace("MRCA: Dispatching results to Integration Hub (Service: $slug)...");
                try {
                    $payload = ($payloadtype === 'summary')
                        ? $this->build_summary_payload($scan, $alerts)
                        : $this->build_payload($scan);
                    \local_integrationhub\mih::request($slug, '/', $payload, 'POST');
                    mtrace("MRCA: MIH Dispatch Successful.");
                } catch (\Exception $e) {
                    mtrace("MRCA: MIH Dispatch Failed: " . $e->getMessage());
                }
            } else {
                mtrace("MRCA: MIH Service Slug not configured.");
            }
        } else {
            mtrace("MRCA: Integration Hub selected but not installed.");
        }
    }

    /**
     * Dispatches the report via Webhook.
     *
     * @param \stdClass $scan The scan record.
     * @param array $alerts Alert records.
     * @param string $payloadtype Summary or full.
     * @return void
     */
    private function dispatch_to_webhook(\stdClass $scan, array $alerts, string $payloadtype): void {
        $url = get_config('local_mrca', 'webhook_url');
        if (!empty($url)) {
            mtrace("MRCA: Dispatching results to Webhook ($url)...");
            $token = get_config('local_mrca', 'webhook_token');
            $service = new \local_mrca\service\webhook_service();
            $payload = ($payloadtype === 'summary')
                ? $this->build_summary_payload($scan, $alerts)
                : $this->build_payload($scan);
            $service->send_report($payload, $url, $token);
        } else {
            mtrace("MRCA: Webhook URL not configured.");
        }
    }

    /**
     * Scans capabilities defined by a specific plugin for risk indicators.
     *
     * @param string $component Component name.
     * @return array Capability findings for scoring.
     */
    private function scan_plugin_capabilities(string $component): array {
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
        $dangerouspatterns = [
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
            foreach ($dangerouspatterns as $pattern) {
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
    private function build_summary_payload(\stdClass $scan, array $alerts): array {
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
    private function build_payload(\stdClass $scan): array {
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
