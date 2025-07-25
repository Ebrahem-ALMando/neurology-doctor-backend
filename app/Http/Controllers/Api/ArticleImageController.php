<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ArticleImage;
use App\Http\Resources\ArticleImageResource;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Exception;

class ArticleImageController extends Controller
{
    use ApiResponseTrait;

    // عرض كل الصور مع فلترة حسب article_id
    public function index(Request $request)
    {
        try {
            $query = ArticleImage::query();
            if ($request->has('article_id')) {
                $query->where('article_id', $request->article_id);
            }
            $images = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 20));
            return $this->successResponse(ArticleImageResource::collection($images), 'Images fetched successfully', 200, [
                'total' => $images->total(),
                'per_page' => $images->perPage(),
                'current_page' => $images->currentPage(),
                'last_page' => $images->lastPage()
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch images', 500, ['exception' => $e->getMessage()]);
        }
    }

    // إضافة صورة (admin, doctor, receptionist فقط)
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            $allowedRoles = ['admin', 'doctor', 'receptionist'];
            if (!$user || !in_array($user->role, $allowedRoles)) {
                return $this->errorResponse('You are not authorized to add images', 403);
            }
            $data = $request->validate([
                'article_id' => 'required|integer|exists:articles,id',
                'image_name' => 'required|string',
                'folder' => 'required|string',
                'is_cover' => 'boolean',
                'caption' => 'nullable|string',
            ]);
            $image = ArticleImage::create($data);
            return $this->successResponse(new ArticleImageResource($image), 'Image created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to create image', 500, ['exception' => $e->getMessage()]);
        }
    }

    // عرض صورة واحدة
    public function show($id)
    {
        try {
            $image = ArticleImage::find($id);
            if (!$image) {
                return $this->errorResponse('Image not found', 404);
            }
            return $this->successResponse(new ArticleImageResource($image), 'Image fetched successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Image not found', 404);
        }
    }

    // تحديث صورة (admin, doctor, receptionist فقط)
    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();
            $allowedRoles = ['admin', 'doctor', 'receptionist'];
            if (!$user || !in_array($user->role, $allowedRoles)) {
                return $this->errorResponse('You are not authorized to update images', 403);
            }
            $image = ArticleImage::find($id);
            if (!$image) {
                return $this->errorResponse('Image not found', 404);
            }
            $data = $request->validate([
                'article_id' => 'sometimes|integer|exists:articles,id',
                'image_name' => 'sometimes|string',
                'folder' => 'sometimes|string',
                'is_cover' => 'boolean',
                'caption' => 'nullable|string',
            ]);
            $image->update($data);
            return $this->successResponse(new ArticleImageResource($image), 'Image updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update image', 500, ['exception' => $e->getMessage()]);
        }
    }

    // حذف صورة (admin, doctor, receptionist فقط)
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $allowedRoles = ['admin', 'doctor', 'receptionist'];
            if (!$user || !in_array($user->role, $allowedRoles)) {
                return $this->errorResponse('You are not authorized to delete images', 403);
            }
            $image = ArticleImage::find($id);
            if (!$image) {
                return $this->errorResponse('Image not found', 404);
            }
            $image->delete();
            return $this->successResponse(null, 'Image deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to delete image', 500, ['exception' => $e->getMessage()]);
        }
    }
}
