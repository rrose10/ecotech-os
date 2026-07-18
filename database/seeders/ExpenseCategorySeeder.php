<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Office Rent',
            'Salary',
            'Internet',
            'Electricity',
            'Water Bill',
            'Office Supplies',
            'Marketing',
            'Software Subscription',
            'Travel',
            'Fuel',
            'Maintenance',
            'Entertainment',
            'Miscellaneous',
        ];

        foreach ($categories as $category) {
            ExpenseCategory::create([
                'name' => $category,
                'status' => true,
            ]);
        }
    }
}