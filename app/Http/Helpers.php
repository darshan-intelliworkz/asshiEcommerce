<?php
use App\Models\Message;
use App\Models\Category;
use App\Models\PostTag;
use App\Models\PostCategory;
use App\Models\Order;
use App\Models\Wishlist;
use App\Models\Shipping;
use App\Models\Cart;
use App\Models\Color;
use App\Models\Product;
// use Auth; 
class Helper{
    public static function messageList()
    {
        return Message::whereNull('read_at')->orderBy('created_at', 'desc')->get();
    } 
    public static function getAllCategory(){
        $category=new Category();
        $menu=$category->getAllParentWithChild();
        return $menu;
    } 
    
    public static function getHeaderCategory(){
        $category = new Category();
        // dd($category);
        $menu=$category->getAllParentWithChild();

        if($menu){
            ?>
            
            <li>
            <a href="javascript:void(0);">Category<i class="ti-angle-down"></i></a>
                <ul class="dropdown border-0 shadow">
                <?php
                    foreach($menu as $cat_info){
                        if($cat_info->child_cat->count()>0){
                            ?>
                            <li><a href="<?php echo route('product-cat',$cat_info->slug); ?>"><?php echo $cat_info->title; ?></a>
                                <ul class="dropdown sub-dropdown border-0 shadow">
                                    <?php
                                    foreach($cat_info->child_cat as $sub_menu){
                                        ?>
                                        <li><a href="<?php echo route('product-sub-cat',[$cat_info->slug,$sub_menu->slug]); ?>"><?php echo $sub_menu->title; ?></a></li>
                                        <?php
                                    }
                                    ?>
                                </ul>
                            </li>
                            <?php
                        }
                        else{
                            ?>
                                <li><a href="<?php echo route('product-cat',$cat_info->slug);?>"><?php echo $cat_info->title; ?></a></li>
                            <?php
                        }
                    }
                    ?>
                </ul>
            </li>
        <?php
        }
    }

    public static function productCategoryList($option='all'){
        if($option='all'){
            return Category::orderBy('id','DESC')->get();
        }
        return Category::has('products')->orderBy('id','DESC')->get();
    }

    public static function postTagList($option='all'){
        if($option='all'){
            return PostTag::orderBy('id','desc')->get();
        }
        return PostTag::has('posts')->orderBy('id','desc')->get();
    }

    public static function postCategoryList($option="all"){
        if($option='all'){
            return PostCategory::orderBy('id','DESC')->get();
        }
        return PostCategory::has('posts')->orderBy('id','DESC')->get();
    }
    // Cart Count
    public static function cartCount($user_id=''){
       
        if(Auth::check()){
            if($user_id=="") $user_id=auth()->user()->id;
            return Cart::where('user_id',$user_id)->where('order_id',null)->sum('quantity');
        }
        else{
            return 0;
        }
    }
    // relationship cart with product
    public function product(){
        return $this->hasOne('App\Models\Product','id','product_id');
    }
 
    public static function getAllProductFromCart($user_id=''){
        if(Auth::check()){
            if($user_id=="") $user_id=auth()->user()->id;
            $cart = Cart::with('product')->where('user_id',$user_id)->where('order_id',null)->get();
            if(isset($cart) && is_countable($cart) && count($cart) > 0){
                foreach($cart as $k => $v){
                    $images = [];
                    if(isset($v->color_id) && $v->color_id != null){
                        $color = Color::find($v->color_id);
                        $images = $color->images->pluck('image')->map(function($image) {
                            return asset('public/storage/products/'.$image);
                        });
                    }
                    if(isset($images) && is_countable($images) && count($images)){
                        $v->color_img = $images[0] ?? null;
                    }
                    
                    $sizeData = json_decode($v->product->size, true);
                    if(isset($v->price) && $v->price != null)
                        $v->price = $v->price;
                    else
                        $v->price = $sizeData['price'][0];
                    
                    $afterDiscount = 0;
                    if(isset($v->product->discount) && $v->product->discount > 0){
                        $sizes = json_decode($v->product->size);
                        $priceArr = $sizes->price;
                        $productPrice = 0;
                        foreach($priceArr as $key => $val){
                            $productPrice = $val;
                        }
                        $afterDiscount = ($productPrice-($productPrice*$v->product->discount)/100);
                    }
                    $v->after_discount = $afterDiscount;
                }
            }
            
            return $cart;
        }
        else{
            return collect();
        }
    }
    // Total amount cart
    public static function totalCartPrice($user_id=''){
        if(Auth::check()){
            if($user_id=="") $user_id=auth()->user()->id;
            return Cart::where('user_id',$user_id)->where('order_id',null)->sum('amount');
        }
        else{
            return 0;
        }
    }
    
