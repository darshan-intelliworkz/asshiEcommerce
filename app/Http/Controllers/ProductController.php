<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Color;
use App\Models\PostSize;
use App\Models\ProductImage;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::all();
        // $products=Product::getAllProduct();
        // return $products;
        return view('backend.product.index')->with('products',$products);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $brand=Brand::get();
        $category=Category::where('is_parent',1)->whereNull('deleted_at')->get();
        $postSize=PostSize::get();
        // return $category;
        return view('backend.product.create')->with('categories',$category)->with('brands',$brand)->with('postSize',$postSize);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $slug = Str::slug($request->product_code);
        $validator = Validator::make(array_merge($request->all(), ['slug' => $slug]), [
            'product_code'=>'string|required',
            'title'=>'string|nullable',
            'description'=>'string|nullable',
            // 'stock'=>"nullable|numeric",
            'cat_id'=>'required|exists:categories,id',
            // 'brand_id'=>'nullable|exists:brands,id',
            'child_cat_id'=>'nullable|exists:categories,id',
            // 'is_featured'=>'sometimes|in:1',
            'status'=>'required|in:active,inactive',
            'condition'=>'nullable|in:default,new,hot',
            'discount'=>'nullable|numeric',
            'hsn_code' => 'required',
            'gst_percent' => 'required|numeric|min:0|max:100',
            'stock' => 'required|numeric',
            
            //'photo' => 'required|array|min:1',             // Must be an array and at least 1 file
            //'photo.*' => 'file|mimes:jpg,jpeg,png,gif|max:2048', // Each file must be an image and max 2MB
            'size'         => 'required|array|min:1',
            'size.*'       => 'required|string|max:50',
            'price'        => 'required|array|min:1',
            'price.*'      => 'required|numeric|min:0',
            // 'video' => 'nullable|mimes:mp4,mov,avi,wmv|max:20480', // max 20MB
            // 'slug'      => [
            //     'required',
            //     Rule::unique('products')->whereNull('deleted_at'),
            // ],
    
        ],[
            //'photo.required' => 'Please upload at least one image.',
            //'photo.array' => 'Invalid file upload.',
            //'photo.*.file' => 'Each uploaded file must be a valid file.',
            //'photo.*.mimes' => 'Only jpg, jpeg, png, gif files are allowed.',
            //'photo.*.max' => 'Each file may not be greater than 2MB.',
            'size.required'    => 'Please add at least one size.',
            'size.array'       => 'Size must be in a valid format.',
            'size.min'         => 'Please add at least one size.',
            'size.*.required'  => 'Size field is required.',
            'size.*.string'    => 'Size must be valid text.',
            'size.*.max'       => 'Size may not be greater than 50 characters.',
            'price.required'   => 'Please add at least one price.',
            'price.array'      => 'Price must be in a valid format.',
            'price.min'        => 'Please add at least one price.',
            'price.*.required' => 'Price field is required.',
            'price.*.numeric'  => 'Price must be a number.',
            'price.*.min'      => 'Price must be zero or greater.',
            
            // 'slug.unique'              => 'Product title already exists.',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $count=Product::where('slug',$slug)->count();
        
        $size = $request->input('size');
        $size_price = $request->input('price');
        $setSizePrice = [
            'size' => $size,
            'price' => $size_price,
        ];
        $jsonData = json_encode($setSizePrice);

        if ($request->hasFile('photo')) {
            $sheets = $request->file('photo');
            $upload_files = [];
            foreach ($sheets as $sheet) {
                $filename = $sheet->getClientOriginalName();
                $extension = $sheet->getClientOriginalExtension();
                $filename = mt_rand(10000000000,99999999999) . '.' . $extension;
                // $filename = str_replace(' ', '_', time(), $filename); // replace spaces with underscores
                $path = public_path('storage/photos/1/Products');
                $sheet->move($path, $filename);
                $upload_files[]= '/storage/photos/1/Products/'.$filename;
                
            }
            $product = [
                'product_code' => $request->product_code, 
                'title' => $request->title, 
                'description' => $request->description, 
                'product_features' => $request->product_features ?? null,
                'size_chart' => $request->size_chart ?? null,
                'cat_id' => $request->cat_id, 
                'child_cat_id' => $request->child_cat_id, 
                'discount' => $request->discount, 
                'hsn_code' => $request->hsn_code,
                'gst_percent' => $request->gst_percent,
                'brand_id' => $request->brand_id, 
                'condition' => $request->condition, 
                'stock' => $request->stock, 
                'status' => $request->status, 
                'size' => $jsonData, 
                'slug' => $slug, 
                'is_featured' => $request->input('is_featured',0), 
                'photo' => implode(',', $upload_files), 
            ];
        } else {
            $product = [
                'product_code' => $request->product_code,
                'title' => $request->title, 
                'description' => $request->description, 
                'product_features' => $request->product_features ?? null,
                'size_chart' => $request->size_chart ?? null,
                'cat_id' => $request->cat_id, 
                'child_cat_id' => $request->child_cat_id, 
                'discount' => $request->discount,
                'hsn_code' => $request->hsn_code,
                'gst_percent' => $request->gst_percent,
                'brand_id' => $request->brand_id, 
                'condition' => $request->condition, 
                'stock' => $request->stock, 
                'status' => $request->status, 
                'size' => $jsonData, 
                'slug' => $slug, 
                'is_featured' => $request->input('is_featured',0), 
            ];
        }
        
        
        // dd($product);
        $product=Product::create($product);
        if($count>0){
            $slug=$slug.'-'.$product->id;
            $product->slug = $slug;
            $product->save();
        }

        foreach ($request->color_name as $index => $colorName) {
            $color = new Color();
            $color->product_id = $product->id;
            $color->color_name = $colorName;
            $color->color_code = $request->color_code[$index];
            $color->save();
    
            if ($request->hasFile("color_images.$index")) {
                foreach ($request->file("color_images.$index") as $file) {
                    $filename = time() . $file->getClientOriginalName();
                    $imagePath = public_path('storage/products');
                    $file->move($imagePath, $filename);
                    $image = new ProductImage();
                    $image->color_id = $color->id;
                    $image->image = $filename;
                    $image->save();
                }
            }
        }

        // $videoName = null;
        // if ($request->hasFile('video')) {
        //     $video = $request->file('video');
        //     $videoName = time().'_'.$product->id.'_'.$video->getClientOriginalName();
        //     $video->move(public_path('product_videos'), $videoName);
        // }
        // $product->video = $videoName;
        // $product->save();

        if($product){
            request()->session()->flash('success','Product Successfully added');
        }
        else{
            request()->session()->flash('error','Please try again!!');
        }
        return redirect()->route('product.index');

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
        $brand=Brand::get();
        $product=Product::findOrFail($id);
        $category=Category::where('is_parent',1)->whereNull('deleted_at')->get();
        $items=Product::where('id',$id)->get();
        $postSize=PostSize::get();
        // return $items;
        return view('backend.product.edit')->with('product',$product)
                    ->with('brands',$brand)
                    ->with('categories',$category)->with('items',$items)->with('postSize',$postSize);
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
        $slug = Str::slug($request->product_code);
        $product=Product::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'product_code'=>'string|required',
            'title'=>'string|nullable',
            'description'=>'string|nullable',
            'cat_id'=>'required|exists:categories,id',
            'child_cat_id'=>'nullable|exists:categories,id',
            'status'=>'required|in:active,inactive',
            'condition'=>'nullable|in:default,new,hot',
            'discount'=>'nullable|numeric',
            'hsn_code' => 'required',
            'gst_percent' => 'required|numeric|min:0|max:100',
            'stock' => 'required|numeric',
            
            'size'         => 'required|array|min:1',
            'size.*'       => 'required|string|max:50',
            'price'        => 'required|array|min:1',
            'price.*'      => 'required|numeric|min:0',
            //'video' => 'nullable|mimes:mp4,mov,avi,wmv|max:20480',
        ],[
            // Size messages
            'size.required'    => 'Please add at least one size.',
            'size.array'       => 'Size must be in a valid format.',
            'size.min'         => 'Please add at least one size.',
            'size.*.required'  => 'Size field is required.',
            'size.*.string'    => 'Size must be valid text.',
            'size.*.max'       => 'Size may not be greater than 50 characters.',
    
            // Price messages
            'price.required'   => 'Please add at least one price.',
            'price.array'      => 'Price must be in a valid format.',
            'price.min'        => 'Please add at least one price.',
            'price.*.required' => 'Price field is required.',
            'price.*.numeric'  => 'Price must be a number.',
            'price.*.min'      => 'Price must be zero or greater.',
        ]);
        // Add manual slug validation
        $validator->after(function ($validator) use ($slug, $product) {
            $exists = Product::where('slug', $slug)
                ->whereNull('deleted_at')
                ->where('id', '!=', $product->id)
                ->first();
            if ($exists) {
                $validator->errors()->add('title', 'Product title already exists.');
            }
        });
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // $checkSlug = Product::where('title',$request->title)->first();
        // if(!$checkSlug){
        //     $slug=Str::slug($request->title);
        //     $productSlug = Product::find($id);
        //     $productSlug->slug = $slug;
        //     $productSlug->save();
        // }
        
        $size = $request->input('size');
        $size_price = $request->input('price');
        $setSizePrice = [
            'size' => $size,
            'price' => $size_price,
        ];
        $jsonData = json_encode($setSizePrice);

        $count=Product::where('slug',$slug)->where('id', '!=', $product->id)->count();
        if($count>0){
            $slug=$slug.'-'.$product->id;
        }
        
        if ($request->hasFile('photo')) {
            $sheets = $request->file('photo');
            $upload_files = [];
            foreach ($sheets as $sheet) {
                $filename = $sheet->getClientOriginalName();
                $extension = $sheet->getClientOriginalExtension();
                $filename = mt_rand(10000000000,99999999999) . '.' . $extension;
                // $filename = str_replace(' ', '_', time(), $filename); // replace spaces with underscores
                $path = public_path('storage/photos/1/Products');
                $sheet->move($path, $filename);
                $upload_files[]= '/storage/photos/1/Products/'.$filename;
                
            }
            $product = Product::find($id);
            $product->product_code = $request->product_code;
            $product->title = $request->title;
            $product->slug = $slug;
            $product->description = $request->description;
            $product->product_features = $request->product_features ?? null;
            $product->size_chart = $request->size_chart ?? null;
            $product->cat_id = $request->cat_id;
            $product->child_cat_id = $request->child_cat_id;
            $product->discount = $request->discount;
            $product->hsn_code = $request->hsn_code;
            $product->gst_percent = $request->gst_percent;
            $product->brand_id = $request->brand_id;
            $product->condition = $request->condition;
            $product->stock = $request->stock;
            $product->status = $request->status;
            $product->size = $jsonData;
            $product->is_featured = $request->input('is_featured',0);
            $product->photo = implode(',', $upload_files);
            $product->save();
        } else {
            $product = Product::find($id);
            $product->product_code = $request->product_code;
            $product->title = $request->title;
            $product->slug = $slug;
            $product->description = $request->description;
            $product->product_features = $request->product_features ?? null;
            $product->size_chart = $request->size_chart ?? null;
            $product->cat_id = $request->cat_id;
            $product->child_cat_id = $request->child_cat_id;
            $product->discount = $request->discount;
            $product->hsn_code = $request->hsn_code;
            $product->gst_percent = $request->gst_percent;
            $product->brand_id = $request->brand_id;
            $product->condition = $request->condition;
            $product->stock = $request->stock;
            $product->status = $request->status;
            $product->size = $jsonData;
            $product->is_featured = $request->input('is_featured',0);
            $product->save();
        }
        if(isset($request->color_name)){
            foreach ($request->color_name as $index => $colorName) {
                // dd($request->color_id);
                if (isset($request->color_id[$index])) {
                    // Update existing color
                    $color = Color::find($request->color_id[$index]);
                    $color->color_name = $colorName;
                    $color->color_code = $request->color_code[$index];
                    $color->save();
                } else {
                    // Create new color
                    $color = new Color();
                    $color->product_id = $id;
                    $color->color_name = $colorName;
                    $color->color_code = $request->color_code[$index];
                    $color->save();
                }
                if ($request->hasFile("color_images.$index")) {
                    foreach ($request->file("color_images.$index") as $file) {
                        $filename = time() . $file->getClientOriginalName();
                        $imagePath = public_path('storage/products');
                        $file->move($imagePath, $filename);
                        $image = new ProductImage();
                        $image->color_id = $color->id;
                        $image->image = $filename;
                        $image->save();
                    }
                }
            }
        }
        if ($request->deleted_colors) {
            $deletedColorIds = explode(',', $request->deleted_colors);
            foreach ($deletedColorIds as $colorId) {
                $color = Color::find($colorId);
                if ($color) {
                    // Optionally, delete associated images here as well
                    foreach ($color->images as $image) {
                        $image->delete();
                    }
                    $color->delete();
                }
            }
        }

        if ($request->has('delete_images')) {
            foreach ($request->delete_images as $imageId) {
                $image = ProductImage::find($imageId);
                if ($image) {
                    $image->delete();
                }
            }
        }
        // if ($request->hasFile('video')) {
        //     // Delete old video if exists
        //     if (!empty($product->video) && file_exists(public_path('product_videos/'.$product->video))) {
        //         unlink(public_path('product_videos/'.$product->video));
        //     }
        //     // Upload new video
        //     $video = $request->file('video');
        //     $videoName = time() . '_' . $product->id . '_' . $video->getClientOriginalName();
        //     $video->move(public_path('product_videos'), $videoName);
        //     $product->video = $videoName;
        //     $product->save();
        // }

        if($product){
            request()->session()->flash('success','Product Successfully updated');
        }
        else{
            request()->session()->flash('error','Please try again!!');
        }
        return redirect()->route('product.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product=Product::findOrFail($id);
        $status=$product->delete();
        
        if($status){
            request()->session()->flash('success','Product successfully deleted');
        }
        else{
            request()->session()->flash('error','Error while deleting product');
        }
        return redirect()->route('product.index');
    }
}
