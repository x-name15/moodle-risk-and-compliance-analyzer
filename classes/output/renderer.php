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
 * Plugin renderer.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\output;

use plugin_renderer_base;

/**
 * Renderer class for the MRCA plugin.
 *
 * Handles the output generation by processing renderables and sending them to Mustache templates.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {
    /**
     * Renders the dashboard.
     *
     * @param \local_mrca\reporting\dashboard $page The dashboard renderable object.
     * @return string HTML for the page.
     */
    public function render_dashboard(\local_mrca\reporting\dashboard $page) {
        $data = $page->export_for_template($this);
        return $this->render_from_template('local_mrca/dashboard', $data);
    }
}
