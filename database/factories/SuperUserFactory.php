<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class SuperUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Super Admin',
            'surname' => 'User',
            'email' => 'admin@example.com',
            'phone' => '+79999999999',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ];
    }
}
