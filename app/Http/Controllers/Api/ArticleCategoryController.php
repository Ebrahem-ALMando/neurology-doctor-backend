<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use App\Http\Resources\ArticleCategoryResource;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Validator;
use Exception;

class ArticleCategoryController extends Controller
{
    use ApiResponseTrait;

   
    public function index(Request $request)
    {
        try {
            $query = ArticleCategory::query();
            if ($request->has('search')) {
                $search = $request->search;
                $query->where('name', 'like', "%{$search}%");
            }
            $categories = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 20));
            return $this->successResponse(ArticleCategoryResource::collection($categories), 'Categories fetched successfully', 200, [
                'total' => $categories->total(),
                'per_page' => $categories->perPage(),
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage()
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch categories', 500, ['exception' => $e->getMessage()]);
        }
    }

    // (admin, doctor, receptionist )
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            $allowedRoles = ['admin', 'doctor', 'receptionist'];
            if (!$user || !in_array($user->role, $allowedRoles)) {
                return $this->errorResponse('You are not authorized to add categories', 403);
            }
            $data = $request->validate([
                'name' => 'required|string|max:255|unique:article_categories,name',
            ]);
            $category = ArticleCategory::create($data);
            return $this->successResponse(new ArticleCategoryResource($category), 'Category created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to create category', 500, ['exception' => $e->getMessage()]);
        }
    }


    public function show($id)
    {
        try {
            $category = ArticleCategory::findOrFail($id);
            return $this->successResponse(new ArticleCategoryResource($category), 'Category fetched successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Category not found', 404);
        }
    }

    // (admin, doctor, receptionist )
    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();
            $allowedRoles = ['admin', 'doctor', 'receptionist'];
            if (!$user || !in_array($user->role, $allowedRoles)) {
                return $this->errorResponse('You are not authorized to update categories', 403);
            }
            $category = ArticleCategory::find($id);
            if (!$category) {
                return $this->errorResponse('Category not found', 404);
            }
            $data = $request->validate([
                'name' => 'required|string|max:255|unique:article_categories,name,' . $id,
            ]);
            $category->update($data);
            return $this->successResponse(new ArticleCategoryResource($category), 'Category updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update category', 500, ['exception' => $e->getMessage()]);
        }
    }

    // (admin, doctor, receptionist )
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $allowedRoles = ['admin', 'doctor', 'receptionist'];
            if (!$user || !in_array($user->role, $allowedRoles)) {
                return $this->errorResponse('You are not authorized to delete categories', 403);
            }
            $category = ArticleCategory::find($id);
            if (!$category) {
                return $this->errorResponse('Category not found', 404);
            }
            $category->delete();
            return $this->successResponse(null, 'Category deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to delete category', 500, ['exception' => $e->getMessage()]);
        }
    }
}
