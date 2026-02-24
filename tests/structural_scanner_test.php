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

use local_mrca\scanners\structural_scanner;

/**
 * Tests for structural_scanner function detection logic.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_mrca\scanners\structural_scanner
 */
final class structural_scanner_test extends \advanced_testcase
{
    /** @var structural_scanner The scanner instance being tested. */
    private $scanner;

    /**
     * Set up the test environment.
     *
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->scanner = new structural_scanner();
    }

    /**
     * Test that scanning a core module returns expected findings.
     *
     * @return void
     */
    public function test_scan_core_module_returns_findings(): void {
        // Mod_forum is a core module with version.php and lang.
        $findings = $this->scanner->scan('mod_forum');
        $this->assertArrayHasKey('has_version_file', $findings);
        $this->assertTrue($findings['has_version_file']);
        $this->assertArrayHasKey('structural_score', $findings);
    }

    /**
     * Test scanning a nonexistent plugin.
     *
     * @return void
     */
    public function test_scan_nonexistent_plugin(): void {
        $findings = $this->scanner->scan('mod_nonexistent_xyzzy_fake');
        $this->assertEquals(structural_scanner::SCORE_NO_VERSION_FILE, $findings['structural_score']);
    }

    /**
     * Test that file_get_contents is not flagged as unsafe.
     *
     * @return void
     */
    public function test_file_get_contents_not_flagged(): void {
        // The file_get_contents function should NOT be in the unsafe functions list.
        $findings = $this->scanner->scan('mod_forum');
        $unsafefuncs = array_column($findings['unsafe_calls'], 'function');
        $this->assertNotContains('file_get_contents', $unsafefuncs);
    }

    /**
     * Test that 'error' is not flagged as deprecated.
     *
     * @return void
     */
    public function test_error_not_flagged_as_deprecated(): void {
        // The 'error' string should NOT be in the deprecated functions list.
        $findings = $this->scanner->scan('mod_forum');
        $deprecatedfuncs = array_column($findings['deprecated_calls'], 'function');
        $this->assertNotContains('error', $deprecatedfuncs);
    }

    /**
     * Test that the structural score is capped at 65.
     *
     * @return void
     */
    public function test_structural_score_capped_at_65(): void {
        $findings = $this->scanner->scan('mod_forum');
        $this->assertLessThanOrEqual(65, $findings['structural_score']);
    }

    /**
     * Test that scanning itself finds the version file.
     *
     * @return void
     */
    public function test_self_scan_has_version_file(): void {
        // MRCA scanning itself should find version.php.
        $findings = $this->scanner->scan('local_mrca');
        $this->assertTrue($findings['has_version_file']);
    }

    /**
     * Test that scanning itself finds the lang folder.
     *
     * @return void
     */
    public function test_self_scan_has_lang(): void {
        $findings = $this->scanner->scan('local_mrca');
        $this->assertTrue($findings['has_lang_file']);
    }

    /**
     * Test that scanning itself finds its own tests.
     *
     * @return void
     */
    public function test_self_scan_has_tests(): void {
        // Now that we have tests, this should be true.
        $findings = $this->scanner->scan('local_mrca');
        $this->assertTrue($findings['has_tests']);
    }

    /**
     * Test that scanning itself finds the README.
     *
     * @return void
     */
    public function test_self_scan_has_readme(): void {
        $findings = $this->scanner->scan('local_mrca');
        $this->assertTrue($findings['has_readme']);
    }
}
