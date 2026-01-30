<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponser;
    public function getUser(Request $request)
    {
        $user = $request->user();
        return $this->successResponse($user);
    }

    public function getUserDetails($id)
    {
        $getUser = User::where('id', $id)->where('is_deleted', 0)->first();
        if ($getUser) {
            return $this->successResponse($getUser, 'User Details fetched successfully', 200);
        } else {
            return $this->errorResponse('User details not found', 404);
        }
    }

    public function search(Request $request)
    {
        $query = $request->query('q');
        if (!$query) {
            return $this->successResponse([]);
        }

        $users = User::where('is_deleted', 0)
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->where('id', '!=', auth()->id())
            ->limit(10)
            ->get();

        $me = auth()->user();
        if ($me) {
            $myFollowingIds = $me->following()->pluck('followed_id')->toArray();
            foreach ($users as $user) {
                $user->is_following = in_array($user->id, $myFollowingIds);
            }
        }

        return $this->successResponse($users);
    }

    public function getProfile($id)
    {
        $user = User::where('id', $id)
            ->where('is_deleted', 0)
            ->withCount(['followers', 'following', 'posts'])
            ->first();

        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        // Check if current user follows this user
        $me = auth()->user();
        $user->is_following = $me ? $me->following()->where('followed_id', $id)->exists() : false;

        return $this->successResponse($user);
    }
}
