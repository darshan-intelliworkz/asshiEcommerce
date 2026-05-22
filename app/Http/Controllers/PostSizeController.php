<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostSize;
use Illuminate\Support\Str;

class PostSizeController extends Controller
{
    public function index()
    {
        $postSize=PostSize::orderBy('id','DESC')->paginate(10);
        return view('backend.postsize.index')->with('postSize',$postSize);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.postsize.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'title'=>'string|required',
        ]);
        $data=$request->all();
        $status=PostSize::create($data);
        if($status){
            request()->session()->flash('success','Post Size Successfully added');
        }
        else{
            request()->session()->flash('error','Please try again!!');
        }
        return redirect()->route('post-size.index');
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
        $postSize=PostSize::findOrFail($id);
        return view('backend.postsize.edit')->with('postSize',$postSize);
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
        $postTag=PostSize::findOrFail($id);
         // return $request->all();
         $this->validate($request,[
            'title'=>'string|required',
        ]);
        $data=$request->all();
        $status=$postTag->fill($data)->save();
        if($status){
            request()->session()->flash('success','Post Size Successfully updated');
        }
        else{
            request()->session()->flash('error','Please try again!!');
        }
        return redirect()->route('post-size.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $postTag=PostSize::findOrFail($id);
       
        $status=$postTag->delete();
        
        if($status){
            request()->session()->flash('success','Post Size successfully deleted');
        }
        else{
            request()->session()->flash('error','Error while deleting post tag');
        }
        return redirect()->route('post-size.index');
    }
}
