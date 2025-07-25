<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get authenticated user's profile
     */
    public function show()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return $this->errorResponse('User not found', 404);
            }
            
            return $this->successResponse([
                'user' => new UserResource($user),
                'token' => $user->createToken('auth-token')->plainTextToken
            ], 'Profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve profile', 500);
        }
    }

    /**
     * Update authenticated user's profile
     */
    public function update(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('user not found', 404);
            }
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'role' => 'sometimes|string|in:admin,doctor,receptionist,patient',
                'gender' => 'sometimes|string|in:male,female,other',
                'birthdate' => 'sometimes|date|before:today',
                'blood_type' => 'sometimes|string|max:10',
                'allergy' => 'sometimes|string',
                'chronic_diseases' => 'sometimes|string',
                'is_active' => 'sometimes|boolean',
                'device_token' => 'sometimes|string|max:255',
                'device_type' => 'sometimes|string|in:android,ios,web',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user->update($request->only([
                'name', 'role', 'gender', 'birthdate', 'blood_type', 'allergy', 'chronic_diseases',
                'is_active', 'device_token', 'device_type'
            ]));

            return $this->successResponse(
                new UserResource($user),
                'Profile updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update profile', 500);
        }
    }

    /**
     * Upload profile avatar
     */
    public function uploadAvatar(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('user not found', 404);
            }
            $validator = Validator::make($request->all(), [
                'avatar_name' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Delete old avatar if exists
            if ($user->avatar) {
                $oldAvatarPath = 'users/' . $user->avatar;
                if (Storage::disk('public')->exists($oldAvatarPath)) {
                    Storage::disk('public')->delete($oldAvatarPath);
                }
            }

            // Get image name from request
            $imageName = $request->avatar_name;
            $folder = 'users';

            // Check if image exists in storage
            $imagePath = $folder . '/' . $imageName;
            if (!Storage::disk('public')->exists($imagePath)) {
                return $this->errorResponse('Image not found in storage', 404);
            }

            // Update user avatar with the image name
            $user->update(['avatar' => $imageName]);

            return $this->successResponse([
                'avatar_name' => $imageName,
                'folder' => $folder,
                'avatar_url' => Storage::disk('public')->url($imagePath),
                'user' => new UserResource($user)
            ], 'Avatar updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update avatar', 500);
        }
    }

    /**
     * Remove profile avatar
     */
    public function removeAvatar()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('user not found', 404);
            }
            if (!$user->avatar) {
                return $this->errorResponse('No avatar to remove', 404);
            }

            // Delete file from storage
            $avatarPath = 'users/' . $user->avatar;
            if (Storage::disk('public')->exists($avatarPath)) {
                Storage::disk('public')->delete($avatarPath);
            }

            // Update user avatar to null
            $user->update(['avatar' => null]);

            return $this->successResponse(
                new UserResource($user),
                'Avatar removed successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to remove avatar', 500);
        }
    }
}
