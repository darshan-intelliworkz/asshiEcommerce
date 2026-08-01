<?php

namespace App\Http\Controllers;
use App\Models\Banner;
use App\Models\Product;
use App\Models\Category;
use App\Models\PostTag;
use App\Models\PostCategory;
use App\Models\Post;
use App\Models\Cart;
use App\Models\Brand;
use App\Models\ProductReview;
use App\User;
use Auth;
use Session;
use Newsletter;
use DB;
use Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use GuzzleHttp\Client;
class FrontendController extends Controller
{
    public function index(Request $request){
        return redirect()->route($request->user()->role);
    }

    public function home(){
        $featured = Product::with('cat_info')->where('status', 'active')->where('is_featured', 1)->whereNull('deleted_at')->get()->unique('cat_id')->take(2);
        $posts=Post::where('status','active')->orderBy('id','DESC')->limit(3)->get();
        $banners=Banner::where('status','active')->limit(3)->orderBy('id','ASC')->get();
        // return $banner;
        // $products=Product::where('status','active')->orderBy('id','DESC')->where('is_featured', 0)->limit(8)->get();
        $products = Product::where('status', 'active')
        ->where('is_featured', 1)
        ->whereNull('deleted_at')
        ->whereHas('cat_info', function ($query) {
            $query->where('status', 'active');
        })
        ->whereNotNull('product_sequence')
        ->orderBy('product_sequence', 'ASC')
        ->limit(8)
        ->get();
    
        $category=Category::where('status','active')->where('is_parent',1)->orderBy('title','ASC')->get();
        // return $category;
        return view('frontend.index')
                ->with('featured',$featured)
                ->with('posts',$posts)
                ->with('banners',$banners)
                ->with('product_lists',$products)
                ->with('category_lists',$category);
    }   
    
    public function aboutUs(){
        return view('frontend.pages.about-us');
    }

    public function contact(){
        return view('frontend.pages.contact');
    }

    public function productDetail($slug){
        $product_detail= Product::getProductBySlug($slug);
        $canReview = false;
        $orderId = null;
        if (auth()->check()) {
            $cart = Cart::with('order')
                ->where('product_id', $product_detail->id)
                ->where('user_id', auth()->id())
                ->whereNotNull('order_id')
                ->first();
            if($cart){
                $orderId = $cart->order_id;
            }
            if ($cart && $cart->order && strtolower($cart->order->status) === 'delivered') {
                $canReview = true;
            }
        }
        $hasReviewed = false;
        if($orderId!= null){
            $hasReviewed = ProductReview::where(['product_id' => $product_detail->id, 'order_id' => $orderId, 'user_id' => auth()->id()])->exists();
        }
       
        return view('frontend.pages.product_detail', compact('product_detail', 'canReview', 'hasReviewed', 'orderId'));
    }

    // public function productGrids(Request $request){
    //     // $products=Product::query();
    //     // $products=$products->where('is_featured',0);
    //     // if(!empty($_GET['category'])){
    //     //     $slug=explode(',',$_GET['category']);
    //     //     // dd($slug);
    //     //     $cat_ids=Category::select('id')->whereIn('slug',$slug)->pluck('id')->toArray();
    //     //     // dd($cat_ids);
    //     //     $products->whereIn('cat_id',$cat_ids);
    //     //     // return $products;
    //     // }
    //     // if(!empty($_GET['brand'])){
    //     //     $slugs=explode(',',$_GET['brand']);
    //     //     $brand_ids=Brand::select('id')->whereIn('slug',$slugs)->pluck('id')->toArray();
    //     //     return $brand_ids;
    //     //     $products->whereIn('brand_id',$brand_ids);
    //     // }
    //     // if(!empty($_GET['sortBy'])){
    //     //     if($_GET['sortBy']=='title'){
    //     //         $products=$products->where('status','active')->orderBy('title','ASC');
    //     //     }
    //     //     if($_GET['sortBy']=='price'){
    //     //         $products=$products->orderBy('price','ASC');
    //     //     }
    //     // }

    //     // if(!empty($_GET['price'])){
    //     //     $price=explode('-',$_GET['price']);
    //     //     // return $price;
    //     //     // if(isset($price[0]) && is_numeric($price[0])) $price[0]=floor(Helper::base_amount($price[0]));
    //     //     // if(isset($price[1]) && is_numeric($price[1])) $price[1]=ceil(Helper::base_amount($price[1]));
            
    //     //     $products->whereBetween('price',$price);
    //     // }

