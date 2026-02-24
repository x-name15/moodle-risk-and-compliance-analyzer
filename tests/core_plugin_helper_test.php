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
 * Unit tests for the core plugin helper.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca;

use local_mrca\util\core_plugin_helper;

/**
 * Tests for core_plugin_helper detection.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_mrca\util\core_plugin_helper
 */
final class core_plugin_helper_test extends \advanced_testcase
{
    /**
     * Set up the test environment.
     *
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        core_plugin_helper::reset_cache();
    }

    /**
     * Test core module detection.
     *
     * @return void
     */
    public function test_core_module_detected(): void {
        $this->assertTrue(core_plugin_helper::is_core_plugin('mod_forum'));
        $this->assertTrue(core_plugin_helper::is_core_plugin('mod_assign'));
        $this->assertTrue(core_plugin_helper::is_core_plugin('mod_quiz'));
    }

    /**
     * Test core block detection.
     *
     * @return void
     */
    public function test_core_block_detected(): void {
        $this->assertTrue(core_plugin_helper::is_core_plugin('block_html'));
        $this->assertTrue(core_plugin_helper::is_core_plugin('block_navigation'));
    }

    /**
     * Test core auth detection.
     *
     * @return void
     */
    public function test_core_auth_detected(): void {
        $this->assertTrue(core_plugin_helper::is_core_plugin('auth_manual'));
        $this->assertTrue(core_plugin_helper::is_core_plugin('auth_email'));
    }

    /**
     * Test core subsystem detection.
     *
     * @return void
     */
    public function test_core_subsystem_detected(): void {
        $this->assertTrue(core_plugin_helper::is_core_plugin('core'));
        $this->assertTrue(core_plugin_helper::is_core_plugin('core_course'));
    }

    /**
     * Test that third-party plugins are correctly identified as non-core.
     *
     * @return void
     */
    public function test_third_party_plugin_not_detected(): void {
        $this->assertFalse(core_plugin_helper::is_core_plugin('mod_nonexistent_xyzzy'));
        $this->assertFalse(core_plugin_helper::is_core_plugin('local_mrca'));
    }

    /**
     * Test invalid component formats.
     *
     * @return void
     */
    public function test_invalid_component_format(): void {
        $this->assertFalse(core_plugin_helper::is_core_plugin(''));
        $this->assertFalse(core_plugin_helper::is_core_plugin('nounderscore'));
    }

    /**
     * Test cache reset functionality.
     *
     * @return void
     */
    public function test_cache_reset(): void {
        core_plugin_helper::is_core_plugin('mod_forum');
        core_plugin_helper::reset_cache();
        $this->assertTrue(core_plugin_helper::is_core_plugin('mod_forum'));
    }
}
