<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Like;
use App\Models\Comment;
use App\Models\Share;
use Illuminate\Http\Request;

class InteractionController extends Controller
{
    public function like(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $user = $request->user();

        $like = Like::updateOrCreate(
            ['user_id' => $user->id, 'post_id' => $post->id],
            ['is_deleted' => 0]
        );

        return response()->json([
            'status' => true,
            'message' => 'Liked successfully',
            'data' => $like
        ]);
    }

    public function unlike(Request $request, $id)
    {
        $user = $request->user();

        Like::where('user_id', $user->id)
            ->where('post_id', $id)
            ->update(['is_deleted' => 1]);

        return response()->json([
            'status' => true,
            'message' => 'Unliked successfully'
        ]);
    }

    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string'
        ]);

        $post = Post::findOrFail($id);

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'post_id' => $post->id,
            'comment' => $request->comment,
            'is_deleted' => 0
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Comment added successfully',
            'data' => $comment->load('user')
        ]);
    }

    public function showComments($id)
    {
        $comments = Comment::where('post_id', $id)
            ->where('is_deleted', 0)
            ->with('user')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $comments
        ]);
    }

    public function share(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $share = Share::create([
            'user_id' => $request->user()->id,
            'post_id' => $post->id,
            'is_deleted' => 0
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Shared successfully',
            'data' => $share
        ]);
    }
}