    //     // $recent_products=Product::where('status','active')->orderBy('id','DESC')->limit(3)->get();
    //     // // Sort by number
    //     // if(!empty($_GET['show'])){
    //     //     $products=$products->where('status','active')->paginate($_GET['show']);
    //     // }
    //     // else{
    //     //     $products=$products->where('status','active')->paginate(9);
    //     // }
        
    //     // $category = Category::where('slug', $request->slug)->first();
    //     // // dd($category);
    //     // if (!$category) {
    //     //     abort(404);
    //     // }
    //     // $products = $category->products;
    //         $category = Category::where('slug', $request->slug)->first();
    //         $products = $category->products;
    //                     $recent_products = Product::where('status', 'active')
    //             ->where('is_featured', '0')
    //             ->orderBy('id', 'DESC')
    //             ->limit(3)
    //             ->get();
              
                    
    //                 return view('frontend.pages.product-grids', [
    //                 'products' => $products,
    //                 'recent_products' => $recent_products,
    //                 'category' => $category
    //             ]);
    // }
    
    
        public function productGrids(Request $request)
        {
            $slug = $request->slug ?? null;
            $category = null;

            $productsQuery = Product::query();
            $productsQuery->where('is_featured', 0)->where('status', 'active');

            if ($slug) {
                $category = Category::where('slug', $slug)->first();
                if (!$category) {
                    abort(404);
                }
                $productsQuery->where('cat_id', $category->id);
            }

            // Brand filter (supports comma separated slugs). If 'all' present, ignore brand filter.
            if ($request->has('brand') && !empty($request->brand)) {
                $slugs = explode(',', $request->brand);
                $brand_ids = Brand::select('id')->whereIn('slug', $slugs)->pluck('id')->toArray();
                if (!empty($brand_ids)) {
                    $productsQuery->whereIn('brand_id', $brand_ids);
                }
            }

            // Sort
            if ($request->has('sortBy') && !empty($request->sortBy)) {
                if ($request->sortBy == 'title') {
                    $productsQuery->orderBy('title', 'ASC');
                } elseif ($request->sortBy == 'price') {
                    $productsQuery->orderBy('price', 'ASC');
                }
            }

            // Pagination / show
            if ($request->has('show') && is_numeric($request->show)) {
                $perPage = (int) $request->show;
                $products = $productsQuery->paginate($perPage)->appends($request->query());
            } else {
                $products = $productsQuery->paginate(9)->appends($request->query());
            }

            $recent_products = Product::where('status', 'active')
                ->where('is_featured', '0')
                ->orderBy('id', 'DESC')
                ->limit(3)
                ->get();

            return view('frontend.pages.product-grids', [
                'products' => $products,
                'recent_products' => $recent_products,
                'category' => $category,
            ]);
        }

        public function productSubGrids(Request $request)
        {
            $sub_slug = $request->sub_slug;
            // dd($sub_slug);
            $category = Category::where('slug', $request->slug)->first();

            $recent_products = Product::where('status', 'active')
                ->orderBy('id', 'DESC')
                ->limit(3)
                ->get();
        
            $products = Category::getProductBySubCat($sub_slug)->sub_products;
        
            return view('frontend.pages.product-grids', [
                'products' => $products,
                'recent_products' => $recent_products,
                'sub_slug' => $sub_slug,
                'category' => $category,

            ]);
        }


   
        
        
            public function showProductList(Request $request, $slug, $sub_slug = null)
            {
                $category = Category::where('slug', $slug)->first();

                if (!$category) {
                    abort(404, 'Category not found');
                }

                // Base product query
                $productsQuery = Product::query();
                // $productsQuery->where('is_featured', 0)->where('status', 'active');
                $productsQuery->where('status', 'active');
                
                // Limit to category or subcategory
                if ($sub_slug) {
                    $subcategory = Category::where('slug', $sub_slug)
                                    ->where('parent_id', $category->id)
                                    ->where('is_parent', 0)
                                    ->first();
                    if (!$subcategory) {
                        abort(404, 'Subcategory not found');
                    }
                    $productsQuery->where('child_cat_id', $subcategory->id);
                } else {
                    $productsQuery->where('cat_id', $category->id);
                }

                // Filters: brand (comma slugs), sortBy, price range
                if ($request->has('brand') && !empty($request->brand)) {
                    $slugs = explode(',', $request->brand);
                    $brand_ids = Brand::select('id')->whereIn('slug', $slugs)->pluck('id')->toArray();
                    if (!empty($brand_ids)) {
                        $productsQuery->whereIn('brand_id', $brand_ids);
                    }
                }

                if ($request->has('sortBy') && !empty($request->sortBy)) {
                    if ($request->sortBy == 'title') {
                        $productsQuery->orderBy('title', 'ASC');
                    } elseif ($request->sortBy == 'price') {
                        $productsQuery->orderBy('price', 'ASC');
                    }
                }

                if ($request->has('price') && !empty($request->price)) {
                    $priceRange = explode('-', $request->price);
                    $minPrice = (int) ($priceRange[0] ?? 0);
                    $maxPrice = (int) ($priceRange[1] ?? 0);

                    if ($minPrice || $maxPrice) {
                        $productsQuery->where(function ($q) use ($minPrice, $maxPrice) {
                            $q->whereRaw('JSON_VALID(`size`) = 1')
                                ->whereRaw(
                                    'COALESCE(JSON_UNQUOTE(JSON_EXTRACT(`size`, "$.price[0]")), 0) BETWEEN ? AND ?',
                                    [$minPrice, $maxPrice]
                                );
                        });
                    }
                }

                // Recent products
                $recent_products = Product::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();

                // Pagination / show
                    if ($request->has('show') && is_numeric($request->show)) {
                        $perPage = (int) $request->show;
                        $products = $productsQuery->paginate($perPage)->appends($request->query());
                    } else {
                        $products = $productsQuery->paginate(9)->appends($request->query());
                    }

                return view('frontend.pages.product-lists', compact('products', 'category', 'sub_slug', 'recent_products'));
            }



