<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConsultationStatusLog;
use App\Http\Resources\ConsultationStatusLogResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Exception;

class ConsultationStatusLogController extends Controller
{
    use ApiResponseTrait;

    // List status logs for a consultation with filters and pagination
    public function index(Request $request)
    {
        try {
            $query = ConsultationStatusLog::with(['changer']);
            if ($request->filled('consultation_id')) {
                $query->where('consultation_id', $request->consultation_id);
            }
            if ($request->filled('from_status')) {
                $query->where('from_status', $request->from_status);
            }
            if ($request->filled('to_status')) {
                $query->where('to_status', $request->to_status);
            }
            if ($request->filled('changed_by_id')) {
                $query->where('changed_by_id', $request->changed_by_id);
            }
            if ($request->filled('changed_by_type')) {
                $query->where('changed_by_type', $request->changed_by_type);
            }
            if ($request->filled('from_date')) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->filled('to_date')) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('note', 'like', "%$search%") ;
            }
            $perPage = $request->get('per_page', 20);
            $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);
            return $this->successResponse(
                ConsultationStatusLogResource::collection($logs),
                'Status logs fetched successfully',
                200,
                [
                    'total' => $logs->total(),
                    'per_page' => $logs->perPage(),
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage()
                ]
            );
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch status logs', 500, ['exception' => $e->getMessage()]);
        }
    }
}
