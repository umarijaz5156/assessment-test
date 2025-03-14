<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => Order::factory(),
            'subtotal' => $subtotal = round(rand(100, 999) / 3, 2),
            'commission_owed' => round($subtotal * 0.1, 2),
        ];
    }
}
