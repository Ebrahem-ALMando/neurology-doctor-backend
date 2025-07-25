<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ArticleComment;
use App\Http\Resources\ArticleCommentResource;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Exception;

class ArticleCommentController extends Controller
{
    use ApiResponseTrait;

    // عرض كل التعليقات مع فلترة حسب article_id وparent_id
    public function index(Request $request)
    {
        try {
            $query = ArticleComment::with(['user']);
            if ($request->has('article_id')) {
                $query->where('article_id', $request->article_id);
            }
            if ($request->has('parent_id')) {
                $query->where('parent_id', $request->parent_id);
            } else {
                $query->whereNull('parent_id'); // جلب التعليقات الرئيسية فقط افتراضيًا
            }
            $comments = $query->orderBy('created_at', 'asc')->paginate($request->get('per_page', 20));
            $comments->load('children.user');
            return $this->successResponse(ArticleCommentResource::collection($comments), 'Comments fetched successfully', 200, [
                'total' => $comments->total(),
                'per_page' => $comments->perPage(),
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage()
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch comments', 500, ['exception' => $e->getMessage()]);
        }
    }

    // إضافة تعليق (فقط للمستخدمين المصادقين)
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return $this->errorResponse('You are not authorized to add comments', 403);
            }
            $data = $request->validate([
                'article_id' => 'required|integer|exists:articles,id',
                'parent_id' => 'nullable|integer|exists:article_comments,id',
                'content' => 'required|string',
            ]);
            $data['user_id'] = $user->id;
            $comment = ArticleComment::create($data);
            $comment->load('user', 'children.user');
            return $this->successResponse(new ArticleCommentResource($comment), 'Comment created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to create comment', 500, ['exception' => $e->getMessage()]);
        }
    }

    // عرض تعليق واحد
    public function show($id)
    {
        try {
            $comment = ArticleComment::with(['user', 'children.user'])->find($id);
            if (!$comment) {
                return $this->errorResponse('Comment not found', 404);
            }
            return $this->successResponse(new ArticleCommentResource($comment), 'Comment fetched successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Comment not found', 404);
        }
    }

    // تحديث تعليق (فقط لصاحب التعليق أو admin)
    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();
            $comment = ArticleComment::find($id);
            if (!$comment) {
                return $this->errorResponse('Comment not found', 404);
            }
            if (!$user || ($user->id !== $comment->user_id && $user->role !== 'admin')) {
                return $this->errorResponse('You are not authorized to update this comment', 403);
            }
            $data = $request->validate([
                'content' => 'required|string',
            ]);
            $comment->update($data);
            $comment->load('user', 'children.user');
            return $this->successResponse(new ArticleCommentResource($comment), 'Comment updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update comment', 500, ['exception' => $e->getMessage()]);
        }
    }

    // حذف تعليق (فقط لصاحب التعليق أو admin)
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $comment = ArticleComment::find($id);
            if (!$comment) {
                return $this->errorResponse('Comment not found', 404);
            }
            if (!$user || ($user->id !== $comment->user_id && $user->role !== 'admin')) {
                return $this->errorResponse('You are not authorized to delete this comment', 403);
            }
            $comment->delete();
            return $this->successResponse(null, 'Comment deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to delete comment', 500, ['exception' => $e->getMessage()]);
        }
    }
}
