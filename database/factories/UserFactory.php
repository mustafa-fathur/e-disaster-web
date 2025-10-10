<?php

namespace Database\Factories;

use App\Enums\UserStatusEnum;
use App\Enums\UserTypeEnum;
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
        return [
            'type' => UserTypeEnum::VOLUNTEER,
            'status' => UserStatusEnum::REGISTERED,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified' => true,
            'password' => static::$password ??= Hash::make('password'),
            'timezone' => fake()->timezone(),
            'last_login_at' => fake()->optional(0.7)->dateTimeBetween('-30 days', 'now'),
            'location' => fake()->optional(0.6)->city(),
            'coordinate' => fake()->optional(0.6)->latitude() . ',' . fake()->optional(0.6)->longitude(),
            'lat' => fake()->optional(0.6)->latitude(),
            'long' => fake()->optional(0.6)->longitude(),
            'nik' => fake()->numerify('################'),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'gender' => fake()->boolean(),
            'date_of_birth' => fake()->date('Y-m-d', '2000-01-01'),
            'reason_to_join' => fake()->optional(0.8)->sentence(),
            'registered_at' => fake()->optional(0.9)->dateTimeBetween('-60 days', 'now'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified' => false,
        ]);
    }

    /**
     * Create an admin user.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => UserTypeEnum::ADMIN,
            'status' => UserStatusEnum::ACTIVE,
            'email_verified' => true,
        ]);
    }

    /**
     * Create an officer user.
     */
    public function officer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => UserTypeEnum::OFFICER,
            'status' => UserStatusEnum::ACTIVE,
            'email_verified' => true,
        ]);
    }

    /**
     * Create a volunteer user.
     */
    public function volunteer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => UserTypeEnum::VOLUNTEER,
            'status' => UserStatusEnum::REGISTERED,
        ]);
    }

    /**
     * Create an active volunteer user.
     */
    public function activeVolunteer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => UserTypeEnum::VOLUNTEER,
            'status' => UserStatusEnum::ACTIVE,
            'email_verified' => true,
        ]);
    }

    /**
     * Create an inactive user.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatusEnum::INACTIVE,
        ]);
    }

    /**
     * Create a user with location data.
     */
    public function withLocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'location' => fake()->city() . ', ' . fake()->country(),
            'coordinate' => fake()->latitude() . ',' . fake()->longitude(),
            'lat' => fake()->latitude(),
            'long' => fake()->longitude(),
        ]);
    }
}
