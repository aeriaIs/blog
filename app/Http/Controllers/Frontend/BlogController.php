<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\{Post, Category, Tag};
use Str;

class BlogController extends Controller
{
    public function index(Post $post) {
    	$recent_post = $post->latest()->take(6)->get();
    	$posts = $post->orderBy('created_at', 'asc')->paginate(3);
    	$newest_post = Post::latest()->paginate(5);
    	$categories = Category::take(5)->get();
    	$navCategories = Category::latest()->take(3)->get();

    	return view('frontend.index', compact('recent_post', 'posts', 'newest_post', 'categories', 'navCategories'));
    }

    public function show($post) {
    	$post = Post::where('slug', $post)->first();
    	$categories = Category::take(5)->get();
    	$navCategories = Category::latest()->take(3)->get();

    	return view('frontend.show', compact('post', 'categories', 'navCategories'));
    }

    public function category(Category $category) {
    	$categories = Category::take(5)->get();
    	$navCategories = Category::latest()->take(3)->get();
    	$posts = Post::latest()->paginate(5);
    	$posts = $category->posts()->paginate(5);

    	return view('frontend.category-show', compact('categories', 'navCategories', 'posts', 'category'));
    }

    public function listCategory() {
    	$categories = Category::take(5)->get();
    	$navCategories = Category::latest()->take(3)->get();
    	$all_categories = Category::all();

    	return view('frontend.list-category', compact('categories', 'navCategories', 'all_categories'));
    }
}
