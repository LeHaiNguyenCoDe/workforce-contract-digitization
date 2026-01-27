<?php

namespace App\Console\Commands;

use App\Models\Language;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Stichoza\GoogleTranslate\GoogleTranslate;

class AutoTranslate extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'translations:auto 
                            {locale? : Target locale to translate to. If omitted, translates to all active languages}
                            {--from=en : Source locale to translate from}
                            {--file=api : Specific file to translate (without .php)}
                            {--force : Overwrite existing translations}
                            {--dry-run : Show what would be translated without saving}';

    /**
     * The console command description.
     */
    protected $description = 'Auto-translate language files using Google Translate';

    private GoogleTranslate $translator;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sourceLocale = $this->option('from');
        $targetLocale = $this->argument('locale');
        $specificFile = $this->option('file');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        // Initialize translator
        $this->translator = new GoogleTranslate();
        $this->translator->setSource($sourceLocale);

        // Get source files
        $sourcePath = lang_path($sourceLocale);
        if (!File::isDirectory($sourcePath)) {
            $this->error("Source locale '{$sourceLocale}' not found.");
            return Command::FAILURE;
        }

        // Determine target locales
        $targetLocales = [];
        if ($targetLocale) {
            $targetLocales[] = $targetLocale;
        } else {
            $languages = Language::where('is_active', true)
                ->where('code', '!=', $sourceLocale)
                ->pluck('code')
                ->toArray();
            $targetLocales = $languages;
        }

        if (empty($targetLocales)) {
            $this->info('No target locales to translate.');
            return Command::SUCCESS;
        }

        $this->info("🌐 Auto-translating from '{$sourceLocale}'...");
        $this->newLine();

        // Get files to translate
        $files = File::files($sourcePath);
        if ($specificFile) {
            $files = array_filter($files, fn($f) => $f->getFilenameWithoutExtension() === $specificFile);
        }

        $totalTranslated = 0;

        foreach ($targetLocales as $locale) {
            $this->info("Translating to: {$locale}");
            $this->translator->setTarget($locale);

            foreach ($files as $file) {
                $filename = $file->getFilenameWithoutExtension();
                $result = $this->translateFile($file, $locale, $force, $dryRun);
                $totalTranslated += $result;
                
                if ($result > 0) {
                    $action = $dryRun ? 'Would translate' : 'Translated';
                    $this->line("  ✓ {$filename}.php: {$result} strings {$action}");
                } else {
                    $this->line("  - {$filename}.php: skipped (already exists)");
                }
            }
            $this->newLine();
        }

        if ($dryRun) {
            $this->warn("Dry run complete. {$totalTranslated} strings would be translated.");
        } else {
            $this->info("Done! Translated {$totalTranslated} strings.");
            $this->info("Run 'php artisan translations:import' to sync to database.");
        }

        return Command::SUCCESS;
    }

    /**
     * Translate a single file
     */
    private function translateFile($sourceFile, string $targetLocale, bool $force, bool $dryRun): int
    {
        $filename = $sourceFile->getFilename();
        $targetPath = lang_path($targetLocale);
        $targetFile = $targetPath . '/' . $filename;

        // Create directory if not exists
        if (!File::isDirectory($targetPath)) {
            if (!$dryRun) {
                File::makeDirectory($targetPath, 0755, true);
            }
        }

        // Load source translations
        $sourceTranslations = require $sourceFile->getPathname();

        // Load existing translations or empty array
        $existingTranslations = [];
        if (File::exists($targetFile)) {
            if (!$force) {
                $existingTranslations = require $targetFile;
            }
        }

        // Translate
        $translated = $this->translateArray($sourceTranslations, $existingTranslations, $force);

        if ($translated['count'] === 0) {
            return 0;
        }

        if (!$dryRun) {
            // Save translated file
            $content = $this->generatePhpFile($translated['data'], $targetLocale);
            File::put($targetFile, $content);
        }

        return $translated['count'];
    }

    /**
     * Recursively translate array
     */
    private function translateArray(array $source, array $existing, bool $force): array
    {
        $result = [];
        $count = 0;

        foreach ($source as $key => $value) {
            if (is_array($value)) {
                $existingNested = $existing[$key] ?? [];
                $nested = $this->translateArray($value, $existingNested, $force);
                $result[$key] = $nested['data'];
                $count += $nested['count'];
            } else {
                // Check if already translated
                if (!$force && isset($existing[$key]) && $existing[$key] !== $value) {
                    $result[$key] = $existing[$key];
                } else {
                    // Translate the string
                    try {
                        $translated = $this->translator->translate($value);
                        $result[$key] = $translated;
                        $count++;
                        
                        // Add small delay to avoid rate limiting
                        usleep(100000); // 100ms
                    } catch (\Exception $e) {
                        $this->warn("  ⚠ Failed to translate '{$key}': " . $e->getMessage());
                        $result[$key] = $value; // Keep original
                    }
                }
            }
        }

        return ['data' => $result, 'count' => $count];
    }

    /**
     * Generate PHP file content
     */
    private function generatePhpFile(array $translations, string $locale): string
    {
        $header = "<?php\n\n/**\n * Auto-translated to: {$locale}\n * Generated: " . now()->toDateTimeString() . "\n */\n\nreturn ";
        $content = $this->arrayToString($translations);
        return $header . $content . ";\n";
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
                $escapedValue = str_replace("'", "\\'", $value ?? '');
                $items[] = "{$spaces}{$keyStr} => '{$escapedValue}'";
            }
        }

        $openBracket = "[\n";
        $closeBracket = "\n" . str_repeat('    ', $indent - 1) . "]";
        
        return $openBracket . implode(",\n", $items) . "," . $closeBracket;
    }
}
