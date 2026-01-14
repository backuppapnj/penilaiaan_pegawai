<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Period;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_vote_belongs_to_period(): void
    {
        $period = Period::factory()->create();
        $vote = Vote::factory()->create(['period_id' => $period->id]);

        $this->assertInstanceOf(Period::class, $vote->period);
        $this->assertEquals($period->id, $vote->period->id);
    }

    public function test_vote_belongs_to_voter(): void
    {
        $user = User::factory()->create();
        $vote = Vote::factory()->create(['voter_id' => $user->id]);

        $this->assertInstanceOf(User::class, $vote->voter);
        $this->assertEquals($user->id, $vote->voter->id);
    }

    public function test_vote_belongs_to_employee(): void
    {
        $employee = Employee::factory()->create();
        $vote = Vote::factory()->create(['employee_id' => $employee->id]);

        $this->assertInstanceOf(Employee::class, $vote->employee);
        $this->assertEquals($employee->id, $vote->employee->id);
    }

    public function test_vote_belongs_to_category(): void
    {
        $category = Category::factory()->create();
        $vote = Vote::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $vote->category);
        $this->assertEquals($category->id, $vote->category->id);
    }

    public function test_vote_has_many_details(): void
    {
        $vote = Vote::factory()->create();
        VoteDetail::factory()->count(2)->create(['vote_id' => $vote->id]);

        $this->assertCount(2, $vote->voteDetails);
        $this->assertInstanceOf(VoteDetail::class, $vote->voteDetails->first());
    }
}