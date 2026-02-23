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
 * Plugin uninstall — cleans up plugin settings on removal.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Custom uninstall procedure.
 *
 * Removes plugin configuration settings that are not automatically
 * cleaned up by Moodle's standard uninstall process.
 *
 * @return bool
 */
function xmldb_local_mrca_uninstall() {
    // Remove all plugin settings.
    $settings = [
        'autoscan_new_plugins',
        'scan_core_plugins',
        'threshold_high',
        'threshold_medium',
        'integration_method',
        'mih_service_slug',
        'webhook_url',
        'webhook_token',
        'report_trigger',
        'report_payload',
    ];

    foreach ($settings as $setting) {
        unset_config($setting, 'local_mrca');
    }

    return true;
}
