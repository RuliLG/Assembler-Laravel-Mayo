<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpsertPostRequest;
use App\Models\Post;

class PostsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'posts' => Post::published()
                ->orderByDesc('published_at')
                ->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UpsertPostRequest $request)
    {
        $validated = $request->validated();
        $categories = $validated['categories'];
        unset($validated['categories']);
        $post = Post::create($validated);

        $post->categories()->attach($categories);
        return response()->json([
            'post' => $post,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        $post->load('categories:id,name');
        if ($post->published_at->isFuture()) {
            abort(404);
        }

        return response()->json([
            'post' => $post,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpsertPostRequest $request, Post $post)
    {
        $validated = $request->validated();

        $categories = $validated['categories'];
        unset($validated['categories']);
        $post->update($validated);

        $post->categories()->detach();
        $post->categories()->attach($categories);

        return response()->json([
            'post' => $post,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();
        return response()->json([
            'status' => 'success',
        ]);
    }
}
