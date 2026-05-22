<?php

namespace App\Http\Controllers;
use Auth;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Wishlist;
class WishlistController extends Controller
{
    protected $product=null;
    public function __construct(Product $product){
        $this->product=$product;
    }

    public function wishlist(Request $request){
        //echo "<pre>"; print_r($request->all()); die;
        // dd($request->all());
        if (empty($request->slug)) {
            request()->session()->flash('error','Invalid Products');
            return back();
        }        
        $product = Product::where('slug', $request->slug)->first();
        // return $product;
        if (empty($product)) {
            request()->session()->flash('error','Invalid Products');
            return back();
        }

        $already_wishlist = Wishlist::where('user_id', auth()->user()->id)->where('cart_id',null);
        if(isset($request->color_id) && $request->color_id != null){
            $already_wishlist = $already_wishlist->where('color_id', $request->color_id);
        }
        $already_wishlist = $already_wishlist->where('product_id', $product->id)->first();
        // return $already_wishlist;
        if($already_wishlist) {
            $already_wishlist->delete();
            request()->session()->flash('success','Product Removed to wishlist');
            return back();
        }else{
            $sizeData = json_decode($product->size, true);
            $price = $product->price;
            if(!isset($product->price) && $product->price == null){
                $price = $sizeData['price'][0];
            }
            $wishlist = new Wishlist;
            $wishlist->user_id = auth()->user()->id;
            $wishlist->product_id = $product->id;
            $wishlist->price = ($price-($price*$product->discount)/100);
            $wishlist->quantity = 1;
            $wishlist->amount=$wishlist->price*$wishlist->quantity;
            $wishlist->color_id=$request->color_id ?? null;
            if ($wishlist->product->stock < $wishlist->quantity || $wishlist->product->stock <= 0) return back()->with('error','Stock not sufficient!.');
            $wishlist->save();
        }
        request()->session()->flash('success','Product successfully added to wishlist');
        return back();       
    }  
    
    public function wishlistDelete(Request $request){
        $wishlist = Wishlist::find($request->id);
        if ($wishlist) {
            $wishlist->delete();
            request()->session()->flash('success','Wishlist successfully removed');
            return back();  
        }
        request()->session()->flash('error','Error please try again');
        return back();       
    }   
    
    public function check(Request $request)
    {
        $exists = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->where('color_id', $request->color_id)
            ->exists();

        return response()->json([
            'wishlisted' => $exists
        ]);
    }
}