    public function productLists(){
      
        // $products=Product::query();
        // $products=$products->where('is_featured',0);
        // if(!empty($_GET['category'])){
        //     $slug=explode(',',$_GET['category']);
        //     $cat_ids=Category::select('id')->whereIn('slug',$slug)->pluck('id')->toArray();
        //     $products->whereIn('cat_id',$cat_ids)->paginate;
        // }
        // if(!empty($_GET['brand'])){
        //     $slugs=explode(',',$_GET['brand']);
        //     $brand_ids=Brand::select('id')->whereIn('slug',$slugs)->pluck('id')->toArray();
        //     return $brand_ids;
        //     $products->whereIn('brand_id',$brand_ids);
        // }
        // if(!empty($_GET['sortBy'])){
        //     if($_GET['sortBy']=='title'){
        //         $products=$products->where('status','active')->orderBy('title','ASC');
        //     }
        //     if($_GET['sortBy']=='price'){
        //         $products=$products->orderBy('price','ASC');
        //     }
        // }
        // if(!empty($_GET['price'])){
        //     $priceRange=explode('-',$_GET['price']);
        //     $minPrice = (int)$priceRange[0];
        //     $maxPrice = (int)$priceRange[1];
        //     $products->whereJsonContains('size->price', $minPrice)
        //      ->orWhereJsonContains('size->price', $maxPrice);
        // }
        // $recent_products=Product::where('status','active')->orderBy('id','DESC')->limit(3)->get();
        //  if(!empty($_GET['show'])){
        //     $products=$products->where('status','active')->paginate($_GET['show']);
        // }
        // else{
        //     $products=$products->where('status','active')->toSql();
        //  }
        \DB::enableQueryLog();
        $products = Product::query();
        // $products = $products->where('is_featured', 0);
        $products = $products->where('status', 'active');

        if (!empty($_GET['category'])) {
            $slug = explode(',', $_GET['category']);
            $cat_ids = Category::select('id')->whereIn('slug', $slug)->pluck('id')->toArray();
            $products->whereIn('cat_id', $cat_ids);
        }

        if (!empty($_GET['brand'])) {
            $slugs = explode(',', $_GET['brand']);
            $brand_ids = Brand::select('id')->whereIn('slug', $slugs)->pluck('id')->toArray();
            $products->whereIn('brand_id', $brand_ids);
        }

        if (!empty($_GET['sortBy'])) {
            if ($_GET['sortBy'] == 'title') {
                $products = $products->where('status', 'active')->orderBy('title', 'ASC');
            } elseif ($_GET['sortBy'] == 'price') {
                $products = $products->orderBy('price', 'ASC');
            }
        }

        if (!empty($_GET['price'])) {
            $priceRange = explode('-', $_GET['price']);
            $minPrice = (int) $priceRange[0];
            $maxPrice = (int) $priceRange[1];

            $products->where(function ($query) use ($minPrice, $maxPrice) {
                $query->whereRaw('JSON_VALID(`size`) = 1')
                    ->whereRaw(
                        'COALESCE(JSON_UNQUOTE(JSON_EXTRACT(`size`, \'$.price[0]\')), 0) BETWEEN ? AND ?',
                        [$minPrice, $maxPrice]
                    );
            });
        }

            
        

        $recent_products = Product::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();

        $show = request()->get('show');
        if (!empty($show) && is_numeric($show)) {
            $perPage = (int) $show;
            $products = $products->where('status', 'active')->paginate($perPage)->appends(request()->query());
        } else {
            $products = $products->where('status', 'active')->paginate(9)->appends(request()->query());
        }
        // dd(\DB::getQueryLog());
//   dd($products);

        return view('frontend.pages.product-lists')->with('products',$products)->with('recent_products',$recent_products);
    }
    public function productFilter(Request $request){
            $data= $request->all();
            // return $data;
            $showURL="";
            if(!empty($data['show'])){
                $showURL .='&show='.$data['show'];
            }

            $sortByURL='';
            if(!empty($data['sortBy'])){
                $sortByURL .='&sortBy='.$data['sortBy'];
            }

            $catURL="";
            if(!empty($data['category'])){
                foreach($data['category'] as $category){
                    if(empty($catURL)){
                        $catURL .='&category='.$category;
                    }
                    else{
                        $catURL .=','.$category;
                    }
                }
            }

            $brandURL="";
            if(!empty($data['brand'])){
                foreach($data['brand'] as $brand){
                    if(empty($brandURL)){
                        $brandURL .='&brand='.$brand;
                    }
                    else{
                        $brandURL .=','.$brand;
                    }
                }
            }
            // return $brandURL;

            $priceRangeURL="";
            if(!empty($data['price_range'])){
                $priceRangeURL .='&price='.$data['price_range'];
            }
            if(request()->is('e-shop.loc/product-grids')){
                return redirect()->route('product-grids',$catURL.$brandURL.$priceRangeURL.$showURL.$sortByURL);
            }
            else{
                return redirect()->route('product-lists',$catURL.$brandURL.$priceRangeURL.$showURL.$sortByURL);
            }
    }
    
