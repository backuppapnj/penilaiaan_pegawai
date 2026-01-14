<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Criterion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Criterion>
 */
class CriterionFactory extends Factory
{
    protected $model = Criterion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->words(3, true),
            'bobot' => fake()->randomFloat(2, 1, 20),
            'category_id' => Category::factory(),
            'urutan' => fake()->numberBetween(1, 10),
        ];
    }
}
