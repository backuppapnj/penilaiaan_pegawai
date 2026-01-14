<?php

namespace Tests\Unit;

use App\Models\Score;
use App\Models\Category;
use App\Models\Period;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_score_belongs_to_period(): void
    {
        $period = Period::factory()->create();
        $score = Score::factory()->create(['period_id' => $period->id]);

        $this->assertEquals($period->id, $score->period->id);
    }

    public function test_score_belongs_to_employee(): void
    {
        $employee = Employee::factory()->create();
        $score = Score::factory()->create(['employee_id' => $employee->id]);

        $this->assertEquals($employee->id, $score->employee->id);
    }

    public function test_score_belongs_to_category(): void
    {
        $category = Category::factory()->create();
        $score = Score::factory()->create(['category_id' => $category->id]);

        $this->assertEquals($category->id, $score->category->id);
    }

    public function test_score_winners_scope(): void
    {
        Score::factory()->create(['is_winner' => true]);
        Score::factory()->create(['is_winner' => false]);

        $this->assertCount(1, Score::winners()->get());
    }

    public function test_score_by_category_scope(): void
    {
        $category = Category::factory()->create();
        Score::factory()->create(['category_id' => $category->id]);
        Score::factory()->create();

        $this->assertCount(1, Score::byCategory($category->id)->get());
    }

    public function test_score_by_period_scope(): void
    {
        $period = Period::factory()->create();
        Score::factory()->create(['period_id' => $period->id]);
        Score::factory()->create();

        $this->assertCount(1, Score::byPeriod($period->id)->get());
    }

    public function test_score_ranked_scope(): void
    {
        Score::factory()->create(['rank' => 2]);
        Score::factory()->create(['rank' => 1]);

        $this->assertEquals(1, Score::ranked()->first()->rank);
    }
}