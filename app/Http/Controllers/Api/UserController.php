<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $query = User::query();
            
            // GET /api/users?role=doctor&is_active=true
            // GET /api/users?search=john&sort_by=name&sort_direction=asc
            // GET /api/users?per_page=10
        
            if ($request->has('role')) {
                $query->where('role', $request->role);
            }
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

       
            $sortField = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $query->orderBy($sortField, $sortDirection);

            $users = $query->paginate($request->get('per_page', 20));

            return $this->successResponse(
                UserResource::collection($users),
                'Users fetched successfully',
                200,
                [
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage()
                ]
            );
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch users', 500, ['exception' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:users',
                'phone' => 'nullable|string|max:20',
                'password' => 'nullable|string|min:6',
                'role' => 'required|in:admin,doctor,receptionist,patient',
                'avatar' => 'nullable|string',
                'gender' => 'nullable|in:male,female',
                'birthdate' => 'nullable|date',
                'is_active' => 'boolean',
            ]);

            if(isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }
            $data['is_active'] = $request->boolean('is_active', true);

            $user = User::create($data);

            return $this->successResponse(
                new UserResource($user),
                'User created successfully',
                201,
                [
                    'id' => $user->id,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ]
            );
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, ['errors' => $e->errors()]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to create user', 500, ['exception' => $e->getMessage()]);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

       
            if ($request->has('include')) {
                $includes = explode(',', $request->include);
                $user->load($includes);
            }

            return $this->successResponse(
                new UserResource($user),
                'User fetched successfully',
                200,
                [
                    'id' => $user->id,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ]
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('User not found', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch user', 500, ['exception' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $data = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'password' => 'nullable|string|min:6',
                'role' => 'sometimes|in:admin,doctor,receptionist,patient',
                'avatar' => 'nullable|string',
                'gender' => 'nullable|in:male,female',
                'birthdate' => 'nullable|date',
                'is_active' => 'boolean',
            ]);

            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user->update($data);

            return $this->successResponse(new UserResource($user), 'User updated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('User not found', 404);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, ['errors' => $e->errors()]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update user', 500, ['exception' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return $this->successResponse(null, 'User deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('User not found', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to delete user', 500);
        }
    }
}
