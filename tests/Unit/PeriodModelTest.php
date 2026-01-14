<?php

namespace Tests\Unit;

use App\Models\Period;
use App\Models\Vote;
use App\Models\Score;
use App\Models\DisciplineScore;
use App\Models\Certificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_period_has_many_votes(): void
    {
        $period = Period::factory()->create();
        Vote::factory()->count(2)->create(['period_id' => $period->id]);

        $this->assertCount(2, $period->votes);
    }

    public function test_period_has_many_scores(): void
    {
        $period = Period::factory()->create();
        Score::factory()->count(2)->create(['period_id' => $period->id]);

        $this->assertCount(2, $period->scores);
    }

    public function test_period_has_many_discipline_scores(): void
    {
        $period = Period::factory()->create();
        DisciplineScore::factory()->count(2)->create(['period_id' => $period->id]);

        $this->assertCount(2, $period->disciplineScores);
    }

    public function test_period_has_many_certificates(): void
    {
        $period = Period::factory()->create();
        Certificate::factory()->count(2)->create(['period_id' => $period->id]);

        $this->assertCount(2, $period->certificates);
    }

    public function test_period_status_helpers(): void
    {
        $draft = Period::factory()->create(['status' => 'draft']);
        $open = Period::factory()->create(['status' => 'open']);
        $closed = Period::factory()->create(['status' => 'closed']);
        $announced = Period::factory()->create(['status' => 'announced']);

        $this->assertTrue($draft->isDraft());
        $this->assertTrue($open->isOpen());
        $this->assertTrue($closed->isClosed());
        $this->assertTrue($announced->isAnnounced());
    }

    public function test_start_date_formatted_attribute(): void
    {
        $period = Period::factory()->create(['start_date' => '2025-01-15']);
        $this->assertNotEmpty($period->start_date_formatted);
    }

    public function test_end_date_formatted_attribute(): void
    {
        $period = Period::factory()->create(['end_date' => '2025-01-15']);
        $this->assertNotEmpty($period->end_date_formatted);
    }
}