<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Employee;
use App\Models\User;
use App\Models\Vote;
use App\Models\Score;
use App\Models\Certificate;
use App\Models\DisciplineScore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_belongs_to_category(): void
    {
        $category = Category::factory()->create();
        $employee = Employee::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $employee->category);
        $this->assertEquals($category->id, $employee->category->id);
    }

    public function test_employee_has_one_user(): void
    {
        $employee = Employee::factory()->create();
        $user = User::factory()->create(['employee_id' => $employee->id]);

        $this->assertInstanceOf(User::class, $employee->user);
        $this->assertEquals($user->id, $employee->user->id);
    }

    public function test_employee_has_many_votes_received(): void
    {
        $employee = Employee::factory()->create();
        Vote::factory()->count(2)->create(['employee_id' => $employee->id]);

        $this->assertCount(2, $employee->votesReceived);
    }

    public function test_employee_has_many_scores(): void
    {
        $employee = Employee::factory()->create();
        Score::factory()->count(2)->create(['employee_id' => $employee->id]);

        $this->assertCount(2, $employee->scores);
    }

    public function test_employee_has_many_discipline_scores(): void
    {
        $employee = Employee::factory()->create();
        DisciplineScore::factory()->count(2)->create(['employee_id' => $employee->id]);

        $this->assertCount(2, $employee->disciplineScores);
    }

    public function test_employee_has_many_certificates(): void
    {
        $employee = Employee::factory()->create();
        Certificate::factory()->count(2)->create(['employee_id' => $employee->id]);

        $this->assertCount(2, $employee->certificates);
    }
}