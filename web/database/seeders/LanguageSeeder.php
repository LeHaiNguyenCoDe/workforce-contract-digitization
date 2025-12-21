<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            ['code' => 'vi', 'name' => 'Tiếng Việt', 'native_name' => 'Tiếng Việt', 'flag' => '🇻🇳', 'is_default' => true, 'sort_order' => 1],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'flag' => '🇬🇧', 'is_default' => false, 'sort_order' => 2],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'flag' => '🇫🇷', 'is_default' => false, 'sort_order' => 3],
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'flag' => '🇩🇪', 'is_default' => false, 'sort_order' => 4],
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español', 'flag' => '🇪🇸', 'is_default' => false, 'sort_order' => 5],
            ['code' => 'it', 'name' => 'Italian', 'native_name' => 'Italiano', 'flag' => '🇮🇹', 'is_default' => false, 'sort_order' => 6],
            ['code' => 'pt', 'name' => 'Portuguese', 'native_name' => 'Português', 'flag' => '🇵🇹', 'is_default' => false, 'sort_order' => 7],
            ['code' => 'ru', 'name' => 'Russian', 'native_name' => 'Русский', 'flag' => '🇷🇺', 'is_default' => false, 'sort_order' => 8],
            ['code' => 'ja', 'name' => 'Japanese', 'native_name' => '日本語', 'flag' => '🇯🇵', 'is_default' => false, 'sort_order' => 9],
            ['code' => 'ko', 'name' => 'Korean', 'native_name' => '한국어', 'flag' => '🇰🇷', 'is_default' => false, 'sort_order' => 10],
            ['code' => 'zh', 'name' => 'Chinese', 'native_name' => '中文', 'flag' => '🇨🇳', 'is_default' => false, 'sort_order' => 11],
            ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'flag' => '🇸🇦', 'is_default' => false, 'sort_order' => 12],
            ['code' => 'th', 'name' => 'Thai', 'native_name' => 'ไทย', 'flag' => '🇹🇭', 'is_default' => false, 'sort_order' => 13],
            ['code' => 'id', 'name' => 'Indonesian', 'native_name' => 'Bahasa Indonesia', 'flag' => '🇮🇩', 'is_default' => false, 'sort_order' => 14],
            ['code' => 'nl', 'name' => 'Dutch', 'native_name' => 'Nederlands', 'flag' => '🇳🇱', 'is_default' => false, 'sort_order' => 15],
            ['code' => 'pl', 'name' => 'Polish', 'native_name' => 'Polski', 'flag' => '🇵🇱', 'is_default' => false, 'sort_order' => 16],
            ['code' => 'tr', 'name' => 'Turkish', 'native_name' => 'Türkçe', 'flag' => '🇹🇷', 'is_default' => false, 'sort_order' => 17],
            ['code' => 'sv', 'name' => 'Swedish', 'native_name' => 'Svenska', 'flag' => '🇸🇪', 'is_default' => false, 'sort_order' => 18],
            ['code' => 'cs', 'name' => 'Czech', 'native_name' => 'Čeština', 'flag' => '🇨🇿', 'is_default' => false, 'sort_order' => 19],
            ['code' => 'hi', 'name' => 'Hindi', 'native_name' => 'हिन्दी', 'flag' => '🇮🇳', 'is_default' => false, 'sort_order' => 20],
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(
                ['code' => $language['code']],
                $language
            );
        }
    }
}

