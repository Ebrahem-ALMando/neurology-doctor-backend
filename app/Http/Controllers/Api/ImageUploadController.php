<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Str;
use Exception;

class ImageUploadController extends Controller
{
    use ApiResponseTrait;

    public function upload(Request $request)
    {
        try {
            $user = $request->user();
            $allowedRoles = ['admin', 'doctor', 'receptionist'];
            if (!$user || !in_array($user->role, $allowedRoles)) {
                return $this->errorResponse('You are not authorized to upload images', 403);
            }
            $request->validate([
                'images' => 'required',
                'images.*' => 'file|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max per image
                'folder' => 'required|string',
            ]);

            $folder = trim($request->folder, '/');
            $files = $request->file('images');
            if (!is_array($files)) {
                $files = [$files];
            }
            $results = [];
            $errors = [];
            foreach ($files as $file) {
                $image = @imagecreatefromstring(file_get_contents($file->getRealPath()));
                if (!$image) {
                    $errors[] = $file->getClientOriginalName();
                    continue;
                }
                $imageName = Str::uuid() . '.webp';
                $path = $folder . '/' . $imageName;
                ob_start();
                imagewebp($image, null, 100);
                $webpData = ob_get_clean();
                Storage::disk('public')->put($path, $webpData);
                imagedestroy($image);
                $results[] = [
                    'image_name' => $imageName,
                    'image_url' => Storage::disk('public')->url($path),
                ];
            }
            return $this->successResponse(
                ['uploaded' => $results, 'failed' => $errors],
                'Images uploaded successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse('Failed to upload images', 500, ['exception' => $e->getMessage()]);
        }
    }
} 