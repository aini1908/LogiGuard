<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SentimentLexiconSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positiveWords = [
            'growth', 'increase', 'profit', 'stable', 'improve', 'boom', 'recovery', 'surplus', 'gain', 'expansion', 'strengthen', 'efficient', 'secured', 'success', 'advance'
        ];
        $negativeWords = [
            'war', 'crisis', 'inflation', 'delay', 'disaster', 'decrease', 'loss', 'conflict', 'strike', 'shortage', 'congestion', 'storm', 'blockade', 'sanction', 'decline'
        ];
        foreach ($positiveWords as $word) {
            DB::table('positive_words')->insertOrIgnore([
                'word' => $word,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
            foreach ($negativeWords as $word) {
                DB::table('negative_words')->insertOrIgnore([
                    'word' => $word,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
     }

