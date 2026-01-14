<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Criterion;
use App\Models\Vote;
use App\Models\Score;
use App\Models\Certificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_has_many_employees(): void
    {
        $category = Category::factory()->create();
        Employee::factory()->count(3)->create(['category_id' => $category->id]);

        $this->assertCount(3, $category->employees);
        $this->assertInstanceOf(Employee::class, $category->employees->first());
    }

    public function test_category_has_many_criteria(): void
    {
        $category = Category::factory()->create();
        Criterion::factory()->count(2)->create(['category_id' => $category->id]);

        $this->assertCount(2, $category->criteria);
        $this->assertInstanceOf(Criterion::class, $category->criteria->first());
    }

    public function test_category_has_many_votes(): void
    {
        $category = Category::factory()->create();
        Vote::factory()->count(2)->create(['category_id' => $category->id]);

        $this->assertCount(2, $category->votes);
        $this->assertInstanceOf(Vote::class, $category->votes->first());
    }

    public function test_category_has_many_scores(): void
    {
        $category = Category::factory()->create();
        Score::factory()->count(2)->create(['category_id' => $category->id]);

        $this->assertCount(2, $category->scores);
        $this->assertInstanceOf(Score::class, $category->scores->first());
    }

    public function test_category_has_many_certificates(): void
    {
        $category = Category::factory()->create();
        Certificate::factory()->count(2)->create(['category_id' => $category->id]);

        $this->assertCount(2, $category->certificates);
        $this->assertInstanceOf(Certificate::class, $category->certificates->first());
    }
}