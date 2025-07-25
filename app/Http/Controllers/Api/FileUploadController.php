<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Str;
use Exception;

class FileUploadController extends Controller
{
    use ApiResponseTrait;

    public function upload(Request $request)
    {
        try {
            $user = $request->user();
            $allowedRoles = ['admin', 'doctor', 'receptionist'];
            if (!$user || !in_array($user->role, $allowedRoles)) {
                return $this->errorResponse('You are not authorized to upload files', 403);
            }
            $request->validate([
                'files' => 'required',
                'files.*' => 'file|max:20480', // 20MB max per file
                'folder' => 'required|string',
            ]);

            $folder = trim($request->folder, '/');
            $files = $request->file('files');
            if (!is_array($files)) {
                $files = [$files];
            }
            $results = [];
            $errors = [];
            foreach ($files as $file) {
                $originalName = $file->getClientOriginalName();
                $extension = strtolower($file->getClientOriginalExtension());
                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                $fileType = $isImage ? 'image' : $extension;
                $fileName = Str::uuid() . ($isImage ? '.webp' : ('.' . $extension));
                $path = $folder . '/' . $fileName;
                try {
                    if ($isImage) {
                        $image = @imagecreatefromstring(file_get_contents($file->getRealPath()));
                        if (!$image) {
                            $errors[] = $originalName;
                            continue;
                        }
                        ob_start();
                        imagewebp($image, null, 100);
                        $webpData = ob_get_clean();
                        Storage::disk('uploads')->put($path, $webpData);
                        imagedestroy($image);
                    } else {
                        Storage::disk('uploads')->put($path, file_get_contents($file->getRealPath()));
                    }
                    $results[] = [
                        'original_name' => $originalName,
                        'file_name' => $fileName,
                        'file_type' => $fileType,
                        'file_path' => $path,
                        'file_url' => Storage::disk('uploads')->url($path),
                        'file_size' => $file->getSize(),
                    ];
                } catch (Exception $e) {
                    $errors[] = $originalName;
                }
            }
            return $this->successResponse(
                ['uploaded' => $results, 'failed' => $errors],
                'Files uploaded successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse('Failed to upload files', 500, ['exception' => $e->getMessage()]);
        }
    }
}
