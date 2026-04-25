<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
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
    protected $description = 'Fetch latest news related to package delivery and logistics using Playwright';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching news from Google News using Playwright...');

        $process = new Process(['node', base_path('fetch-news.js')]);
        $process->setTimeout(120);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            $this->error('Failed to execute node script: ' . $exception->getMessage());
            return;
        }

        $output = $process->getOutput();
        $articles = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Failed to parse JSON output: ' . json_last_error_msg());
            return;
        }

        if (!is_array($articles)) {
            $this->error('Invalid format: output is not an array.');
            return;
        }

        $count = 0;

        foreach ($articles as $item) {
            if (empty($item['title'])) continue;

            $publishedAt = null;
            if (!empty($item['published_at'])) {
                try {
                    $publishedAt = Carbon::parse($item['published_at'])->setTimezone('Asia/Jakarta');
                } catch (\Exception $e) {
                    // Ignore parsing errors for individual dates
                }
            }

            $article = Article::updateOrCreate(
                ['url' => $item['url']],
                [
                    'source' => $item['source'] ?? 'Unknown Source',
                    'title' => $item['title'],
                    'description' => null, // No description available from this scraper
                    'content' => null,     // No content available from this scraper
                    'published_at' => $publishedAt,
                ]
            );

            if ($article->wasRecentlyCreated) {
                $count++;
            }
        }

        $this->info("Successfully fetched and added {$count} new articles.");
    }
}
