<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SplitTranslations extends Command
{
    protected $signature = 'translations:split {--locale=* : Specific locales to split (e.g., --locale=en --locale=ja)}';
    protected $description = 'Split api.php translation file into separate files by category';

    /**
     * Category mappings: key prefixes to file names
     */
    private array $categoryMappings = [
        'common' => ['success', 'error', 'not_found', 'validation_error', 'validation_failed', 
                     'unauthorized', 'forbidden', 'server_error', 'created', 'updated', 'deleted'],
        'auth' => ['login', 'logout', 'invalid_credentials'],
        'user' => ['user_'],
        'product' => ['product_', 'products_', 'image_', 'variant_'],
        'order' => ['order_', 'shipper_', 'tracking_', 'cart_empty'],
        'cart' => ['item_', 'cart_'],
        'category' => ['category_'],
        'warehouse' => ['warehouse_', 'inbound_', 'batch_', 'quality_', 'stock_', 'receipt_', 
                        'transfer_', 'stocktake_', 'pr_', 'setting_', 'auto_requests_'],
        'finance' => ['expense_', 'payment_', 'ar_', 'ap_', 'reconciliation_'],
        'store' => ['article_', 'promotion_', 'wishlist', 'review_', 'supplier_', 
                    'rma_', 'tier_', 'loyalty_', 'language_', 'unsupported_locale',
                    'added_to_wishlist', 'removed_from_wishlist'],
    ];

    public function handle(): int
    {
        $locales = $this->option('locale');
        
        if (empty($locales)) {
            // Get all locales with api.php
            $locales = [];
            foreach (File::directories(lang_path()) as $dir) {
                $locale = basename($dir);
                if (File::exists(lang_path("{$locale}/api.php"))) {
                    $locales[] = $locale;
                }
            }
        }

        if (empty($locales)) {
            $this->info('No api.php files found to split.');
            return Command::SUCCESS;
        }

        $this->info("Splitting translations for: " . implode(', ', $locales));
        $this->newLine();

        foreach ($locales as $locale) {
            $this->splitLocale($locale);
        }

        $this->newLine();
        $this->info("Done! Run 'php artisan translations:import' to sync to database.");

        return Command::SUCCESS;
    }

    private function splitLocale(string $locale): void
    {
        $apiFile = lang_path("{$locale}/api.php");
        
        if (!File::exists($apiFile)) {
            $this->warn("  Skipped {$locale}: api.php not found");
            return;
        }

        $this->line("Processing: {$locale}");
        
        $translations = require $apiFile;
        $categorized = $this->categorizeTranslations($translations);
        
        foreach ($categorized as $category => $items) {
            if (empty($items)) continue;
            
            $filePath = lang_path("{$locale}/{$category}.php");
            $content = $this->generateFileContent($items, $category);
            File::put($filePath, $content);
            $this->line("   Created {$category}.php (" . count($items) . " keys)");
        }
        
        // Delete original api.php
        File::delete($apiFile);
        $this->line("   Deleted api.php");
    }

    private function categorizeTranslations(array $translations): array
    {
        $result = array_fill_keys(array_keys($this->categoryMappings), []);
        $uncategorized = [];

        foreach ($translations as $key => $value) {
            $found = false;
            
            foreach ($this->categoryMappings as $category => $patterns) {
                foreach ($patterns as $pattern) {
                    if ($key === $pattern || str_starts_with($key, $pattern)) {
                        $result[$category][$key] = $value;
                        $found = true;
                        break 2;
                    }
                }
            }
            
            if (!$found) {
                $uncategorized[$key] = $value;
            }
        }
        
        // Add uncategorized to 'store' as fallback
        if (!empty($uncategorized)) {
            $result['store'] = array_merge($result['store'], $uncategorized);
        }

        return $result;
    }

    private function generateFileContent(array $translations, string $category): string
    {
        $header = "<?php\n\n// " . ucfirst($category) . " messages\nreturn [\n";
        $items = [];
        
        foreach ($translations as $key => $value) {
            $escapedValue = str_replace("'", "\\'", $value);
            $items[] = "    '{$key}' => '{$escapedValue}'";
        }
        
        return $header . implode(",\n", $items) . ",\n];\n";
    }
}
