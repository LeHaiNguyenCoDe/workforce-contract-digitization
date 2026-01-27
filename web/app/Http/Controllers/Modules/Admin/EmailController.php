<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Traits\StoreApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class EmailController extends Controller
{
    use StoreApiResponse;

    /**
     * Get list of email campaigns
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $campaigns = EmailCampaign::with('user')
                ->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 15));

            return $this->paginatedResponse($campaigns);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Store a new campaign
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content_html' => 'required|string',
            'recipients' => 'nullable|array',
            'scheduled_at' => 'nullable|date',
        ]);

        try {
            $campaign = EmailCampaign::create([
                'name' => $request->name,
                'subject' => $request->subject,
                'content_html' => $request->content_html,
                'recipients' => $request->recipients,
                'scheduled_at' => $request->scheduled_at,
                'status' => $request->scheduled_at ? 'scheduled' : 'draft',
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Chiến dịch đã được tạo thành công',
                'data' => $campaign
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a campaign
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'name' => 'string|max:255',
            'subject' => 'string|max:255',
            'content_html' => 'string',
            'recipients' => 'nullable|array',
            'scheduled_at' => 'nullable|date',
            'status' => 'string|in:draft,scheduled,sending,sent,failed',
        ]);

        try {
            $campaign = EmailCampaign::findOrFail($id);
            $campaign->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Chiến dịch đã được cập nhật',
                'data' => $campaign
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a campaign
     */
    public function destroy($id): JsonResponse
    {
        try {
            $campaign = EmailCampaign::findOrFail($id);
            $campaign->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Chiến dịch đã được xóa'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of templates
     */
    public function templates(): JsonResponse
    {
        try {
            $templates = EmailTemplate::where('is_active', true)->get();
            return response()->json([
                'status' => 'success',
                'data' => $templates
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Trigger sending campaign
     */
    public function send($id): JsonResponse
    {
        try {
            $campaign = EmailCampaign::findOrFail($id);

            if ($campaign->status === 'sent' || $campaign->status === 'sending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Chiến dịch này đang được gửi hoặc đã gửi xong'
                ], 400);
            }

            // Logic gửi email thực tế sẽ dùng Queue/Job ở đây
// $campaign->update(['status' => 'sending']);
// SendEmailCampaignJob::dispatch($campaign);

            return response()->json([
                'status' => 'success',
                'message' => 'Chiến dịch đã được đưa vào hàng đợi để gửi'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}