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
 * Unit tests for the scoring model.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca;

use local_mrca\engine\scoring_model;

/**
 * Tests for scoring_model computations.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_mrca\engine\scoring_model
 */
final class scoring_model_test extends \advanced_testcase
{
    /** @var scoring_model The scoring model instance. */
    private $model;

    /**
     * Set up the test environment.
     *
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->model = new scoring_model();
    }

    /**
     * Test plugin risk calculation.
     *
     * @return void
     */
    public function test_plugin_risk_sum(): void {
        $result = $this->model->calculate_plugin_risk(20, 30, 10);
        $this->assertEquals(60, $result);
    }

    /**
     * Test role risk calculation.
     *
     * @return void
     */
    public function test_role_risk_sum(): void {
        $result = $this->model->calculate_role_risk(15, 25, 10);
        $this->assertEquals(50, $result);
    }

    /**
     * Test correlation application.
     *
     * @return void
     */
    public function test_correlation_applied_when_both_above_threshold(): void {
        $result = $this->model->apply_correlation(50, 50);
        $this->assertEquals(150.0, $result);
    }

    /**
     * Test site risk index calculation.
     *
     * @return void
     */
    public function test_site_risk_index_normal(): void {
        $result = $this->model->calculate_site_risk_index(50, 200);
        $this->assertEquals(25.0, $result);
    }

    /**
     * Test site risk classification as critical.
     *
     * @return void
     */
    public function test_classify_critical(): void {
        $this->assertEquals('critical', $this->model->classify_site_risk(81));
        $this->assertEquals('critical', $this->model->classify_site_risk(100));
    }
}
