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
 * Plugin index page — MRCA Dashboard.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/mrca:view', $context);

// Set up page.
$PAGE->set_context($context);
$PAGE->set_url(new \moodle_url('/local/mrca/index.php'));
$PAGE->set_title(get_string('dashboard_title', 'local_mrca'));
$PAGE->set_heading(get_string('pluginname', 'local_mrca'));

// Process actions.
$action = optional_param('action', '', PARAM_ALPHANUMEXT);
if ($action === 'scan' && confirm_sesskey()) {
    $task = new \local_mrca\task\run_scan();
    $task->execute();
    redirect($PAGE->url, get_string('scan_completed', 'local_mrca'));
} else if ($action === 'download_pdf' && confirm_sesskey()) {
    $scanid = required_param('scanid', PARAM_INT);
    $generator = new \local_mrca\reporting\pdf_generator();
    $generator->generate_report($scanid);
    exit;
} else if ($action === 'download_csv' && confirm_sesskey()) {
    $scanid = required_param('scanid', PARAM_INT);
    $generator = new \local_mrca\reporting\csv_generator();
    $generator->generate_report($scanid);
    exit;
} else if ($action === 'download_json' && confirm_sesskey()) {
    $scanid = required_param('scanid', PARAM_INT);
    $generator = new \local_mrca\reporting\export_json();
    $generator->generate_report($scanid);
    exit;
} else if ($action === 'whitelist_add' && confirm_sesskey()) {
    $component = required_param('component', PARAM_TEXT);
    $table = required_param('table', PARAM_TEXT);
    $field = required_param('field', PARAM_TEXT);

    $manager = new \local_mrca\manager\whitelist_manager();
    $manager->add($component, $table, $field, $USER->id);

    echo json_encode(['success' => true]);
    exit;
} else if ($action === 'whitelist_remove' && confirm_sesskey()) {
    $id = required_param('id', PARAM_INT);

    $manager = new \local_mrca\manager\whitelist_manager();
    $manager->remove($id);

    echo json_encode(['success' => true]);
    exit;
} else if ($action === 'send_single_report' && confirm_sesskey()) {
    // Manually send a single plugin report to integration.
    $resultid = required_param('resultid', PARAM_INT);
    $result = $DB->get_record('local_mrca_scan_results', ['id' => $resultid], '*', MUST_EXIST);

    $method = get_config('local_mrca', 'integration_method');
    $success = false;
    $message = '';

    // Prepare single payload.
    $payload = [
        'event' => 'manual_risk_report',
        'timestamp' => time(),
        'plugin' => $result->plugin,
        'risk_score' => $result->risk_score,
        'details' => json_decode($result->details),
    ];

    if ($method === 'mih') {
        if (
            \core\component::get_component_directory('local_integrationhub') &&
                class_exists('\local_integrationhub\mih')
        ) {
            $slug = get_config('local_mrca', 'mih_service_slug');
            try {
                \local_integrationhub\mih::request($slug, '/', $payload, 'POST');
                $success = true;
            } catch (\Exception $e) {
                $message = $e->getMessage();
            }
        } else {
            $message = 'MIH not installed/configured.';
        }
    } else if ($method === 'webhook') {
        $url = get_config('local_mrca', 'webhook_url');
        $token = get_config('local_mrca', 'webhook_token');
        $service = new \local_mrca\service\webhook_service();
        if ($service->send_report($payload, $url, $token)) {
            $success = true;
        } else {
            $message = 'Webhook failed.';
        }
    } else {
        $message = 'Integration disabled.';
    }

    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// Output.
$renderer = $PAGE->get_renderer('local_mrca');
$dashboard = new \local_mrca\reporting\dashboard();
$templatedata = $dashboard->export_for_template($renderer);

// Inject Chart.js before the dashboard renders.
echo $renderer->header();
echo '<script src="' . new \moodle_url('/local/mrca/assets/min/chart.umd.min.js') . '"></script>';
echo $renderer->render_from_template('local_mrca/dashboard', $templatedata);

// Initialize AMD dashboard module with chart + pagination data.
$PAGE->requires->js_call_amd('local_mrca/dashboard', 'init', [
    json_decode($templatedata['chart_data'] ?? '{}'),
    json_decode($templatedata['trend_data'] ?? '{}'),
    $templatedata['scan_url'],
    sesskey(),
]);

echo $renderer->footer();
