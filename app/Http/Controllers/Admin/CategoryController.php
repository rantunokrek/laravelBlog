<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::latest()->get();
        return view('admin.category.index',compact('categories'));
   
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.category.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    { // Get Form Image

        $this->validate($request , [
            'name'          => 'required|unique:categories',
            'image'         =>  'required|image|mimes:jpeg,bmp,png,jpg'
        ]);
        $image = $request->file('image');
        $slug = str::slug($request->name);
        if (isset($image))
        {
            $currentDate = Carbon::now()->toDateString();
            $imageName = $slug.'-'.$currentDate.'-'.uniqid().'.'.$image->getClientOriginalExtension();
            // Check if Category Dir exists
            if (!Storage::disk('public')->exists('category'))
            {
                Storage::disk('public')->makeDirectory('category');
            }
            // Resize image for category and upload
            Image::make($image)->resize(1000, 800)->save(storage_path('app/public/category/').'/'.$imageName);

            // Check if Category Slider Dir exists
            if (!Storage::disk('public')->exists('category/slider'))
            {
                Storage::disk('public')->makeDirectory('category/slider');
            }

            // Resize image for category slider and upload
          Image::make($image)->resize(1000, 400)->save(storage_path('app/public/category/slider/') .$imageName);

        }
        else
        {
            $imageName = 'default.png';
        }

        $category = new Category();
        $category->name = $request->name;
        $category->slug = $slug;
        $category->image = $imageName;

        $category->save();
        Toastr::success('Category Saved Successfully','Success');
        return redirect()->route('admin.category.index');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $imgs = Category::latest()->get();
        return view('admin.category.edit',compact('imgs'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $category = Category::find($id);
     
        return view('admin.category.edit',compact('category'));
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
        $this->validate($request , [
            'name'          => 'required',
            'image'         =>  'image|mimes:jpeg,bmp,png,jpg'
        ]);

        // Get Form Image
        $image = $request->file('image');
        $slug = str::slug($request->name);
        $category = Category::find($id);
        if (isset($image))
        {
            $currentDate = Carbon::now()->toDateString();
            $imageName = $slug.'-'.$currentDate.'-'.uniqid().'.'.$image->getClientOriginalExtension();
            
            // Check if Category Dir exists
            if (!Storage::disk('public')->exists('category'))
            {
                Storage::disk('public')->makeDirectory('category');
            }
             // delete old image
             if (Storage::disk('public')->exists('category/'.$category->image)) {
                Storage::disk('public')->delete('category/'.$category->image);
             }
              // Resize image for category and upload
              $categoryName = Image::make($image)->resize(1000,800)->stream();
            
             Storage::disk('public')->put('category/'.$imageName,$categoryName);
           
          

            // Check if Category Slider Dir exists
            if (!Storage::disk('public')->exists('category/slider'))
            {
                Storage::disk('public')->makeDirectory('category/slider');
            }
             // delete old image
             if (Storage::disk('public')->exists('category/slider/'.$category->image)) {
                Storage::disk('public')->delete('category/slider/'.$category->image);
             }
            // Resize image for category slider and upload
            $slider = Image::make($image)->resize(1000,400)->stream();
            
            Storage::disk('public')->put('category/slider/'.$imageName,$slider);
          

        }
        else
        {
            $imageName = $category->image;
        }

        $category->name = $request->name;
        $category->slug = $slug;
        $category->image = $imageName;

        $category->save();
        Toastr::success('Category updated Successfully','Success');
        return redirect()->route('admin.category.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category =  Category::find($id);
        if (Storage::disk('public')->exists('category/'.$category->image)) {
            Storage::disk('public')->delete('category/'.$category->image);
        }
        if (Storage::disk('public')->exists('category/slider/'.$category->image)) {
          Storage::disk('public')->delete('category/slider/'.$category->image);
      }
      $category->delete();
      Toastr::success('Category delete susccessfully','Success');
      return redirect()->back();
    }
}
