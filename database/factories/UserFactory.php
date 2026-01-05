<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // return [
        //     'name' => fake()->name(),
        //     'email' => fake()->unique()->safeEmail(),
        //     'email_verified_at' => now(),
        //     'password' => static::$password ??= Hash::make('password'),
        //     'remember_token' => Str::random(10),
        // ];

        $avatars = [
            '/images/avatars/avatar1.png',
            '/images/avatars/avatar2.png',
            '/images/avatars/avatar3.png',
            '/images/avatars/avatar4.png',
            '/images/avatars/avatar5.png',
        ];

        return [
            'first_name'  => $this->faker->firstName,
            'last_name'   => $this->faker->lastName,
            'username'    => $this->faker->unique()->userName,
            'email'       => $this->faker->unique()->safeEmail,
            'password'    => static::$password ??= Hash::make('password'),
            'role_id'     => 2, // 👈 normal user role
            'is_bot'      => false,
            'image'       => $this->faker->randomElement($avatars),
            'description' => $this->faker->sentence(10),
            // 'remember_token' => Str::random(10),
            // 'email_verified_at' => now(),
        ];
    }

    public function bot()
    {
        $avatars = [
            '/images/avatars/avatar1.png',
            '/images/avatars/avatar2.png',
            '/images/avatars/avatar3.png',
            '/images/avatars/avatar4.png',
            '/images/avatars/avatar5.png',
        ];

        return $this->state(function () use ($avatars) {
            return [
                'is_bot'      => true,
                'role_id'     => 2,
                'image'       => $this->faker->randomElement($avatars),
                'description' => $this->faker->sentence(12),
            ];
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
