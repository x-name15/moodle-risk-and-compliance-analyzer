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
 * Plugin settings.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Create the category for MRCA.
    $ADMIN->add('server', new admin_category('local_mrca', get_string('pluginname', 'local_mrca')));

    // Add the link to the Dashboard.
    $ADMIN->add('local_mrca', new admin_externalpage(
        'local_mrca_dashboard',
        get_string('dashboard_title', 'local_mrca'),
        new moodle_url('/local/mrca/index.php'),
        'local/mrca:view'
    ));

    // Settings page.
    $settings = new admin_settingpage('local_mrca_settings', get_string('settings', 'local_mrca'));
    $ADMIN->add('local_mrca', $settings);

    $settings->add(new admin_setting_configcheckbox(
        'local_mrca/autoscan_new_plugins',
        get_string('autoscan_new_plugins', 'local_mrca'),
        get_string('autoscan_new_plugins_desc', 'local_mrca'),
        0
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_mrca/scan_core_plugins',
        get_string('scan_core_plugins', 'local_mrca'),
        get_string('scan_core_plugins_desc', 'local_mrca'),
        0
    ));

    // Risk Thresholds.
    $settings->add(new admin_setting_heading(
        'local_mrca/risk_heading',
        get_string('risk_thresholds_heading', 'local_mrca'),
        get_string('risk_thresholds_heading_desc', 'local_mrca')
    ));

    $settings->add(new admin_setting_configtext(
        'local_mrca/threshold_high',
        get_string('threshold_high', 'local_mrca'),
        get_string('threshold_high_desc', 'local_mrca'),
        '61',
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_mrca/threshold_medium',
        get_string('threshold_medium', 'local_mrca'),
        get_string('threshold_medium_desc', 'local_mrca'),
        '31',
        PARAM_INT
    ));

    // External Integration.
    $settings->add(new admin_setting_heading(
        'local_mrca/integration_heading',
        get_string('integration_heading', 'local_mrca'),
        get_string('integration_heading_desc', 'local_mrca')
    ));

    // Integration Method Selector.
    $integrationoptions = [
        'disabled' => get_string('integration_method_disabled', 'local_mrca'),
        'webhook' => get_string('integration_method_webhook', 'local_mrca'),
    ];

    // Check if MIH is installed.
    if (core_component::get_component_directory('local_integrationhub')) {
        $integrationoptions['mih'] = get_string('integration_method_mih', 'local_mrca') .
            ' (' . get_string('recommended', 'local_mrca') . ')';
    } else {
        $settings->add(new admin_setting_description(
            'local_mrca/mih_missing',
            '',
            get_string('mih_missing_note', 'local_mrca')
        ));
    }

    $settings->add(new admin_setting_configselect(
        'local_mrca/integration_method',
        get_string('integration_method', 'local_mrca'),
        get_string('integration_method_desc', 'local_mrca'),
        'disabled',
        $integrationoptions
    ));

    // MIH Slug (Show if method == mih).
    $setting = new admin_setting_configtext(
        'local_mrca/mih_service_slug',
        get_string('mih_service_slug', 'local_mrca'),
        get_string('mih_service_slug_desc', 'local_mrca'),
        '',
        PARAM_TEXT
    );
    $setting->set_updatedcallback(function () {
        return true;
    });
    $settings->add($setting);
    $settings->hide_if('local_mrca/mih_service_slug', 'local_mrca/integration_method', 'neq', 'mih');

    // Webhook URL (Show if method == webhook).
    $setting = new admin_setting_configtext(
        'local_mrca/webhook_url',
        get_string('webhook_url', 'local_mrca'),
        get_string('webhook_url_desc', 'local_mrca'),
        '',
        PARAM_URL
    );
    $settings->add($setting);
    $settings->hide_if('local_mrca/webhook_url', 'local_mrca/integration_method', 'neq', 'webhook');

    // Webhook Token (Show if method == webhook).
    $setting = new admin_setting_configpasswordunmask(
        'local_mrca/webhook_token',
        get_string('webhook_token', 'local_mrca'),
        get_string('webhook_token_desc', 'local_mrca'),
        ''
    );
    $settings->add($setting);
    $settings->hide_if('local_mrca/webhook_token', 'local_mrca/integration_method', 'neq', 'webhook');

    // Report Dispatch Options (visible when integration is NOT disabled).
    $settings->add(new admin_setting_heading(
        'local_mrca/report_dispatch_heading',
        get_string('report_dispatch_heading', 'local_mrca'),
        get_string('report_dispatch_heading_desc', 'local_mrca')
    ));

    // Report trigger: always or critical only.
    $settings->add(new admin_setting_configselect(
        'local_mrca/report_trigger',
        get_string('report_trigger', 'local_mrca'),
        get_string('report_trigger_desc', 'local_mrca'),
        'always',
        [
            'always' => get_string('report_trigger_always', 'local_mrca'),
            'critical_only' => get_string('report_trigger_critical', 'local_mrca'),
        ]
    ));
    $settings->hide_if('local_mrca/report_trigger', 'local_mrca/integration_method', 'eq', 'disabled');

    // Report payload: full or summary.
    $settings->add(new admin_setting_configselect(
        'local_mrca/report_payload',
        get_string('report_payload', 'local_mrca'),
        get_string('report_payload_desc', 'local_mrca'),
        'full',
        [
            'full' => get_string('report_payload_full', 'local_mrca'),
            'summary' => get_string('report_payload_summary', 'local_mrca'),
        ]
    ));
    $settings->hide_if('local_mrca/report_payload', 'local_mrca/integration_method', 'eq', 'disabled');
}
