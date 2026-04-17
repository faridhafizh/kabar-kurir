<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Article;
use Carbon\Carbon;

class FetchNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch latest news related to package delivery and logistics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $apiKey = env('NEWS_API_KEY');
        
        if (empty($apiKey)) {
            $this->error('NEWS_API_KEY is not set in .env file.');
            return;
        }

        $this->info('Fetching news from NewsAPI...');

        // Query related to logistics, couriers, or shipping in Indonesia
        $query = urlencode('kurir OR ekspedisi OR paket OR pengiriman');
        
        $response = Http::withOptions(['verify' => false])->get("https://newsapi.org/v2/everything?q={$query}&language=id&sortBy=publishedAt&apiKey={$apiKey}");

        if ($response->failed()) {
            $this->error('Failed to fetch news from API: ' . $response->body());
            return;
        }

        $articles = $response->json('articles') ?? [];
        $count = 0;

        foreach ($articles as $item) {
            // Skip invalid or removed articles
            if (empty($item['title']) || $item['title'] === '[Removed]') continue;

            $article = Article::updateOrCreate(
                ['url' => $item['url']],
                [
                    'source' => $item['source']['name'] ?? 'Unknown Source',
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'content' => $item['content'],
                    'published_at' => Carbon::parse($item['publishedAt'])->setTimezone('Asia/Jakarta'),
                ]
            );

            if ($article->wasRecentlyCreated) {
                $count++;
            }
        }

        $this->info("Successfully fetched and added {$count} new articles.");
    }
}
