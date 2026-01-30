<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function follow(Request $request, $id)
    {
        $userToFollow = User::findOrFail($id);
        $me = $request->user();

        if ($me->id == $userToFollow->id) {
            return response()->json(['status' => false, 'message' => 'You cannot follow yourself'], 400);
        }

        $follow = Follow::updateOrCreate(
            ['follower_id' => $me->id, 'followed_id' => $userToFollow->id],
            ['is_deleted' => 0]
        );

        return response()->json([
            'status' => true,
            'message' => 'Followed successfully',
            'data' => $follow
        ]);
    }

    public function unfollow(Request $request, $id)
    {
        $me = $request->user();

        Follow::where('follower_id', $me->id)
            ->where('followed_id', $id)
            ->update(['is_deleted' => 1]);

        return response()->json([
            'status' => true,
            'message' => 'Unfollowed successfully'
        ]);
    }

    public function followings(Request $request)
    {
        $followings = $request->user()->following()->get();
        return response()->json([
            'status' => true,
            'data' => $followings
        ]);
    }
}
