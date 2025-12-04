<?php

namespace Database\Factories;

use App\Models\ScheduleType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScheduleType>
 */
class ScheduleTypeFactory extends Factory
{
    protected $model = ScheduleType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['2/2', '5/2']),
            'hours_number' => 12,
        ];
    }

    /**
     * График "2 через 2" (2 рабочих дня, 2 выходных)
     */
    public function twoByTwo(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => '2/2',
            'hours_number' => 12,
        ]);
    }

    /**
     * График "5 через 2" (5 рабочих дней, 2 выходных)
     */
    public function fiveByTwo(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => '5/2',
            'hours_number' => 9,
        ]);
    }
}
