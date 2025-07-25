<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConsultationAttachment;
use App\Http\Resources\ConsultationAttachmentResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Storage;

class ConsultationAttachmentController extends Controller
{
    use ApiResponseTrait;

    // List attachments for a consultation or message, with filters and pagination
    public function index(Request $request)
    {
        try {
            $query = ConsultationAttachment::query();
            if ($request->filled('consultation_id')) {
                $query->where('consultation_id', $request->consultation_id);
            }
            if ($request->filled('consultation_message_id')) {
                $query->where('consultation_message_id', $request->consultation_message_id);
            }
            if ($request->filled('file_type')) {
                $query->where('file_type', $request->file_type);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('original_name', 'like', "%$search%")
                      ->orWhere('file_name', 'like', "%$search%") ;
                });
            }
            $perPage = $request->get('per_page', 20);
            $attachments = $query->orderBy('created_at', 'desc')->paginate($perPage);
            return $this->successResponse(
                ConsultationAttachmentResource::collection($attachments),
                'Attachments fetched successfully',
                200,
                [
                    'total' => $attachments->total(),
                    'per_page' => $attachments->perPage(),
                    'current_page' => $attachments->currentPage(),
                    'last_page' => $attachments->lastPage()
                ]
            );
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch attachments', 500, ['exception' => $e->getMessage()]);
        }
    }

    // Show single attachment
    public function show($id)
    {
        try {
            $attachment = ConsultationAttachment::find($id);
            if (!$attachment) {
                return $this->errorResponse('Attachment not found', 404);
            }
            return $this->successResponse(new ConsultationAttachmentResource($attachment), 'Attachment fetched successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch attachment', 500, ['exception' => $e->getMessage()]);
        }
    }

    // Delete attachment
    public function destroy($id)
    {
        try {
            $attachment = ConsultationAttachment::find($id);
            if (!$attachment) {
                return $this->errorResponse('Attachment not found', 404);
            }
            // Delete file from storage if exists
            if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }
            $attachment->delete();
            return $this->successResponse(null, 'Attachment deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to delete attachment', 500, ['exception' => $e->getMessage()]);
        }
    }
}
