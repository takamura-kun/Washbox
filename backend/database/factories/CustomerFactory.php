<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        static $branchIds = null;
        if ($branchIds === null) {
            $branchIds = Branch::pluck('id')->toArray();
            if (empty($branchIds)) $branchIds = [1];
        }
        $branchId = $this->faker->randomElement($branchIds);

        return [
            'name'                => $this->faker->name(),
            'email'               => $this->faker->unique()->safeEmail(),
            'phone'               => '09' . $this->faker->numerify('#########'),
            'password'            => 'Customer@123',
            'address'             => $this->faker->address(),
            'branch_id'           => $branchId,
            'preferred_branch_id' => $branchId,
            'registration_type'   => $this->faker->randomElement(['self_registered', 'walk_in']),
            'is_active'           => true,
        ];
    }
}
