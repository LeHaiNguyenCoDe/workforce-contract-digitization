<?php

namespace App\Console\Commands;

use App\Services\TranslationService;
use Illuminate\Console\Command;

class ExportTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:export 
                            {--locale= : Export specific locale only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export translations from database to language files';

    /**
     * Execute the console command.
     */
    public function handle(TranslationService $translationService): int
    {
        $this->info('Starting translation export...');

        $locale = $this->option('locale');
        $count = $translationService->exportToFiles($locale);

        $this->info("✅ Exported {$count} translations");
        
        if ($locale) {
            $this->info("   Locale: {$locale}");
        }

        $this->info('Translation export completed!');

        return Command::SUCCESS;
    }
}