    public static function totalGstPrice($user_id=''){
        if(Auth::check()){
            if($user_id=="") $user_id=auth()->user()->id;
            $cart = Cart::where('user_id',$user_id)->where('order_id',null)->get();
            $totalGstAmount = 0;
            if(isset($cart) && is_countable($cart) && count($cart) > 0){
                foreach($cart as $key => $val){
                    $product = Product::with(['cat_info:id,gst'])->select('id', 'cat_id' , 'gst_percent')->where('id', $val->product_id)->first();
                    $gstPercent = $gstAmt = 0;
                    if($product){
                        if($product->gst_percent){
                            $gstPercent = $product->gst_percent ?? 0;
                        }else{
                            $gstPercent = $product->cat_info->gst ?? 0;
                        }
                    }
                    if($gstPercent != 0){
                        $totalGstAmount += ($val->amount * $gstPercent) / 100;
                        $gstAmt += ($val->amount * $gstPercent) / 100;
                    }
                    $val->gst_percent = $gstPercent;
                    $val->gst_amt = $gstAmt;
                    $val->save();
                }
            }
            return $totalGstAmount;
        }
        else{
            return 0;
        }
    }
    
    // Wishlist Count
    public static function wishlistCount($user_id=''){
       
        if(Auth::check()){
            if($user_id=="") $user_id=auth()->user()->id;
            return Wishlist::where('user_id',$user_id)->where('cart_id',null)->sum('quantity');
        }
        else{
            return 0;
        }
    }
    public static function getAllProductFromWishlist($user_id=''){
        if(Auth::check()){
            if($user_id=="") $user_id=auth()->user()->id;
            $wishlist = Wishlist::with('product')->where('user_id',$user_id)->where('cart_id',null)->get();
            if(isset($wishlist) && is_countable($wishlist) && count($wishlist) > 0){
                foreach($wishlist as $k => $v){
                    $images = [];
                    if(isset($v->color_id) && $v->color_id != null){
                        $color = Color::find($v->color_id);
                        $images = $color->images->pluck('image')->map(function($image) {
                            return asset('public/storage/products/'.$image);
                        });
                    }
                    if(isset($images) && is_countable($images) && count($images)){
                        $v->color_img = $images[0] ?? null;
                    }
                    
                    $sizeData = json_decode($v->product->size, true);
                    if(isset($v->product->price) && $v->product->price != null)
                        $v->price = $v->product->price;
                    else
                        $v->price = $sizeData['price'][0];
                    
                    $afterDiscount = 0;
                    if(isset($v->product->discount) && $v->product->discount > 0){
                        $sizes = json_decode($v->product->size);
                        $priceArr = $sizes->price;
                        $productPrice = 0;
                        foreach($priceArr as $key => $val){
                            $productPrice = $val;
                        }
                        $afterDiscount = ($productPrice-($productPrice*$v->product->discount)/100);
                    }
                    $v->after_discount = $afterDiscount;
                }
            }
            return $wishlist;
        }
        else{
            return 0;
        }
    }
    public static function totalWishlistPrice($user_id=''){
        if(Auth::check()){
            if($user_id=="") $user_id=auth()->user()->id;
            return Wishlist::where('user_id',$user_id)->where('cart_id',null)->sum('amount');
        }
        else{
            return 0;
        }
    }

    // Total price with shipping and coupon
    public static function grandPrice($id,$user_id){
        $order=Order::find($id);
        dd($id);
        if($order){
            $shipping_price=(float)$order->shipping->price;
            $order_price=self::orderPrice($id,$user_id);
            return number_format((float)($order_price+$shipping_price),2,'.','');
        }else{
            return 0;
        }
    }


    // Admin home
    public static function earningPerMonth(){
        $month_data=Order::where('status','delivered')->get();
        // return $month_data;
        $price=0;
        foreach($month_data as $data){
            $price = $data->cart_info->sum('price');
        }
        return number_format((float)($price),2,'.','');
    }

    public static function shipping(){
        return Shipping::orderBy('id','DESC')->get();
    }
}

?>