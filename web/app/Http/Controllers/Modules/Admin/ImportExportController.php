<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class ImportExportController extends Controller
{
    use StoreApiResponse;

    /**
     * Export products to Excel
     */
    public function exportProducts(Request $request): JsonResponse
    {
        try {
            $products = \App\Models\Product::with('category')->get();

            $data = $products->map(function ($p) {
                return [
                    'ID' => $p->id,
                    'SKU' => $p->sku,
                    'Tên' => $p->name,
                    'Danh mục' => $p->category?->name,
                    'Giá' => $p->price,
                    'Giá gốc' => $p->original_price,
                    'Tồn kho' => $p->stock_quantity ?? 0,
                    'Trạng thái' => $p->is_active ? 'Đang bán' : 'Ngừng bán',
                ];
            });

            // For simplicity, return JSON - in production use Laravel Excel
            return $this->successResponse([
                'headers' => ['ID', 'SKU', 'Tên', 'Danh mục', 'Giá', 'Giá gốc', 'Tồn kho', 'Trạng thái'],
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Export orders to Excel
     */
    public function exportOrders(Request $request): JsonResponse
    {
        try {
            $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
            $toDate = $request->input('to_date', now()->toDateString());

            $orders = \App\Models\Order::with('user')
                ->whereBetween('created_at', [$fromDate, $toDate . ' 23:59:59'])
                ->get();

            $data = $orders->map(function ($o) {
                return [
                    'Mã đơn' => $o->order_number,
                    'Khách hàng' => $o->user?->name,
                    'Email' => $o->user?->email,
                    'Tổng tiền' => $o->total,
                    'Trạng thái' => $o->status,
                    'Ngày đặt' => $o->created_at->format('d/m/Y H:i'),
                ];
            });

            return $this->successResponse([
                'headers' => ['Mã đơn', 'Khách hàng', 'Email', 'Tổng tiền', 'Trạng thái', 'Ngày đặt'],
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Import products from Excel/CSV
     */
    public function importProducts(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
            ]);

            $file = $request->file('file');
            $path = $file->store('imports', 'local');

            // Read file (simplified - in production use Laravel Excel)
            $content = Storage::disk('local')->get($path);
            $lines = array_filter(explode("\n", $content));

            $imported = 0;
            $errors = [];

            foreach (array_slice($lines, 1) as $index => $line) { // Skip header
                $row = str_getcsv($line);

                if (count($row) < 3) {
                    $errors[] = "Dòng " . ($index + 2) . ": Thiếu dữ liệu";
                    continue;
                }

                try {
                    \App\Models\Product::updateOrCreate(
                        ['sku' => $row[0]],
                        [
                            'name' => $row[1],
                            'price' => floatval($row[2] ?? 0),
                            'original_price' => floatval($row[3] ?? $row[2] ?? 0),
                        ]
                    );
                    $imported++;
                } catch (Exception $e) {
                    $errors[] = "Dòng " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            Storage::disk('local')->delete($path);

            return $this->successResponse([
                'imported' => $imported,
                'errors' => $errors,
            ], "Đã import {$imported} sản phẩm");
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Download import template
     */
    public function downloadTemplate(string $type): JsonResponse
    {
        try {
            $templates = [
                'products' => [
                    'headers' => ['SKU', 'Tên sản phẩm', 'Giá bán', 'Giá gốc', 'Danh mục ID'],
                    'sample' => ['SP001', 'Sản phẩm mẫu', '100000', '120000', '1'],
                ],
                'customers' => [
                    'headers' => ['Tên', 'Email', 'Số điện thoại', 'Địa chỉ'],
                    'sample' => ['Nguyễn Văn A', 'a@example.com', '0901234567', 'Hà Nội'],
                ],
            ];

            if (!isset($templates[$type])) {
                return $this->errorResponse('Template không tồn tại', 404);
            }

            return $this->successResponse($templates[$type]);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }
}
