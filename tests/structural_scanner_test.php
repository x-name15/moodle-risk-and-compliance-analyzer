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
 * Unit tests for the structural scanner.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca;

defined('MOODLE_INTERNAL') || die();

use local_mrca\scanners\structural_scanner;

/**
 * Tests for structural_scanner function detection logic.
 *
 * @covers \local_mrca\scanners\structural_scanner
 */
class structural_scanner_test extends \advanced_testcase
{
    /** @var structural_scanner */
    private $scanner;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->scanner = new structural_scanner();
    }

    public function test_scan_core_module_returns_findings(): void {
        // mod_forum is a core module with version.php and lang.
        $findings = $this->scanner->scan('mod_forum');
        $this->assertArrayHasKey('has_version_file', $findings);
        $this->assertTrue($findings['has_version_file']);
        $this->assertArrayHasKey('structural_score', $findings);
    }

    public function test_scan_nonexistent_plugin(): void {
        $findings = $this->scanner->scan('mod_nonexistent_xyzzy_fake');
        $this->assertEquals(structural_scanner::SCORE_NO_VERSION_FILE, $findings['structural_score']);
    }

    public function test_file_get_contents_not_flagged(): void {
        // file_get_contents should NOT be in the unsafe functions list.
        $findings = $this->scanner->scan('mod_forum');
        $unsafe_funcs = array_column($findings['unsafe_calls'], 'function');
        $this->assertNotContains('file_get_contents', $unsafe_funcs);
    }

    public function test_error_not_flagged_as_deprecated(): void {
        // 'error' should NOT be in the deprecated functions list.
        $findings = $this->scanner->scan('mod_forum');
        $deprecated_funcs = array_column($findings['deprecated_calls'], 'function');
        $this->assertNotContains('error', $deprecated_funcs);
    }

    public function test_structural_score_capped_at_65(): void {
        $findings = $this->scanner->scan('mod_forum');
        $this->assertLessThanOrEqual(65, $findings['structural_score']);
    }

    public function test_self_scan_has_version_file(): void {
        // MRCA scanning itself should find version.php.
        $findings = $this->scanner->scan('local_mrca');
        $this->assertTrue($findings['has_version_file']);
    }

    public function test_self_scan_has_lang(): void {
        $findings = $this->scanner->scan('local_mrca');
        $this->assertTrue($findings['has_lang_file']);
    }

    public function test_self_scan_has_tests(): void {
        // Now that we have tests, this should be true.
        $findings = $this->scanner->scan('local_mrca');
        $this->assertTrue($findings['has_tests']);
    }

    public function test_self_scan_has_readme(): void {
        $findings = $this->scanner->scan('local_mrca');
        $this->assertTrue($findings['has_readme']);
    }
}
