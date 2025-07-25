<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Http\Resources\ArticleResource;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Exception;

class ArticleController extends Controller
{
    use ApiResponseTrait;

    // عرض كل المقالات مع بحث وفلترة
    public function index(Request $request)
    {
        try {
            $query = Article::with(['doctor', 'category', 'images', 'comments' => function($q) {
                $q->whereNull('parent_id')->with('user', 'children.user');
            }]);
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
                });
            }
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }
            if ($request->has('doctor_id')) {
                $query->where('doctor_id', $request->doctor_id);
            }
            if ($request->has('is_published')) {
                $query->where('is_published', $request->boolean('is_published'));
            }
            $articles = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 20));
            return $this->successResponse(ArticleResource::collection($articles), 'Articles fetched successfully', 200, [
                'total' => $articles->total(),
                'per_page' => $articles->perPage(),
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage()
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch articles', 500, ['exception' => $e->getMessage()]);
        }
    }

    // إضافة مقال جديد (admin, doctor, receptionist فقط)
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            $allowedRoles = ['admin', 'doctor', 'receptionist'];
            if (!$user || !in_array($user->role, $allowedRoles)) {
                return $this->errorResponse('You are not authorized to add articles', 403);
            }
            $data = $request->validate([
                'doctor_id' => 'nullable|integer|exists:users,id',
                'category_id' => 'required|integer|exists:article_categories,id',
                'title' => 'required|string|max:255',
                'short_description' => 'nullable|string',
                'content' => 'required|string',
                'is_published' => 'boolean',
                'published_at' => 'nullable|date',
            ]);
            $data['views_count'] = 0;
            $article = Article::create($data);
            $article->load(['doctor', 'category', 'images', 'comments' => function($q) {
                $q->whereNull('parent_id')->with('user', 'children.user');
            }]);
            return $this->successResponse(new ArticleResource($article), 'Article created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to create article', 500, ['exception' => $e->getMessage()]);
        }
    }

    // عرض مقال واحد
    public function show($id)
    {
        try {
            $article = Article::with(['doctor', 'category', 'images', 'comments' => function($q) {
                $q->whereNull('parent_id')->with('user', 'children.user');
            }])->find($id);
            if (!$article) {
                return $this->errorResponse('Article not found', 404);
            }
            return $this->successResponse(new ArticleResource($article), 'Article fetched successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Article not found', 404);
        }
    }

    // تحديث مقال (admin, doctor, receptionist فقط)
    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();
            $allowedRoles = ['admin', 'doctor', 'receptionist'];
            if (!$user || !in_array($user->role, $allowedRoles)) {
                return $this->errorResponse('You are not authorized to update articles', 403);
            }
            $article = Article::find($id);
            if (!$article) {
                return $this->errorResponse('Article not found', 404);
            }
            $data = $request->validate([
                'doctor_id' => 'nullable|integer|exists:users,id',
                'category_id' => 'sometimes|integer|exists:article_categories,id',
                'title' => 'sometimes|string|max:255',
                'short_description' => 'nullable|string',
                'content' => 'sometimes|string',
                'is_published' => 'boolean',
                'published_at' => 'nullable|date',
            ]);
            $article->update($data);
            $article->load(['doctor', 'category', 'images', 'comments' => function($q) {
                $q->whereNull('parent_id')->with('user', 'children.user');
            }]);
            return $this->successResponse(new ArticleResource($article), 'Article updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update article', 500, ['exception' => $e->getMessage()]);
        }
    }

    // حذف مقال (admin, doctor, receptionist فقط)
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $allowedRoles = ['admin', 'doctor', 'receptionist'];
            if (!$user || !in_array($user->role, $allowedRoles)) {
                return $this->errorResponse('You are not authorized to delete articles', 403);
            }
            $article = Article::find($id);
            if (!$article) {
                return $this->errorResponse('Article not found', 404);
            }
            $article->delete();
            return $this->successResponse(null, 'Article deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to delete article', 500, ['exception' => $e->getMessage()]);
        }
    }
}
