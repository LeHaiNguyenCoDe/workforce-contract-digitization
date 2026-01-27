<?php

namespace App\Console\Commands;

use App\Models\Language;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateTranslations extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'translations:generate 
                            {locale? : Locale code to generate (e.g., ja, ko, zh). If omitted, generates for all active languages}
                            {--template=en : Template locale to copy from}
                            {--force : Overwrite existing translation files}';

    /**
     * The console command description.
     */
    protected $description = 'Generate translation files for new languages from a template';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $templateLocale = $this->option('template');
        $force = $this->option('force');
        $targetLocale = $this->argument('locale');

        // Get template files
        $templatePath = lang_path($templateLocale);
        if (!File::isDirectory($templatePath)) {
            $this->error("Template locale '{$templateLocale}' not found in lang directory.");
            return Command::FAILURE;
        }

        // Determine which locales to generate
        $localesToGenerate = [];
        if ($targetLocale) {
            $localesToGenerate[] = $targetLocale;
        } else {
            // Get all active languages from database
            $languages = Language::where('is_active', true)->pluck('code')->toArray();
            $localesToGenerate = array_diff($languages, [$templateLocale]);
        }

        if (empty($localesToGenerate)) {
            $this->info('No locales to generate.');
            return Command::SUCCESS;
        }

        $this->info("Generating translations from '{$templateLocale}' template...");
        $this->newLine();

        $generated = 0;
        $skipped = 0;

        foreach ($localesToGenerate as $locale) {
            $result = $this->generateForLocale($locale, $templatePath, $force);
            $generated += $result['generated'];
            $skipped += $result['skipped'];
        }

        $this->newLine();
        $this->info("Done! Generated: {$generated} files, Skipped: {$skipped} files");

        return Command::SUCCESS;
    }

    /**
     * Generate translation files for a specific locale
     */
    private function generateForLocale(string $locale, string $templatePath, bool $force): array
    {
        $targetPath = lang_path($locale);
        $generated = 0;
        $skipped = 0;

        // Create directory if not exists
        if (!File::isDirectory($targetPath)) {
            File::makeDirectory($targetPath, 0755, true);
            $this->line("  <info>Created directory:</info> {$locale}/");
        }

        // Get all translation files from template
        $files = File::files($templatePath);

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $targetFile = $targetPath . '/' . $filename;

            if (File::exists($targetFile) && !$force) {
                $this->line("  <comment>Skipped:</comment> {$locale}/{$filename} (already exists)");
                $skipped++;
                continue;
            }

            // Copy and add TODO comments
            $content = $this->generateTranslationFile($file->getPathname(), $locale);
            File::put($targetFile, $content);

            $this->line("  <info>Generated:</info> {$locale}/{$filename}");
            $generated++;
        }

        return ['generated' => $generated, 'skipped' => $skipped];
    }

    /**
     * Generate translation file content with TODO markers
     */
    private function generateTranslationFile(string $sourcePath, string $targetLocale): string
    {
        $source = file_get_contents($sourcePath);
        
        // Add header comment
        $header = "<?php\n\n/**\n * Translation file for locale: {$targetLocale}\n * Generated from template. Please translate all values.\n * TODO: Translate all strings to {$targetLocale}\n */\n\nreturn ";

        // Get array content
        $translations = require $sourcePath;
        
        // Add TODO to each value
        $translationsWithTodo = $this->addTodoMarkers($translations, $targetLocale);
        
        $arrayContent = $this->arrayToString($translationsWithTodo);
        
        return $header . $arrayContent . ";\n";
    }

    /**
     * Add TODO markers to translation values
     */
    private function addTodoMarkers(array $translations, string $locale): array
    {
        $result = [];
        foreach ($translations as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->addTodoMarkers($value, $locale);
            } else {
                // Keep original value as placeholder with TODO marker in comments
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * Convert array to formatted PHP string
     */
    private function arrayToString(array $array, int $indent = 1): string
    {
        $spaces = str_repeat('    ', $indent);
        $items = [];

        foreach ($array as $key => $value) {
            $keyStr = is_string($key) ? "'{$key}'" : $key;
            
            if (is_array($value)) {
                $valueStr = $this->arrayToString($value, $indent + 1);
                $items[] = "{$spaces}{$keyStr} => {$valueStr}";
            } else {
                // Escape single quotes in value
                $escapedValue = str_replace("'", "\\'", $value ?? '');
                $items[] = "{$spaces}{$keyStr} => '{$escapedValue}'";
            }
        }

        $openBracket = "[\n";
        $closeBracket = "\n" . str_repeat('    ', $indent - 1) . "]";
        
        return $openBracket . implode(",\n", $items) . "," . $closeBracket;
    }
}
