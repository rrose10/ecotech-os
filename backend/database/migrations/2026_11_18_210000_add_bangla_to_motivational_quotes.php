<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Bangla for the 20 quotes that shipped with the first install. */
    private array $original = [
        'Success is the sum of small efforts repeated day after day.' => 'সাফল্য হলো প্রতিদিন বারবার করা ছোট ছোট চেষ্টার যোগফল।',
        'The secret of getting ahead is getting started.' => 'এগিয়ে যাওয়ার গোপন কথা হলো শুরু করা।',
        'Dream big. Start small. Act now.' => 'বড় স্বপ্ন দেখো। ছোট করে শুরু করো। এখনই কাজে নামো।',
        'Quality means doing it right when no one is looking.' => 'মান মানে কেউ না দেখলেও ঠিকভাবে কাজ করা।',
        'Do not wait for opportunity. Create it.' => 'সুযোগের অপেক্ষা কোরো না। সুযোগ তৈরি করো।',
        'Consistency beats intensity.' => 'ধারাবাহিকতা তীব্রতার চেয়ে শক্তিশালী।',
        'Great things never come from comfort zones.' => 'বড় কিছু কখনো আরামের গণ্ডি থেকে আসে না।',
        'Every accomplishment starts with the decision to try.' => 'প্রতিটি অর্জন শুরু হয় চেষ্টা করার সিদ্ধান্ত থেকে।',
        'Work hard in silence. Let success make the noise.' => 'নীরবে কঠোর পরিশ্রম করো। সাফল্যই শব্দ করুক।',
        'Success is not for the lazy.' => 'সাফল্য অলসদের জন্য নয়।',
        'Small progress is still progress.' => 'ছোট অগ্রগতিও অগ্রগতি।',
        'Believe you can and you are halfway there.' => 'বিশ্বাস করো তুমি পারবে, তাহলে অর্ধেক পথ পেরিয়ে গেছো।',
        'Discipline is choosing what you want most over what you want now.' => 'শৃঙ্খলা মানে এখনকার চাওয়ার চেয়ে সবচেয়ে বড় চাওয়াটাকে বেছে নেওয়া।',
        'The future depends on what you do today.' => 'ভবিষ্যৎ নির্ভর করে আজ তুমি কী করছো তার ওপর।',
        'Stay positive. Work hard. Make it happen.' => 'ইতিবাচক থাকো। কঠোর পরিশ্রম করো। করে দেখাও।',
        'Teamwork makes the dream work.' => 'টিমওয়ার্কই স্বপ্নকে বাস্তব করে।',
        'Your only limit is your willingness to keep going.' => 'তোমার একমাত্র সীমা হলো এগিয়ে চলার ইচ্ছা।',
        'Do something today your future self will thank you for.' => 'আজ এমন কিছু করো যার জন্য ভবিষ্যৎ তুমি ধন্যবাদ দেবে।',
        'Success begins with self-discipline.' => 'সাফল্য শুরু হয় আত্মশৃঙ্খলা দিয়ে।',
        'Never stop learning because life never stops teaching.' => 'শেখা থামিও না, কারণ জীবন কখনো শেখানো থামায় না।',
    ];

    public function up(): void
    {
        Schema::table('motivational_quotes', function (Blueprint $table) {
            $table->text('quote_bn')->nullable()->after('quote');
        });

        foreach ($this->original as $en => $bn) {
            DB::table('motivational_quotes')->where('quote', $en)->update(['quote_bn' => $bn]);
        }

        $en = array_values(array_filter(array_map('trim', file(database_path('data/motivation_quotes.txt')))));
        $bn = array_values(array_filter(array_map('trim', file(database_path('data/motivation_quotes_bn.txt')))));

        foreach ($en as $i => $text) {
            if (isset($bn[$i])) {
                DB::table('motivational_quotes')->where('quote', $text)->update(['quote_bn' => $bn[$i]]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('motivational_quotes', function (Blueprint $table) {
            $table->dropColumn('quote_bn');
        });
    }
};
