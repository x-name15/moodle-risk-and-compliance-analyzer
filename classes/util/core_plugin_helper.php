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
 * Core plugin helper — detects standard Moodle plugins.
 *
 * Used to prevent false positives by distinguishing core plugins
 * (maintained by Moodle HQ) from third-party plugins.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\util;

defined('MOODLE_INTERNAL') || die();

use core_plugin_manager;

class core_plugin_helper
{

    /** @var array|null Cache of standard plugins list. */
    private static $standard_plugins_cache = null;

    /**
     * Checks if a component is a standard (core) Moodle plugin.
     *
     * Uses core_plugin_manager::standard_plugins_list() to identify
     * plugins that ship with Moodle and are maintained by Moodle HQ.
     *
     * @param string $component Component name (e.g. 'mod_forum', 'block_html').
     * @return bool True if the plugin is a standard Moodle plugin.
     */
    public static function is_core_plugin(string $component): bool
    {
        // Core subsystems (e.g. 'core', 'core_course') are always core.
        if (strpos($component, 'core') === 0) {
            return true;
        }

        // Parse the component into type and name.
        $parts = explode('_', $component, 2);
        if (count($parts) < 2) {
            return false;
        }

        $type = $parts[0];
        $name = $parts[1];

        // Get the standard plugins list for this type.
        $standard = self::get_standard_plugins($type);

        return in_array($name, $standard);
    }

    /**
     * Gets the list of standard plugins for a given type.
     *
     * @param string $type Plugin type (e.g. 'mod', 'block', 'auth').
     * @return array List of standard plugin names for this type.
     */
    private static function get_standard_plugins(string $type): array
    {
        if (self::$standard_plugins_cache === null) {
            self::$standard_plugins_cache = [];
        }

        if (!isset(self::$standard_plugins_cache[$type])) {
            $list = core_plugin_manager::standard_plugins_list($type);
            self::$standard_plugins_cache[$type] = is_array($list) ? $list : [];
        }

        return self::$standard_plugins_cache[$type];
    }

    /**
     * Resets the internal cache. Useful for testing.
     */
    public static function reset_cache(): void
    {
        self::$standard_plugins_cache = null;
    }
}