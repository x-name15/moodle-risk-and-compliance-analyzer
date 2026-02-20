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

defined('MOODLE_INTERNAL') || die();

use local_mrca\engine\scoring_model;

/**
 * Tests for scoring_model computations.
 *
 * @covers \local_mrca\engine\scoring_model
 */
class scoring_model_test extends \advanced_testcase
{

    /** @var scoring_model */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new scoring_model();
    }

    // ==================== Plugin Risk ====================

    public function test_plugin_risk_sum(): void
    {
        $result = $this->model->calculate_plugin_risk(20, 30, 10);
        $this->assertEquals(60, $result);
    }

    public function test_plugin_risk_zeroes(): void
    {
        $result = $this->model->calculate_plugin_risk(0, 0, 0);
        $this->assertEquals(0, $result);
    }

    // ==================== Role Risk ====================

    public function test_role_risk_sum(): void
    {
        $result = $this->model->calculate_role_risk(15, 25, 10);
        $this->assertEquals(50, $result);
    }

    // ==================== Correlation Multiplier ====================

    public function test_correlation_applied_when_both_above_threshold(): void
    {
        // Both above default threshold (40).
        $result = $this->model->apply_correlation(50, 50);
        $this->assertEquals(150.0, $result); // (50+50) * 1.5
    }

    public function test_correlation_not_applied_when_one_below_threshold(): void
    {
        $result = $this->model->apply_correlation(50, 30);
        $this->assertEquals(80.0, $result); // Simple sum.
    }

    public function test_correlation_not_applied_when_both_below_threshold(): void
    {
        $result = $this->model->apply_correlation(20, 30);
        $this->assertEquals(50.0, $result);
    }

    public function test_correlation_custom_threshold(): void
    {
        $result = $this->model->apply_correlation(15, 15, 10);
        $this->assertEquals(45.0, $result); // (15+15) * 1.5
    }

    public function test_correlation_at_exact_threshold_not_applied(): void
    {
        // At exact threshold, not above.
        $result = $this->model->apply_correlation(40, 40);
        $this->assertEquals(80.0, $result); // Not applied, needs to be > threshold.
    }

    // ==================== Site Risk Index ====================

    public function test_site_risk_index_normal(): void
    {
        $result = $this->model->calculate_site_risk_index(50, 200);
        $this->assertEquals(25.0, $result);
    }

    public function test_site_risk_index_zero_max(): void
    {
        $result = $this->model->calculate_site_risk_index(50, 0);
        $this->assertEquals(0.0, $result);
    }

    public function test_site_risk_index_capped_at_100(): void
    {
        $result = $this->model->calculate_site_risk_index(300, 200);
        $this->assertEquals(100.0, $result);
    }

    public function test_site_risk_index_zero_risk(): void
    {
        $result = $this->model->calculate_site_risk_index(0, 500);
        $this->assertEquals(0.0, $result);
    }

    // ==================== Site Risk Classification ====================

    public function test_classify_healthy(): void
    {
        $this->assertEquals('healthy', $this->model->classify_site_risk(0));
        $this->assertEquals('healthy', $this->model->classify_site_risk(20));
    }

    public function test_classify_low(): void
    {
        $this->assertEquals('low', $this->model->classify_site_risk(21));
        $this->assertEquals('low', $this->model->classify_site_risk(40));
    }

    public function test_classify_moderate(): void
    {
        $this->assertEquals('moderate', $this->model->classify_site_risk(41));
        $this->assertEquals('moderate', $this->model->classify_site_risk(60));
    }

    public function test_classify_high(): void
    {
        $this->assertEquals('high', $this->model->classify_site_risk(61));
        $this->assertEquals('high', $this->model->classify_site_risk(80));
    }

    public function test_classify_critical(): void
    {
        $this->assertEquals('critical', $this->model->classify_site_risk(81));
        $this->assertEquals('critical', $this->model->classify_site_risk(100));
    }
}