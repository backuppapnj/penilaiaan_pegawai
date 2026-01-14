<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Period;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vote>
 */
class VoteFactory extends Factory
{
    protected $model = Vote::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $scores = [];
        for ($i = 1; $i <= 7; $i++) {
            $scores[] = [
                'criterion_id' => $i,
                'score' => fake()->numberBetween(60, 100),
            ];
        }

        return [
            'period_id' => Period::factory(),
            'voter_id' => User::factory(),
            'employee_id' => Employee::factory(),
            'category_id' => Category::factory(),
            'scores' => $scores,
            'total_score' => collect($scores)->sum('score'),
        ];
    }
}
