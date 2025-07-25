<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Database\QueryException;

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * Normalize phone number: remove spaces, convert Arabic digits, ensure leading +, remove non-numeric except +
     */
    private function normalizePhone($phone)
    {
      
        $phone = preg_replace('/\s+/', '', $phone);
      
        $phone = strtr($phone, ['٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);
       
        if (strpos($phone, '+') !== 0) {
            $phone = '+' . ltrim($phone, '+');
        }
      
        $phone = preg_replace('/[^\d+]/', '', $phone);
        return $phone;
    }

    // Step 1: Login (send OTP)
    public function login(Request $request)
    {
        try {
            $allowedRoles = ['admin', 'doctor', 'receptionist', 'patient'];
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string',
                'role' => 'required|string',
                'device_token' => 'nullable|string',
                'device_type' => 'nullable|in:mobile,web',
            ]);
            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', 422, ['errors' => $validator->errors()]);
            }
            $phone = $this->normalizePhone($request->phone);
            $role = $request->role;
            if (!in_array($role, $allowedRoles)) {
                return $this->errorResponse('Invalid role. Allowed roles: admin, doctor, receptionist, patient', 422);
            }
            $otp = '666666'; // For development only

            // Find or create user
            $user = User::where('phone', $phone)->where('role', $role)->first();
            if (!$user) {
                $user = User::create([
                    'phone' => $phone,
                    'role' => $role,
                    'name' => 'User_' . Str::random(5),
                    'is_active' => true,
                ]);
            }
            $user->device_token = $request->device_token;
            $user->device_type = $request->device_type;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'OTP sent to your phone',
                'key' => 'OTP sent to your phone',
                'data' => [
                    'phone' => $user->phone,
                    'role' => $user->role,
                ],
            ], 200);
        } catch (QueryException $e) {
            return $this->errorResponse('Database error', 500, ['exception' => $e->getMessage()]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to login', 500, ['exception' => $e->getMessage()]);
        }
    }

    // Step 2: Verify OTP
    public function verifyOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string',
                'role' => 'required|string',
                'otp' => 'required|string',
                'device_token' => 'nullable|string',
                'device_type' => 'nullable|in:mobile,web',
            ]);
            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', 422, ['errors' => $validator->errors()]);
            }
            $phone = $this->normalizePhone($request->phone);
            $role = $request->role;
            $otp = $request->otp;
            $allowedRoles = ['admin', 'doctor', 'receptionist', 'patient'];
            if (!in_array($role, $allowedRoles)) {
                return $this->errorResponse('Invalid role. Allowed roles: admin, doctor, receptionist, patient', 422);
            }
            if ($otp !== '666666') {
                return $this->errorResponse('Invalid OTP', 401);
            }
            $user = User::where('phone', $phone)->where('role', $role)->first();
            if (!$user) {
                return $this->errorResponse('User not found', 404);
            }
            $user->device_token = $request->device_token;
            $user->device_type = $request->device_type;
            $user->save();

            // Device-based token logic
            $deviceToken = $request->device_token ?? 'default';
            $deviceType = $request->device_type ?? 'web';
            $tokenName = $deviceToken . '-' . $deviceType;
            // $tokenName = substr(hash('sha256', $deviceToken . '-' . $deviceType), 0, 30);

            $existingToken = $user->tokens()->where('name', $tokenName)->first();
            if ($existingToken) {
                $token = $existingToken->plainTextToken ?? $existingToken->token;
            } else {
                $token = $user->createToken($tokenName)->plainTextToken;
            }

            return $this->successResponse([
                'user' => $user,
                'token' => $token,
            ], 'OTP verified, login successful');
        } catch (QueryException $e) {
            return $this->errorResponse('Database error', 500, ['exception' => $e->getMessage()]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to verify OTP', 500, ['exception' => $e->getMessage()]);
        }
    }

    // Step 3: Logout       
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return $this->errorResponse('User not authenticated', 401);
            }
            $token = $user->currentAccessToken();
            if ($token) {
                $token->delete();
                return $this->successResponse(null, 'Logged out successfully');
            } else {
                return $this->errorResponse('No active token found', 401);
            }
        } catch (\Throwable $e) {
            return $this->errorResponse('Unexpected error', 500, [
                'exception' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTrace() : null
            ]);
        }
    }
    public function logoutAll(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return $this->errorResponse('User not authenticated', 401);
            }
            $user->tokens()->delete();
            return $this->successResponse(null, 'Logged out from all devices');
        } catch (\Throwable $e) {
            return $this->errorResponse('Unexpected error', 500, [
                'exception' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTrace() : null
            ]);
        }
    }
    
} 