    public function productSearch(Request $request)
    {
        $search = $request->search;
        // dd($search);
        $recent_products = Product::where('status', 'active')
            ->orderBy('id', 'DESC')
            ->limit(3)
            ->get();

        $query = Product::where('status', 'active')
            ->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('price', 'like', "%{$search}%");
            })
            ->orderBy('id', 'DESC');

        $show = request()->get('show');
        if (!empty($show) && is_numeric($show)) {
            $perPage = (int) $show;
        } else {
            $perPage = 9;
        }

        $products = $query->paginate($perPage)->appends(request()->query());

        return view('frontend.pages.product-lists', compact('products', 'recent_products'));
    }

    public function productBrand(Request $request){
        // Redirect to the unified product list route with the brand slug in the query string
        $params = request()->query();
        // set/replace brand param (support multiple comma-separated brands if needed)
        $params['brand'] = isset($params['brand']) && $params['brand'] ? $params['brand'] . ',' . $request->slug : $request->slug;

        $url = route('product-lists') . (count($params) ? '?' . http_build_query($params) : '');
        return redirect($url);
    }
    
    public function productCat(Request $request) {
        // Redirect to the unified product list route so GET filters are handled centrally
        $query = request()->getQueryString();
        $url = route('productlist', $request->slug) . ($query ? '?' . $query : '');
        return redirect($url);
    }
        //  public function productCat(Request $request) {
        //     $category = Category::where('slug', $request->slug)->first();
        //     $products = $category->products;
        //                 $recent_products = Product::where('status', 'active')
        //         ->where('is_featured', '0')
        //         ->orderBy('id', 'DESC')
        //         ->limit(3)
        //         ->get();
        
        //     if (request()->is('e-shop.loc/product-grids')) {
        //         return view('frontend.pages.product-grids', [
        //             'products' => $products,
        //             'recent_products' => $recent_products,
        //             'category' => $category
        //         ]);
        //     } else {
        //         return view('frontend.pages.product-lists', [
        //             'products' => $products,
        //             'recent_products' => $recent_products,
        //             'category' => $category
        //         ]);
        //     }
        // }

        // public function productSubCat(Request $request)
        // {
        //     $sub_slug = $request->sub_slug;
        
        //     $products = Category::getProductBySubCat($sub_slug); 
        
        //     $recent_products = Product::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();
        
        //     // Check the current route and return appropriate view
        //     if (request()->is('e-shop.loc/product-grids')) {
        //         return view('frontend.pages.product-grids', [
        //             'products' => $products->sub_products,  // Assuming sub_products is available
        //             'recent_products' => $recent_products,
        //             'sub_slug' => $sub_slug  // Pass sub_slug to the view
        //         ]);
        //     } else {
        //         return view('frontend.pages.product-lists', [
        //             'products' => $products->sub_products,  // Assuming sub_products is available
        //             'recent_products' => $recent_products,
        //             'sub_slug' => $sub_slug  // Pass sub_slug to the view
        //         ]);
        //     }
        // }
        
        public function productSubCat(Request $request)
        {
            // Redirect to unified product list route for subcategory to preserve GET filters
            $query = request()->getQueryString();
            $url = route('productlist-with-sub', ['slug' => $request->slug, 'sub_slug' => $request->sub_slug]) . ($query ? '?' . $query : '');
            return redirect($url);
        }
        



    public function blog(){
        $post=Post::query();
        
        if(!empty($_GET['category'])){
            $slug=explode(',',$_GET['category']);
            // dd($slug);
            $cat_ids=PostCategory::select('id')->whereIn('slug',$slug)->pluck('id')->toArray();
            return $cat_ids;
            $post->whereIn('post_cat_id',$cat_ids);
            // return $post;
        }
        if(!empty($_GET['tag'])){
            $slug=explode(',',$_GET['tag']);
            // dd($slug);
            $tag_ids=PostTag::select('id')->whereIn('slug',$slug)->pluck('id')->toArray();
            // return $tag_ids;
            $post->where('post_tag_id',$tag_ids);
            // return $post;
        }

        if(!empty($_GET['show'])){
            $post=$post->where('status','active')->orderBy('id','DESC')->paginate($_GET['show']);
        }
        else{
            $post=$post->where('status','active')->orderBy('id','DESC')->paginate(9);
        }
        // $post=Post::where('status','active')->paginate(8);
        $rcnt_post=Post::where('status','active')->orderBy('id','DESC')->limit(3)->get();
        return view('frontend.pages.blog')->with('posts',$post)->with('recent_posts',$rcnt_post);
    }

    public function blogDetail($slug){
        $post=Post::getPostBySlug($slug);
        $rcnt_post=Post::where('status','active')->orderBy('id','DESC')->limit(3)->get();
        // return $post;
        return view('frontend.pages.blog-detail')->with('post',$post)->with('recent_posts',$rcnt_post);
    }

    public function blogSearch(Request $request){
        // return $request->all();
        $rcnt_post=Post::where('status','active')->orderBy('id','DESC')->limit(3)->get();
        $posts=Post::orwhere('title','like','%'.$request->search.'%')
            ->orwhere('quote','like','%'.$request->search.'%')
            ->orwhere('summary','like','%'.$request->search.'%')
            ->orwhere('description','like','%'.$request->search.'%')
            ->orwhere('slug','like','%'.$request->search.'%')
            ->orderBy('id','DESC')
            ->paginate(8);
        return view('frontend.pages.blog')->with('posts',$posts)->with('recent_posts',$rcnt_post);
    }

    public function blogFilter(Request $request){
        $data=$request->all();
        // return $data;
        $catURL="";
        if(!empty($data['category'])){
            foreach($data['category'] as $category){
                if(empty($catURL)){
                    $catURL .='&category='.$category;
                }
                else{
                    $catURL .=','.$category;
                }
            }
        }

        $tagURL="";
        if(!empty($data['tag'])){
            foreach($data['tag'] as $tag){
                if(empty($tagURL)){
                    $tagURL .='&tag='.$tag;
                }
                else{
                    $tagURL .=','.$tag;
                }
            }
        }
        // return $tagURL;
            // return $catURL;
        return redirect()->route('blog',$catURL.$tagURL);
    }

    public function blogByCategory(Request $request){
        $post=PostCategory::getBlogByCategory($request->slug);
        $rcnt_post=Post::where('status','active')->orderBy('id','DESC')->limit(3)->get();
        return view('frontend.pages.blog')->with('posts',$post->post)->with('recent_posts',$rcnt_post);
    }

    public function blogByTag(Request $request){
        // dd($request->slug);
        $post=Post::getBlogByTag($request->slug);
        // return $post;
        $rcnt_post=Post::where('status','active')->orderBy('id','DESC')->limit(3)->get();
        return view('frontend.pages.blog')->with('posts',$post)->with('recent_posts',$rcnt_post);
    }

    // Login
    public function login(){
        return view('frontend.pages.login');
    }
    public function loginSubmit(Request $request)
    {
        // $data= $request->all();
        // if(Auth::attempt(['email' => $data['email'], 'password' => $data['password'],'status'=>'active', 'role' => 'user'])){
        //     Session::put('user',$data['email']);
        //     request()->session()->flash('success','Successfully login');
        //     return redirect()->route('home');
        // }
        // else{ 
        //     request()->session()->flash('error','Invalid email and password pleas try again!');
        //     return redirect()->route('login.form');
        // }
        $credentials = $request->only('email','password');
        // Check if credentials exist with 'user' role
        $credentials['role'] = 'user';
        $credentials['status'] = 'active';
    
        if (Auth::check()) {
            // Already logged in
            if (Auth::user()->role !== 'user') {
                Auth::logout(); // logout previous role
            }
        }
    
        if (Auth::attempt($credentials)) {
            Session::regenerate();
            $data= $request->all();
            Session::put('user',$data['email']);
            session()->flash('success','Login successful');
            return redirect()->route('home');
        }
    
        return redirect()->route('login.form')->with('error','Invalid credentials');
    }

    public function adminLogin(){
        return view('backend.login');
    }
    public function adminLoginSubmit(Request $request)
    {
        // $data= $request->all();
        // if(Auth::attempt(['email' => $data['email'], 'password' => $data['password'], 'status'=>'active', 'role' => 'admin'])){
        //     Session::put('user',$data['email']);
        //     request()->session()->flash('success','Successfully login');
        //     return redirect()->route('admin');
        // }
        // else{
        //     return redirect()->route('admin.login')->with('error','Invalid email and password pleas try again!');
        // }
        
        $credentials = $request->only('email','password');
        $credentials['role'] = 'admin';
        $credentials['status'] = 'active';
    
        if (Auth::check()) {
            if (Auth::user()->role !== 'admin') {
                Auth::logout(); // logout previous role
            }
        }
    
        if (Auth::attempt($credentials)) {
            Session::regenerate();
            $data= $request->all();
            Session::put('user',$data['email']);
            session()->flash('success','Admin login successful');
            return redirect()->route('admin');
        }
    
        return redirect()->route('admin.login')->with('error','Invalid credentials');
    }


    public function logout(){
        Session::forget('user');
        if(Auth::user()->role=='admin'){
            Auth::logout();
            request()->session()->flash('success','Logout successfully');
            return redirect()->route('admin.login');
        }else{
            Auth::logout();
            request()->session()->flash('success','Logout successfully');
            return back();
        }
    }

    public function register(){
        return view('frontend.pages.register');
    }
    public function registerSubmit(Request $request){
        // return $request->all();
        $registeredMessage = 'This email is already registered. Please login or use another email.';
        if (User::where('email', $request->email)->exists()) {
            return redirect()->route('register.form')
                ->withErrors(['email' => $registeredMessage])
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', $registeredMessage);
        }

        $validator = Validator::make($request->all(), [
            'name'=>'string|required|min:2',
            'email'=>'string|required|email|unique:users,email',
            'password'=>'required|min:6|confirmed',
        ], [
            'email.unique' => $registeredMessage,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data=$request->all();
        //$check=$this->create($data);
        $check = User::create([
            'name'=>$data['name'],
            'email'=>$data['email'],
            'password'=>Hash::make($data['password']),
            'status'=>'active'
            ]);

        Session::put('user',$data['email']);
        if($check){
            request()->session()->flash('success','Successfully registered');
            return redirect()->route('home');
        }
        else{
            request()->session()->flash('error','Please try again!');
            return back();
        }
    }
    
    // Reset password
    public function showResetForm(){
        return view('auth.passwords.old-reset');
    }

    public function subscribe(Request $request){
        if(! Newsletter::isSubscribed($request->email)){
                Newsletter::subscribePending($request->email);
                if(Newsletter::lastActionSucceeded()){
                    request()->session()->flash('success','Subscribed! Please check your email');
                    return redirect()->route('home');
                }
                else{
                    Newsletter::getLastError();
                    return back()->with('error','Something went wrong! please try again');
                }
            }
            else{
                request()->session()->flash('error','Already Subscribed');
                return back();
            }
    }
    
}
