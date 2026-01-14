<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Period;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Certificate>
 */
class CertificateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'certificate_id' => 'CERT-' . strtoupper(Str::random(12)),
            'employee_id' => Employee::factory(),
            'period_id' => Period::factory(),
            'category_id' => Category::factory(),
            'rank' => '1',
            'score' => fake()->randomFloat(2, 80, 100),
            'qr_code_path' => 'qr-codes/test.png',
            'pdf_path' => 'certificates/test.pdf',
            'issued_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
