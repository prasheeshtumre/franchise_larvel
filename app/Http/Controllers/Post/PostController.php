<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;

use App\Models\Post;
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
    public function index()
    {
        $posts = Post::with('user')->latest()->get();

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
        $post = Post::where('user_id', $id)
            ->with('user')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $post,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        $this->authorize('update', $post);

        $request->validate([
            'description' => 'required|string',
            'post_files' => 'nullable|array',
            'post_files.*' => 'file|mimes:jpg,jpeg,png,gif,mp4,mov|max:10240',
        ]);

        $post->description = $request->description;

        if ($request->hasFile('post_files')) {
            // Optional: Delete old files?
            // Existing requirement says "update post", often implies replacing or adding.
            // For this implementation, I will assume replacing files if new ones are provided,
            // or we could append. Re-reading usually implies replacement in CRUD.
            // Let's replace for simplicity and "clean" state, but standard social feeds might append.
            // Given "update a post" and "files", replacing the array seems safest for consistency unless specified.
            // Let's delete old files to be clean.

            if ($post->post_files) {
                foreach ($post->post_files as $oldFile) {
                    Storage::disk('public')->delete($oldFile);
                }
            }

            $filePaths = [];
            foreach ($request->file('post_files') as $file) {
                $path = $file->store('posts', 'public');
                $filePaths[] = $path;
            }
            $post->post_files = $filePaths;
        }

        $post->save();

        return response()->json([
            'status' => true,
            'message' => 'Post updated successfully',
            'data' => $post,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        if ($post->post_files) {
            foreach ($post->post_files as $file) {
                Storage::disk('public')->delete($file);
            }
        }

        $post->delete();

        return response()->json([
            'status' => true,
            'message' => 'Post deleted successfully',
        ]);
    }
}
