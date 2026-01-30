<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PostController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $followingIds = $user->following()->pluck('followed_id')->toArray();
        $followingIds[] = $user->id; // Include own posts

        $posts = Post::with(['user', 'likes', 'comments.user', 'shares'])
            ->whereIn('user_id', $followingIds)
            ->where('is_deleted', 0)
            ->withCount(['likes', 'comments', 'shares'])
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $posts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'post_files' => 'nullable|array',
            'post_files.*' => 'file|mimes:jpg,jpeg,png,gif,mp4,mov|max:10240', // 10MB max per file, allowed types
        ]);

        $filePaths = [];

        if ($request->hasFile('post_files')) {
            foreach ($request->file('post_files') as $file) {
                // Store file in public disk, creating a unique path
                $path = $file->store('posts', 'public');
                $filePaths[] = $path;
            }
        }

        $post = Post::create([
            'user_id' => $request->user()->id,
            'description' => $request->description,
            'post_files' => $filePaths,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Post created successfully',
            'data' => $post,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $posts = Post::where('user_id', $id)
            ->where('is_deleted', 0)
            ->with(['user', 'likes', 'comments.user', 'shares'])
            ->withCount(['likes', 'comments', 'shares'])
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $posts,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        if (!$post || $post->user_id !== auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized or post not found.'
            ], 403);
        }

        $request->validate([
            'description' => 'required|string',
            'post_files' => 'nullable|array',
            'post_files.*' => 'file|mimes:jpg,jpeg,png,gif,mp4,mov|max:10240',
        ]);

        $post->description = $request->description;

        $currentFiles = $post->post_files ?: [];
        $filesToKeep = $currentFiles;

        // If existing_files is provided, it defines the subset of old files to preserve.
        // If it's missing, we assume no existing files are being removed (FB style append).
        if ($request->has('existing_files')) {
            $filesToKeep = $request->input('existing_files', []);

            // Handle case where it might be sent as a string/null if empty in FormData
            if (!is_array($filesToKeep)) {
                $filesToKeep = array_filter([$filesToKeep]); // Keep non-empty strings
            }

            // Delete files that are no longer in the 'keep' list
            foreach ($currentFiles as $file) {
                if (!in_array($file, $filesToKeep)) {
                    Storage::disk('public')->delete($file);
                }
            }
        }

        // Handle new file uploads
        $newFilePaths = [];
        if ($request->hasFile('post_files')) {
            foreach ($request->file('post_files') as $file) {
                $path = $file->store('posts', 'public');
                $newFilePaths[] = $path;
            }
        }

        // Final list: preserved old files + new uploads
        $post->post_files = array_merge($filesToKeep, $newFilePaths);

        $post->save();

        $post->load(['user', 'likes', 'comments.user', 'shares'])->loadCount(['likes', 'comments', 'shares']);

        return response()->json([
            'status' => true,
            'message' => 'Post updated successfully',
            'data' => $post,
        ]);
    }

    /**
     * Display posts for a specific user.
     */
    public function userPosts($userId)
    {
        $user = User::find($userId);
        $followingIds = $user->following()->pluck('followed_id')->toArray();
        $followingIds[] = $user->id;

        $posts = Post::with(['user', 'likes', 'comments.user', 'shares'])
            ->whereIn('user_id', $followingIds)
            ->where('is_deleted', 0)
            ->withCount(['likes', 'comments', 'shares'])
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $posts,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        // Authorization
        if ($post->user_id !== auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. You can only delete your own posts.'
            ], 403);
        }

        // For soft delete logic as requested
        $post->update(['is_deleted' => 1]);

        return response()->json([
            'status' => true,
            'message' => 'Post deleted successfully',
        ]);
    }
}
