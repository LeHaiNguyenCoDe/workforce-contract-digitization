<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private $module;

    public function __construct()
    {
        $this->module = 'user';
    }

    /**
     * Display a listing of the users.
     */
    public function index(Request $req): JsonResponse
    {
        try {
            $perPage = $req->get('per_page', 15);
            $search = $req->get('search');
            $columns = ['id', 'name', 'email', 'created_at'];

            $query = User::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $users = $query->select($columns)->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $users,
            ]);
        } catch (\Exception $ex) {
            Helper::trackingError($this->module, $ex->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Store a newly created user.
     */
    public function store(UserRequest $req): JsonResponse
    {
        try {
            $validator = $req->storeValidator();
            if ($validator->count()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator,
                ], 422);
            }

            $data = $this->getDataRequest($req, 'create');
            $user = User::create($data);

            $data_log['action'] = getAction($this->module);
            $data_log['obj_action'] = json_encode(array());
            $data_log['new_value'] = json_encode($req->all());
            Helper::addLog($data_log);

            return response()->json([
                'status' => 'success',
                'message' => 'Operation completed successfully',
                'data' => $user,
            ], 201);
        } catch (\Exception $ex) {
            Helper::trackingError($this->module, $ex->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        try {
            return response()->json([
                'status' => 'success',
                'data' => $user,
            ]);
        } catch (\Exception $ex) {
            Helper::trackingError($this->module, $ex->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Update the specified user.
     */
    public function update(UserRequest $req, User $user): JsonResponse
    {
        try {
            $validator = $req->updateValidator();
            if ($validator->count()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator,
                ], 422);
            }

            $oldValue = $user->toArray();
            $data = $this->getDataRequest($req, 'update');
            $user->update($data);

            $data_log['action'] = getAction($this->module);
            $data_log['obj_action'] = json_encode(array($user->id));
            $data_log['old_value'] = json_encode($oldValue);
            $data_log['new_value'] = json_encode($req->all());
            Helper::addLog($data_log);

            return response()->json([
                'status' => 'success',
                'message' => 'Operation completed successfully',
                'data' => $user->fresh(),
            ]);
        } catch (\Exception $ex) {
            Helper::trackingError($this->module, $ex->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            $oldValue = $user->toArray();
            
            // Set deleted_by and active before soft delete
            $user->deleted_by = auth()->user()->id ?? 0;
            $user->active = false;
            $user->save();
            
            // Soft delete
            $user->delete();

            $data_log['action'] = getAction($this->module, 'destroy');
            $data_log['obj_action'] = json_encode(array($user->id));
            $data_log['old_value'] = json_encode($oldValue);
            Helper::addLog($data_log);

            return response()->json([
                'status' => 'success',
                'message' => 'Data deleted successfully',
            ]);
        } catch (\Exception $ex) {
            Helper::trackingError($this->module, $ex->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Get data request for create/update
     *
     * @param UserRequest $req
     * @param string|null $action
     * @return array
     */
    private function getDataRequest(UserRequest $req, ?string $action = null): array
    {
        $baseData = [
            'name' => $req->name,
            'email' => $req->email,
        ];

        // Add password if provided (only hash when password is present)
        $passwordData = $req->filled('password')
            ? ['password' => Hash::make($req->password)]
            : [];

        // Get audit fields based on action
        $auditData = $this->getAuditData($action);

        return array_merge($baseData, $passwordData, $auditData);
    }

    /**
     * Get audit fields (created_at, updated_at, created_by, updated_by) based on action
     *
     * @param string|null $action
     * @return array
     */
    private function getAuditData(?string $action): array
    {
        $userId = auth()->id() ?? 0;
        $timestamp = Carbon::now()->toDateTimeString();

        return match ($action) {
            'create' => [
                'created_at' => $timestamp,
                'created_by' => $userId,
            ],
            'update' => [
                'updated_at' => $timestamp,
                'updated_by' => $userId,
            ],
            default => [],
        };
    }
}
