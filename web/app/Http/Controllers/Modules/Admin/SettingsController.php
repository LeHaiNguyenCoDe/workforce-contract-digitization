<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class SettingsController extends Controller
{
    use StoreApiResponse;

    /**
     * Get all settings grouped by section
     */
    public function index(): JsonResponse
    {
        try {
            $settings = DB::table('settings')->get()->groupBy('group');

            $result = [];
            foreach ($settings as $group => $items) {
                $result[$group] = [];
                foreach ($items as $item) {
                    $result[$group][$item->key] = $this->castValue($item->value, $item->type);
                }
            }

            return $this->successResponse($result);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Get settings by group
     */
    public function getByGroup(string $group): JsonResponse
    {
        try {
            $settings = DB::table('settings')
                ->where('group', $group)
                ->get();

            $result = [];
            foreach ($settings as $item) {
                $result[$item->key] = $this->castValue($item->value, $item->type);
            }

            return $this->successResponse($result);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Update settings for a group
     */
    public function update(Request $request, string $group): JsonResponse
    {
        try {
            $data = $request->all();

            foreach ($data as $key => $value) {
                // Handle file uploads (like logo)
                if ($request->hasFile($key)) {
                    $file = $request->file($key);
                    $path = $file->store('settings', 'public');
                    $value = Storage::url($path);
                }

                DB::table('settings')->updateOrInsert(
                    ['group' => $group, 'key' => $key],
                    [
                        'value' => is_array($value) ? json_encode($value) : $value,
                        'type' => is_array($value) ? 'json' : (is_bool($value) ? 'boolean' : 'string'),
                        'updated_at' => now(),
                    ]
                );
            }

            return $this->successResponse(null, 'Cập nhật cấu hình thành công');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Get single setting value
     */
    public function getSetting(string $group, string $key): JsonResponse
    {
        try {
            $setting = DB::table('settings')
                ->where('group', $group)
                ->where('key', $key)
                ->first();

            if (!$setting) {
                return $this->successResponse(null);
            }

            return $this->successResponse($this->castValue($setting->value, $setting->type));
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Cast value based on type
     */
    private function castValue($value, $type)
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }
}
