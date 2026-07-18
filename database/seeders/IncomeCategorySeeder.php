<?php

namespace Database\Seeders;

use App\Models\IncomeCategory;
use Illuminate\Database\Seeder;

class IncomeCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Product Sales',
            'Service Income',
            'Client Payment',
            'Subscription',
            'Investment',
            'Commission',
            'Refund',
            'Other Income',
        ];

        foreach ($categories as $category) {
            IncomeCategory::create([
                'name' => $category,
                'status' => true,
            ]);
        }
    }
}