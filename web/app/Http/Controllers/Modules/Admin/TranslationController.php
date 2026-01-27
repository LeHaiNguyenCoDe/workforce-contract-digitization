<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use App\Services\Core\RealTimeTranslationService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TranslationController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private RealTimeTranslationService $translationService
    ) {}

    /**
     * Translate single text
     * POST /api/admin/translate
     */
    public function translate(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string',
            'target_locale' => 'required|string|max:10',
            'source_locale' => 'nullable|string|max:10',
            'save' => 'nullable|boolean',
            'translatable_type' => 'nullable|string',
            'translatable_id' => 'nullable|integer',
            'field' => 'nullable|string',
        ]);

        try {
            $text = $request->input('text');
            $targetLocale = $request->input('target_locale');
            $sourceLocale = $request->input('source_locale', 'vi');
            $shouldSave = $request->input('save', false);

            // Translate text
            $translated = $this->translationService->translate($text, $targetLocale, $sourceLocale);

            // Option A: Save to database if requested
            if ($shouldSave && $request->has('translatable_type') && $request->has('translatable_id')) {
                DB::table('content_translations')->updateOrInsert(
                    [
                        'translatable_type' => $request->input('translatable_type'),
                        'translatable_id' => $request->input('translatable_id'),
                        'field' => $request->input('field', 'name'),
                        'locale' => $targetLocale,
                    ],
                    [
                        'value' => $translated,
                        'is_auto_translated' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            return $this->successResponse([
                'original' => $text,
                'translated' => $translated,
                'source_locale' => $sourceLocale,
                'target_locale' => $targetLocale,
                'saved' => $shouldSave,
            ]);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('translation_error', $ex);
        }
    }

    /**
     * Batch translate multiple texts
     * POST /api/admin/translate/batch
     */
    public function translateBatch(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.text' => 'required|string',
            'items.*.field' => 'nullable|string',
            'target_locale' => 'required|string|max:10',
            'source_locale' => 'nullable|string|max:10',
            'save' => 'nullable|boolean',
            'translatable_type' => 'nullable|string',
            'translatable_id' => 'nullable|integer',
        ]);

        try {
            $items = $request->input('items');
            $targetLocale = $request->input('target_locale');
            $sourceLocale = $request->input('source_locale', 'vi');
            $shouldSave = $request->input('save', false);

            $results = [];
            foreach ($items as $item) {
                $text = $item['text'];
                $field = $item['field'] ?? 'name';
                
                $translated = $this->translationService->translate($text, $targetLocale, $sourceLocale);
                
                $results[] = [
                    'field' => $field,
                    'original' => $text,
                    'translated' => $translated,
                ];

                // Save if requested
                if ($shouldSave && $request->has('translatable_type') && $request->has('translatable_id')) {
                    DB::table('content_translations')->updateOrInsert(
                        [
                            'translatable_type' => $request->input('translatable_type'),
                            'translatable_id' => $request->input('translatable_id'),
                            'field' => $field,
                            'locale' => $targetLocale,
                        ],
                        [
                            'value' => $translated,
                            'is_auto_translated' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }

            return $this->successResponse([
                'translations' => $results,
                'target_locale' => $targetLocale,
                'saved' => $shouldSave,
            ]);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('translation_error', $ex);
        }
    }

    /**
     * Get translation for content (with fallback to on-the-fly)
     * GET /api/admin/translate
     */
    public function getTranslation(Request $request): JsonResponse
    {
        $request->validate([
            'translatable_type' => 'required|string',
            'translatable_id' => 'required|integer',
            'field' => 'required|string',
            'locale' => 'required|string|max:10',
            'fallback_text' => 'nullable|string',
        ]);

        try {
            // Try to get from database first (Option A)
            $translation = DB::table('content_translations')
                ->where('translatable_type', $request->input('translatable_type'))
                ->where('translatable_id', $request->input('translatable_id'))
                ->where('field', $request->input('field'))
                ->where('locale', $request->input('locale'))
                ->first();

            if ($translation) {
                return $this->successResponse([
                    'value' => $translation->value,
                    'is_auto_translated' => $translation->is_auto_translated,
                    'source' => 'database',
                ]);
            }

            // Option B: Translate on-the-fly if fallback_text provided
            if ($request->has('fallback_text')) {
                $translated = $this->translationService->translate(
                    $request->input('fallback_text'),
                    $request->input('locale'),
                    'vi'
                );

                return $this->successResponse([
                    'value' => $translated,
                    'is_auto_translated' => true,
                    'source' => 'on_the_fly',
                ]);
            }

            return $this->successResponse([
                'value' => null,
                'source' => 'not_found',
            ]);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('translation_error', $ex);
        }
    }

    /**
     * Translate all fields for a model to all supported locales
     * POST /api/admin/translate/all
     */
    public function translateAll(Request $request): JsonResponse
    {
        $request->validate([
            'translatable_type' => 'required|string',
            'translatable_id' => 'required|integer',
            'fields' => 'required|array',
            'fields.*.name' => 'required|string',
            'fields.*.value' => 'required|string',
            'source_locale' => 'nullable|string|max:10',
        ]);

        try {
            $sourceLocale = $request->input('source_locale', 'vi');
            $fields = $request->input('fields');
            $translatableType = $request->input('translatable_type');
            $translatableId = $request->input('translatable_id');

            // All supported locales except source
            $targetLocales = ['en', 'ar', 'cs', 'de', 'es', 'fr', 'hi', 'id', 'it', 'ja', 'ko', 'nl', 'pl', 'pt', 'ru', 'sv', 'th', 'tr', 'zh'];
            $targetLocales = array_filter($targetLocales, fn($l) => $l !== $sourceLocale);

            $results = [];
            foreach ($targetLocales as $targetLocale) {
                $results[$targetLocale] = [];
                
                foreach ($fields as $field) {
                    $translated = $this->translationService->translate(
                        $field['value'],
                        $targetLocale,
                        $sourceLocale
                    );

                    // Save to database
                    DB::table('content_translations')->updateOrInsert(
                        [
                            'translatable_type' => $translatableType,
                            'translatable_id' => $translatableId,
                            'field' => $field['name'],
                            'locale' => $targetLocale,
                        ],
                        [
                            'value' => $translated,
                            'is_auto_translated' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );

                    $results[$targetLocale][$field['name']] = $translated;
                }
                
                // Small delay between locales to avoid rate limiting
                usleep(100000); // 100ms
            }

            return $this->successResponse([
                'translations' => $results,
                'locales_count' => count($targetLocales),
                'fields_count' => count($fields),
            ]);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('translation_error', $ex);
        }
    }
}
