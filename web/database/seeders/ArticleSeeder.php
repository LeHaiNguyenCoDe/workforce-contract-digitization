<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $articles = [
            [
                'title' => 'Welcome to Our Store',
                'content' => 'Welcome to our online store! We are excited to offer you a wide range of high-quality products at competitive prices. Our mission is to provide excellent customer service and ensure your satisfaction with every purchase.',
                'published_at' => now()->subDays(30),
            ],
            [
                'title' => 'How to Choose the Right Product',
                'content' => 'Choosing the right product can be challenging. In this article, we will guide you through the process of selecting products that meet your needs and budget. Consider factors such as quality, price, reviews, and warranty when making your decision.',
                'published_at' => now()->subDays(25),
            ],
            [
                'title' => 'Tips for Online Shopping',
                'content' => 'Online shopping has become increasingly popular. Here are some tips to make your online shopping experience better: Always read product reviews, check return policies, compare prices, and ensure secure payment methods.',
                'published_at' => now()->subDays(20),
            ],
            [
                'title' => 'New Product Launch',
                'content' => 'We are thrilled to announce the launch of our new product line! These products feature the latest technology and innovative designs. Stay tuned for special launch promotions and discounts.',
                'published_at' => now()->subDays(15),
            ],
            [
                'title' => 'Customer Service Excellence',
                'content' => 'At our store, customer satisfaction is our top priority. Our dedicated customer service team is available 24/7 to assist you with any questions or concerns. We strive to provide the best shopping experience possible.',
                'published_at' => now()->subDays(10),
            ],
            [
                'title' => 'Seasonal Sale Announcement',
                'content' => 'Don\'t miss our seasonal sale! Get up to 50% off on selected items. This is the perfect time to stock up on your favorite products or try something new. Sale ends soon, so shop now!',
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Product Care and Maintenance',
                'content' => 'Proper care and maintenance can extend the life of your products significantly. Follow our care instructions to keep your items in perfect condition. Regular maintenance ensures optimal performance and longevity.',
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Shipping and Delivery Information',
                'content' => 'We offer fast and reliable shipping to locations worldwide. Standard shipping takes 3-5 business days, while express shipping is available for urgent orders. Track your order in real-time through our website.',
                'published_at' => now(),
            ],
        ];

        foreach ($articles as $article) {
            Article::create([
                'title' => $article['title'],
                'slug' => Str::slug($article['title']) . '-' . uniqid(),
                'thumbnail' => "https://picsum.photos/800/400?random=" . rand(1, 1000),
                'content' => $article['content'],
                'published_at' => $article['published_at'],
            ]);
        }
    }
}


