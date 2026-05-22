<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $category=Category::getAllCategory();
        // return $category;
        return view('backend.category.index')->with('categories',$category);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $parent_cats=Category::where('is_parent',1)->orderBy('title','ASC')->get();
        return view('backend.category.create')->with('parent_cats',$parent_cats);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $slug = Str::slug($request->title);
        $validator = Validator::make(array_merge($request->all(), ['slug' => $slug]), [
            'title' => 'string|required',
            'summary' => 'string|nullable',
            'status' => 'required|in:active,inactive',
            'gst' => 'required_if:is_parent,1',
            'is_parent' => 'sometimes|in:1',
            'parent_id' => 'nullable|exists:categories,id|required_unless:is_parent,1',
            
            'slug'      => [
                'required',
                Rule::unique('categories')->whereNull('deleted_at')->where('is_parent', 1),
            ],
        ], [
            'gst.required_if' => 'GST percent is required if This is a Parent Categor.',
            'parent_id.required_unless' => 'Child category is required if this is not a parent category.',
            'parent_id.exists' => 'The selected parent category is invalid.',
            
            'slug.unique'              => 'Category title already exists.',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $data= $request->all();
        if($request->hasFile('photo') && $request->file('photo')->isValid()){
            $imageName = mt_rand(10000000000,99999999999).'.'.$request->photo->extension();  
            $request->photo->move(public_path('storage/photos/1/Category'), $imageName);
            $data['photo'] = '/storage/photos/1/Category/'.$imageName;
        }
        $slug=Str::slug($request->title);
        $count=Category::where('slug',$slug)->count();
        if($count>0){
            $slug=$slug.'-'.date('ymdis').'-'.rand(0,999);
        }
        $data['slug']=$slug;
        $data['is_parent']=$request->input('is_parent',0);
        // return $data;   
        $status=Category::create($data);
        if($status){
            request()->session()->flash('success','Category successfully added');
        }
        else{
            request()->session()->flash('error','Error occurred, Please try again!');
        }
        return redirect()->route('category.index');


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
        $parent_cats=Category::where('is_parent',1)->get();
        $category=Category::findOrFail($id);
        return view('backend.category.edit')->with('category',$category)->with('parent_cats',$parent_cats);
    }

    public function update(Request $request, $id)
    {
        $slug = Str::slug($request->title);
        $category=Category::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'title' => 'string|required',
            'summary' => 'string|nullable',
            'status' => 'required|in:active,inactive',
            'is_parent' => 'sometimes|in:1',
            'gst' => 'required_if:is_parent,1',
            'parent_id' => 'nullable|exists:categories,id|required_unless:is_parent,1'
            
        ], [
            //'slug.unique'              => 'Category title already exists.',
            'gst.required_if' => 'GST percent is required if This is a Parent Categor.',
            'parent_id.required_unless' => 'Child category is required if this is not a parent category.',
            'parent_id.exists' => 'The selected parent category is invalid.',
        ]);
        
        // Add manual slug validation
        $validator->after(function ($validator) use ($slug, $category) {
            $exists = Category::where('slug', $slug)
                ->whereNull('deleted_at')->where('is_parent', 1)
                ->where('id', '!=', $category->id)
                ->exists();
            if ($exists) {
                $validator->errors()->add('title', 'Category title already exists.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $data= $request->all();
        if($request->hasFile('photo') && $request->file('photo')->isValid()){
            $imageName = mt_rand(10000000000,99999999999).'.'.$request->photo->extension();  
            $request->photo->move(public_path('storage/photos/1/Category'), $imageName);
            $data['photo'] = '/storage/photos/1/Category/'.$imageName;
        }
        $data['is_parent']=$request->input('is_parent',0);
        $data['slug'] = $slug;
        // return $data;
        $status=$category->fill($data)->save();
        if($status){
            request()->session()->flash('success','Category successfully updated');
        }
        else{
            request()->session()->flash('error','Error occurred, Please try again!');
        }
        return redirect()->route('category.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category=Category::findOrFail($id);
        $productExists = Product::where(function ($query) use ($id) {
                            $query->where('cat_id', $id)
                                  ->orWhere('child_cat_id', $id);
                        })->exists();
        if($productExists){
            request()->session()->flash('error','You can not delete this category as it associated with some Product');
            return redirect()->back();
        }
        $child_cat_id=Category::where('parent_id',$id)->pluck('id');
        if(isset($child_cat_id) && is_countable($child_cat_id) && count($child_cat_id) > 0){
            request()->session()->flash('error','You can not delete this category as it associated with some Child Category');
            return redirect()->back();
        }
        $status=$category->delete();
        
        if($status){
            if(count($child_cat_id)>0){
                Category::shiftChild($child_cat_id);
            }
            request()->session()->flash('success','Category successfully deleted');
        }
        else{
            request()->session()->flash('error','Error while deleting category');
        }
        return redirect()->route('category.index');
    }

    public function getChildByParent(Request $request){
        // dd($request->all());
        // return $request->all();
        $category=Category::findOrFail($request->id);
        $child_cat=Category::getChildByParentID($request->id);
        // return $child_cat;
        if(count($child_cat)<=0){
            return response()->json(['status'=>false,'msg'=>'','data'=>null]);
        }
        else{
            return response()->json(['status'=>true,'msg'=>'','data'=>$child_cat]);
        }
    }
}
