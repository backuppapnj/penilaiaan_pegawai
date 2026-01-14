<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Period;
use App\Models\Score;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Score>
 */
class ScoreFactory extends Factory
{
    protected $model = Score::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'period_id' => Period::factory(),
            'employee_id' => Employee::factory(),
            'category_id' => Category::factory(),
            'weighted_score' => fake()->randomFloat(2, 60, 100),
            'rank' => fake()->numberBetween(1, 30),
            'is_winner' => false,
            'score_details' => [],
        ];
    }
}