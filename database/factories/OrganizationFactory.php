<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'name'   => $name,
            'slug'   => \Illuminate\Support\Str::slug($name) . '-' . $this->faker->unique()->numerify('###'),
            'status' => 'active',
        ];
    }
}
