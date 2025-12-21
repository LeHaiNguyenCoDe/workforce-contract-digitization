<?php

namespace App\Console\Commands;

use App\Services\TranslationService;
use Illuminate\Console\Command;

class ImportTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:import 
                            {--locale= : Import specific locale only}
                            {--sync : Sync both directions (import and export)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import translations from language files to database';

    /**
     * Execute the console command.
     */
    public function handle(TranslationService $translationService): int
    {
        $this->info('Starting translation import...');

        if ($this->option('sync')) {
            $this->info('Syncing translations (import + export)...');
            $result = $translationService->sync();
            
            $this->info("✅ Imported: {$result['imported']} translations");
            $this->info("✅ Exported: {$result['exported']} translations");
        } else {
            $locale = $this->option('locale');
            $count = $translationService->importFromFiles($locale);
            
            $this->info("✅ Imported {$count} translations");
            
            if ($locale) {
                $this->info("   Locale: {$locale}");
            }
        }

        $this->info('Translation import completed!');
        $this->info('Cache has been cleared.');

        return Command::SUCCESS;
    }
}

