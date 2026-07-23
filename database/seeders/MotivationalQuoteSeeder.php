<?php

namespace Database\Seeders;

use App\Models\MotivationalQuote;
use Illuminate\Database\Seeder;

class MotivationalQuoteSeeder extends Seeder
{
    public function run(): void
    {
        $quotes = [

            [
                'quote' => 'Success is the sum of small efforts repeated day after day.',
                'author' => 'Robert Collier',
            ],

            [
                'quote' => 'The secret of getting ahead is getting started.',
                'author' => 'Mark Twain',
            ],

            [
                'quote' => 'Dream big. Start small. Act now.',
                'author' => 'Robin Sharma',
            ],

            [
                'quote' => 'Quality means doing it right when no one is looking.',
                'author' => 'Henry Ford',
            ],

            [
                'quote' => 'Do not wait for opportunity. Create it.',
                'author' => 'Unknown',
            ],

            [
                'quote' => 'Consistency beats intensity.',
                'author' => 'Unknown',
            ],

            [
                'quote' => 'Great things never come from comfort zones.',
                'author' => 'Unknown',
            ],

            [
                'quote' => 'Every accomplishment starts with the decision to try.',
                'author' => 'John F. Kennedy',
            ],

            [
                'quote' => 'Work hard in silence. Let success make the noise.',
                'author' => 'Frank Ocean',
            ],

            [
                'quote' => 'Success is not for the lazy.',
                'author' => 'Unknown',
            ],

            [
                'quote' => 'Small progress is still progress.',
                'author' => 'Unknown',
            ],

            [
                'quote' => 'Believe you can and you are halfway there.',
                'author' => 'Theodore Roosevelt',
            ],

            [
                'quote' => 'Discipline is choosing what you want most over what you want now.',
                'author' => 'Abraham Lincoln (attributed)',
            ],

            [
                'quote' => 'The future depends on what you do today.',
                'author' => 'Mahatma Gandhi',
            ],

            [
                'quote' => 'Stay positive. Work hard. Make it happen.',
                'author' => 'Unknown',
            ],

            [
                'quote' => 'Teamwork makes the dream work.',
                'author' => 'John C. Maxwell',
            ],

            [
                'quote' => 'Your only limit is your willingness to keep going.',
                'author' => 'Unknown',
            ],

            [
                'quote' => 'Do something today your future self will thank you for.',
                'author' => 'Unknown',
            ],

            [
                'quote' => 'Success begins with self-discipline.',
                'author' => 'Unknown',
            ],

            [
                'quote' => 'Never stop learning because life never stops teaching.',
                'author' => 'Unknown',
            ],

        ];

        foreach ($quotes as $quote) {

            MotivationalQuote::create([
                'quote' => $quote['quote'],
                'author' => $quote['author'],
                'is_active' => true,
            ]);

        }
    }
}