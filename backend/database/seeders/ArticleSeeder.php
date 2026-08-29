<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run()
    {
        $author = User::where('role', 'editor')->first();
        if (!$author) {
            $author = new User();
            $author->name = 'Editorial Team';
            $author->email = 'editor' . time() . '@latestdeal.in';
            $author->password = bcrypt('editor123');
            $author->role = 'editor';
            $author->save();
        }

        $templates = [
            [
                'title' => 'The Complete Guide to Buying Laptops in 2024',
                'excerpt' => 'A comprehensive research framework for determining which laptop fits your needs and budget before buying.',
                'content' => '<h2>Introduction</h2><p>Template outlining CPU, RAM, and display requirements.</p><h2>What to Look For</h2><p>[Research needed]</p><h2>Best Time to Buy</h2><p>[Historical price analysis goes here]</p>',
            ],
            [
                'title' => 'How to Spot Fake Discounts on Amazon',
                'excerpt' => 'Our methodology for identifying artificial price inflations and verifying true historical lows.',
                'content' => '<h2>The Artificial Markup</h2><p>[Drafting section on MSRP vs actual selling price]</p>',
            ],
            [
                'title' => 'Best Wireless Noise Cancelling Headphones',
                'excerpt' => 'A comparison of Sony, Bose, and Apple flagship headphones based on value for money.',
                'content' => '<h2>Sony WH-1000XM5 vs AirPods Max</h2><p>[Draft analysis]</p>',
            ],
            [
                'title' => 'When is the Best Time to Buy a TV?',
                'excerpt' => 'Analyzing seasonal price drops for major television brands.',
                'content' => '<h2>Super Bowl vs Black Friday</h2><p>[Researching historical data]</p>',
            ],
            [
                'title' => 'The Ultimate Smart Home Starter Kit',
                'excerpt' => 'Which ecosystem to choose and what devices actually provide value.',
                'content' => '<h2>Alexa vs Google Home</h2><p>[Needs testing data]</p>',
            ],
            [
                'title' => 'Gaming Consoles: PlayStation 5 vs Xbox Series X',
                'excerpt' => 'Value comparison including subscription services and exclusive titles.',
                'content' => '<h2>Total Cost of Ownership</h2><p>[Drafting spreadsheet analysis]</p>',
            ],
            [
                'title' => 'Best Budget Smartphones Under $300',
                'excerpt' => 'You don\'t need to spend $1000 for a great phone. Here are our top budget picks.',
                'content' => '<h2>Pixel 6a vs Samsung A54</h2><p>[Pending camera comparisons]</p>',
            ],
            [
                'title' => 'Mechanical Keyboards: A Beginner\'s Guide',
                'excerpt' => 'Understanding switches, layouts, and finding the best value board.',
                'content' => '<h2>Switch Types Explained</h2><p>[Drafting graphics]</p>',
            ],
            [
                'title' => 'Are Extended Warranties Worth It?',
                'excerpt' => 'A data-driven look at failure rates and warranty costs.',
                'content' => '<h2>The Math Behind Warranties</h2><p>[Actuarial data needed]</p>',
            ],
            [
                'title' => 'How to Build a Budget PC in 2024',
                'excerpt' => 'Maximizing frames per second per dollar with current generation parts.',
                'content' => '<h2>GPU Value Rankings</h2><p>[Waiting for next-gen release dates]</p>',
            ]
        ];

        foreach ($templates as $index => $template) {
            Article::create([
                'title' => $template['title'],
                'slug' => Str::slug($template['title']),
                'summary' => $template['excerpt'],
                'content' => $template['content'],
                'status' => 'draft', // Explicitly Draft
                'author_id' => $author->id,
                'published_at' => null,
            ]);
        }
    }
}
