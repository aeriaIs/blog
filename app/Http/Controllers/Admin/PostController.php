<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\{Post, Category, Tag, WebSetting};
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use File;
use View;

class PostController extends Controller
{
    public function __construct() {
        $web = WebSetting::all()->first();

        View::share('web', $web);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(auth()->user()->role == 99) {
            $posts = Post::orderBy('created_at', 'desc')->paginate(25);
            $trash_posts = Post::onlyTrashed()->paginate(10);
        }else if(auth()->user()->role == 1) {
            $posts = auth()->user()->posts()->latest()->paginate(25);
        }

        return view('admin.post.index', compact('posts', 'trash_posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.post.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|min:5',
            'category_id' => 'required',
            'content' => 'required|min:20',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:4000'
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $imageSize = $file->getsize();
            $destinationPath = public_path('/storage/post-image/');
            $file->move($destinationPath, $filename);
            $insert['image'] = "$filename";

            $post = Post::create([
                'title' => $request->title,
                'user_id' => auth()->user()->id,
                'slug' => Str::slug($request->title, '-'),
                'category_id' => $request->category_id,
                'content' => $request->content,
                'image' => $filename,
            ]);

            $post->tags()->attach($request->tags);

            return redirect(route('post.index'))->with(['success' => 'Berhasil mebuat post baru']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $post = Post::findOrFail($id);
        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.post.edit', compact('post', 'categories', 'tags'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required|min:5',
            'category_id' => 'required',
            'content' => 'required|min:20',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:4000',
        ]);

        $post = Post::findOrFail($id);


        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $imageSize = $file->getsize();
            $destinationPath = public_path('/storage/post-image');
            $file->move($destinationPath, $filename);
            $insert['image'] = "$filename";
            $image = File::delete($destinationPath . $post->image); 

            $post_data = [
                'title' => $request->title,
                'slug' => Str::slug($request->title, '-'),
                'category_id' => $request->category_id,
                'content' => $request->content,
                'image' => $filename,
            ];

            $post->update($post_data);
            $post->tags()->sync($request->tags);

            return redirect(route('post.index'))->with(['success' => 'Berhasil merubah post']);
        }else {
            $post_data = [
                'title' => $request->title,
                'slug' => Str::slug($request->title, '-'),
                'category_id' => $request->category_id,
                'content' => $request->content,
            ];

            $post->update($post_data);
            $post->tags()->sync($request->tags);

            return redirect(route('post.index'))->with(['success' => 'Berhasil merubah post']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return redirect()->back()->with(['success' => 'Data berhasil dihapus, Silahkan lihat di Post Recycle Bin']);
    }

    public function post_bin() {
        $posts = Post::onlyTrashed()->paginate(10);

        return view('admin.post.post-bin', compact('posts'));
    }

    public function restore($id) {
        $post = Post::withTrashed()->where('id', $id)->first();
        $post->restore();

        return redirect()->back()->with(['success' => 'Data berhasil resstore, Silahkan lihat di List Post']);
    }

    public function clean($id) {
        $post = Post::withTrashed()->where('id', $id)->first();

        // Delete Image
        $destinationPath = public_path('/storage/post-image/');
        $image = File::delete($destinationPath . $post->image); 

        $post->forceDelete();
        
        return redirect()->back()->with(['success' => 'Data berhasil dihapus secara permanen']);
    }

    public function mainPost(Request $request, $id) {
        $post = Post::findOrFail($id);
        $posts = Post::where('main_content', 1)->get();

        if ($request->main_content == 1) {
            if($posts->count() >= 3) {
                return redirect()->back()->with(['error' => 'Main konten tidak dapat melebihi 3 konten.']);
            }else {
                $post->main_content = 1;
            }
        }else {
            $post->main_content = 0;
        }
        $post->update();

        return redirect()->back()->with(['success' => 'Status Main Post sudah berhasil diubah.']);
    }

    public function search(Request $request) {
        $posts = Post::where('title', $request->search)->orWhere('title', 'like', '%'.$request->search.'%')->latest()->paginate(15);

        return view('admin.post.index', compact('posts'));
    }
}
