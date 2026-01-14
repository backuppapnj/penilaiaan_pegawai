<?php

namespace Database\Factories;

use App\Models\Criterion;
use App\Models\Vote;
use App\Models\VoteDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VoteDetail>
 */
class VoteDetailFactory extends Factory
{
    protected $model = VoteDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vote_id' => Vote::factory(),
            'criterion_id' => Criterion::factory(),
            'score' => fake()->randomFloat(2, 1, 100),
        ];
    }
}
