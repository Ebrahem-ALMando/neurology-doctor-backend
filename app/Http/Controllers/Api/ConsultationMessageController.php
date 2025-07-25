<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConsultationMessage;
use App\Models\ConsultationAttachment;
use App\Http\Resources\ConsultationMessageResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class ConsultationMessageController extends Controller
{
    use ApiResponseTrait;

    // List messages for a consultation with filters, search, and pagination
    public function index(Request $request, $consultation_id)
    {
        try {
            $query = ConsultationMessage::with(['sender', 'attachments'])
                ->where('consultation_id', $consultation_id);

            // Filters
            if ($request->filled('sender_id')) {
                $query->where('sender_id', $request->sender_id);
            }
            if ($request->filled('sender_type')) {
                $query->where('sender_type', $request->sender_type);
            }
            if ($request->filled('from_date')) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->filled('to_date')) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }
            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('message', 'like', "%$search%")
                      ->orWhere('subject', 'like', "%$search%") ;
                });
            }
            // Pagination
            $perPage = $request->get('per_page', 20);
            $messages = $query->orderBy('created_at', 'asc')->paginate($perPage);
            return $this->successResponse(
                ConsultationMessageResource::collection($messages),
                'Messages fetched successfully',
                200,
                [
                    'total' => $messages->total(),
                    'per_page' => $messages->perPage(),
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage()
                ]
            );
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch messages', 500, ['exception' => $e->getMessage()]);
        }
    }

    // Send a new message (with optional attachments)
    public function store(Request $request, $consultation_id)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'sender_id' => 'required|exists:users,id',
                'sender_type' => 'required|in:doctor,patient',
                'subject' => 'nullable|string|max:255',
                'message' => 'required|string',
                'attachments' => 'nullable|array',
                'attachments.*.file_name' => 'required_with:attachments|string',
                'attachments.*.original_name' => 'required_with:attachments|string',
                'attachments.*.file_path' => 'required_with:attachments|string',
                'attachments.*.file_type' => 'required_with:attachments|string',
            ]);
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }
            $data = $validator->validated();
            $message = ConsultationMessage::create([
                'consultation_id' => $consultation_id,
                'sender_id' => $data['sender_id'],
                'sender_type' => $data['sender_type'],
                'subject' => $data['subject'] ?? null,
                'message' => $data['message'],
                'read_by_patient' => $data['sender_type'] === 'patient' ? true : false,
                'read_by_doctor' => $data['sender_type'] === 'doctor' ? true : false,
            ]);
            // Attachments
            if (!empty($data['attachments'])) {
                foreach ($data['attachments'] as $att) {
                    ConsultationAttachment::create([
                        'consultation_id' => $consultation_id,
                        'consultation_message_id' => $message->id,
                        'file_name' => $att['file_name'],
                        'original_name' => $att['original_name'],
                        'file_path' => $att['file_path'],
                        'file_type' => $att['file_type'],
                    ]);
                }
            }
            DB::commit();
            $message->load(['sender', 'attachments']);
            // Placeholder for live event (broadcast)
            // event(new ConsultationMessageSent($message));
            return $this->successResponse(new ConsultationMessageResource($message), 'Message sent successfully', 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to send message', 500, ['exception' => $e->getMessage()]);
        }
    }

    // Update a message (only by sender or admin)
    public function update(Request $request, $id)
    {
        try {
            $message = ConsultationMessage::find($id);
            if (!$message) {
                return $this->errorResponse('Message not found', 404);
            }
            $validator = Validator::make($request->all(), [
                'subject' => 'nullable|string|max:255',
                'message' => 'required|string',
            ]);
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }
            $message->update($validator->validated());
            $message->load(['sender', 'attachments']);
            return $this->successResponse(new ConsultationMessageResource($message), 'Message updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update message', 500, ['exception' => $e->getMessage()]);
        }
    }

    // Soft delete a message
    public function destroy($id)
    {
        try {
            $message = ConsultationMessage::find($id);
            if (!$message) {
                return $this->errorResponse('Message not found', 404);
            }
            $message->delete();
            return $this->successResponse(null, 'Message deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to delete message', 500, ['exception' => $e->getMessage()]);
        }
    }

    // Mark message as read by patient
    public function markAsReadByPatient($id)
    {
        try {
            $message = ConsultationMessage::find($id);
            if (!$message) {
                return $this->errorResponse('Message not found', 404);
            }
            $message->read_by_patient = true;
            $message->save();
            return $this->successResponse(new ConsultationMessageResource($message), 'Marked as read by patient');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to mark as read', 500, ['exception' => $e->getMessage()]);
        }
    }

    // Mark message as read by doctor
    public function markAsReadByDoctor($id)
    {
        try {
            $message = ConsultationMessage::find($id);
            if (!$message) {
                return $this->errorResponse('Message not found', 404);
            }
            $message->read_by_doctor = true;
            $message->save();
            return $this->successResponse(new ConsultationMessageResource($message), 'Marked as read by doctor');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to mark as read', 500, ['exception' => $e->getMessage()]);
        }
    }
}
