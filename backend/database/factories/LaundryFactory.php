<?php

namespace Database\Factories;

use App\Models\Laundry;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LaundryFactory extends Factory
{
    protected $model = Laundry::class;

    public function definition(): array
    {
        static $customerIds = null;
        static $branchIds   = null;
        static $serviceIds  = null;
        static $userIds     = null;

        if ($customerIds === null) {
            $customerIds = Customer::pluck('id')->toArray();
            if (empty($customerIds)) $customerIds = [1];
        }
        if ($branchIds === null) {
            $branchIds = Branch::pluck('id')->toArray();
            if (empty($branchIds)) $branchIds = [1];
        }
        if ($serviceIds === null) {
            $serviceIds = Service::pluck('id')->toArray();
        }
        if ($userIds === null) {
            $userIds = User::pluck('id')->toArray();
            if (empty($userIds)) $userIds = [1];
        }

        $status   = $this->faker->randomElement(['received', 'processing', 'ready', 'paid', 'completed', 'cancelled']);
        $loads    = $this->faker->numberBetween(1, 5);
        $price    = $this->faker->randomFloat(2, 80, 200);
        $subtotal = $price * $loads;

        return [
            'tracking_number' => 'WB-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
            'customer_id'     => $this->faker->randomElement($customerIds),
            'branch_id'       => $this->faker->randomElement($branchIds),
            'service_id'      => empty($serviceIds) ? null : $this->faker->randomElement($serviceIds),
            'created_by'      => $this->faker->randomElement($userIds),
            'weight'          => $this->faker->randomFloat(2, 1, 10),
            'number_of_loads' => $loads,
            'subtotal'        => $subtotal,
            'addons_total'    => 0,
            'discount_amount' => 0,
            'total_amount'    => $subtotal,
            'status'          => $status,
            'payment_status'  => in_array($status, ['paid', 'completed']) ? 'paid' : 'pending',
            'payment_method'  => $this->faker->randomElement(['cash', 'gcash']),
            'received_at'     => $this->faker->dateTimeBetween('-6 months', 'now'),
            'notes'           => $this->faker->optional()->sentence(),
        ];
    }
}
