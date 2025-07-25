<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\ConsultationStatusLog;
use App\Http\Resources\ConsultationResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class ConsultationController extends Controller
{
    use ApiResponseTrait;

    // List consultations with filters
    public function index(Request $request)
    {
        try {
            $query = Consultation::with(['patient', 'doctor', 'lastSender', 'messages', 'attachments', 'statusLogs']);
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            if ($request->has('patient_id')) {
                $query->where('patient_id', $request->patient_id);
            }
            if ($request->has('doctor_id')) {
                $query->where('doctor_id', $request->doctor_id);
            }
            $consultations = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 20));
            return $this->successResponse(
                ConsultationResource::collection($consultations),
                'Consultations fetched successfully',
                200,
                [
                    'total' => $consultations->total(),
                    'per_page' => $consultations->perPage(),
                    'current_page' => $consultations->currentPage(),
                    'last_page' => $consultations->lastPage()
                ]
            );
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch consultations', 500, ['exception' => $e->getMessage()]);
        }
    }

    // Show single consultation
    public function show($id)
    {
        try {
            $consultation = Consultation::with(['patient', 'doctor', 'lastSender', 'messages.attachments', 'messages.sender', 'attachments', 'statusLogs.changer'])->find($id);
            if (!$consultation) {
                return $this->errorResponse('Consultation not found', 404);
            }
            return $this->successResponse(new ConsultationResource($consultation), 'Consultation fetched successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch consultation', 500, ['exception' => $e->getMessage()]);
        }
    }

    // Create new consultation
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'patient_id' => 'required|exists:users,id',
                'doctor_id' => 'nullable|exists:users,id',
                'status' => 'nullable|in:open,waiting_response,answered,closed,cancelled',
            ]);
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }
            $data = $validator->validated();
            $consultation = Consultation::create($data);
            // Log initial status
            ConsultationStatusLog::create([
                'consultation_id' => $consultation->id,
                'from_status' => 'open',
                'to_status' => $consultation->status,
                'changed_by_id' => $request->user()->id ?? null,
                'changed_by_type' => $request->user() ? $request->user()->role : 'patient',
                'note' => 'Consultation created',
            ]);
            DB::commit();
            $consultation->load(['patient', 'doctor', 'lastSender', 'messages.attachments', 'messages.sender', 'attachments', 'statusLogs.changer']);
            // Placeholder for live event (broadcast)
            // event(new ConsultationCreated($consultation));
            return $this->successResponse(new ConsultationResource($consultation), 'Consultation created successfully', 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create consultation', 500, ['exception' => $e->getMessage()]);
        }
    }

    // Update consultation (status, doctor, etc.)
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $consultation = Consultation::find($id);
            if (!$consultation) {
                return $this->errorResponse('Consultation not found', 404);
            }
            $validator = Validator::make($request->all(), [
                'doctor_id' => 'nullable|exists:users,id',
                'status' => 'nullable|in:open,waiting_response,answered,closed,cancelled',
            ]);
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }
            $oldStatus = $consultation->status;
            $consultation->update($validator->validated());
            // Log status change if changed
            if ($request->has('status') && $oldStatus !== $request->status) {
                ConsultationStatusLog::create([
                    'consultation_id' => $consultation->id,
                    'from_status' => $oldStatus,
                    'to_status' => $request->status,
                    'changed_by_id' => $request->user()->id ?? null,
                    'changed_by_type' => $request->user() ? $request->user()->role : null,
                    'note' => $request->note ?? null,
                ]);
                // Placeholder for live event (broadcast)
                // event(new ConsultationStatusChanged($consultation));
            }
            DB::commit();
            $consultation->load(['patient', 'doctor', 'lastSender', 'messages.attachments', 'messages.sender', 'attachments', 'statusLogs.changer']);
            return $this->successResponse(new ConsultationResource($consultation), 'Consultation updated successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update consultation', 500, ['exception' => $e->getMessage()]);
        }
    }

    // Update only the status of a consultation
    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $consultation = Consultation::find($id);
            if (!$consultation) {
                return $this->errorResponse('Consultation not found', 404);
            }
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:open,waiting_response,answered,closed,cancelled',
                'note' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }
            $oldStatus = $consultation->status;
            $newStatus = $request->status;
            if ($oldStatus === $newStatus) {
                return $this->errorResponse('Status is already set to the requested value', 422);
            }
            $consultation->status = $newStatus;
            $consultation->save();
            // Log status change
            ConsultationStatusLog::create([
                'consultation_id' => $consultation->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'changed_by_id' => $request->user()->id ?? null,
                'changed_by_type' => $request->user() ? $request->user()->role : null,
                'note' => $request->note ?? null,
            ]);
            DB::commit();
            $consultation->load(['patient', 'doctor', 'lastSender', 'messages.attachments', 'messages.sender', 'attachments', 'statusLogs.changer']);
            return $this->successResponse(new ConsultationResource($consultation), 'Consultation status updated successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update consultation status', 500, ['exception' => $e->getMessage()]);
        }
    }

    // Delete consultation
    public function destroy($id)
    {
        try {
            $consultation = Consultation::find($id);
            if (!$consultation) {
                return $this->errorResponse('Consultation not found', 404);
            }
            $consultation->delete();
            return $this->successResponse(null, 'Consultation deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to delete consultation', 500, ['exception' => $e->getMessage()]);
        }
    }
}
