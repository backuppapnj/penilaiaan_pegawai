<?php

namespace Database\Factories;

use App\Models\Period;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Period>
 */
class PeriodFactory extends Factory
{
    protected $model = Period::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = fake()->numberBetween(2023, 2026);
        $semester = fake()->randomElement(['ganjil', 'genap']);

        return [
            'name' => "Semester {$semester} {$year}",
            'semester' => $semester,
            'year' => $year,
            'start_date' => fake()->date(),
            'end_date' => fake()->date(),
            'status' => fake()->randomElement(['draft', 'open', 'closed', 'announced']),
            'notes' => fake()->optional()->text(),
        ];
    }
}
