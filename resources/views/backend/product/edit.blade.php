@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">Edit Product</h5>
    <div class="card-body">
      <form method="post" action="{{route('product.update',$product->id)}}" enctype="multipart/form-data">
        @csrf 
        @method('PATCH')
        <div class="form-group">
          <label for="inputProductCode" class="col-form-label">Product Code <span class="text-danger">*</span></label>
          <input id="inputProductCode" type="text" name="product_code" placeholder="Enter product code"  value="{{ old('product_code', $product->product_code)}}" class="form-control">
          @error('product_code')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="title" class="col-form-label">Title <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $product->title)}}">
          @error('title')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="description" class="col-form-label">Description</label>
          <textarea class="form-control" id="description" name="description">{{ old('description', $product->description) }}</textarea>
          @error('description')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="product_features" class="col-form-label">Product Features</label>
          <textarea class="form-control" id="product_features" name="product_features">{{ old('product_features', $product->product_features) }}</textarea>
          @error('product_features')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="size_chart" class="col-form-label">Size Chart</label>
          <textarea class="form-control" id="size_chart" name="size_chart">{{ old('size_chart', $product->size_chart) }}</textarea>
          @error('size_chart')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="is_featured">Is Featured</label><br>
          <input type="checkbox" name='is_featured' id='is_featured' value="1" {{(($product->is_featured) ? 'checked' : '')}}> Yes                        
        </div>
              {{-- {{$categories}} --}}

        <div class="form-group">
          <label for="cat_id">Category <span class="text-danger">*</span></label>
          <select name="cat_id" id="cat_id" class="form-control">
              <option value="">--Select any category--</option>
              @foreach($categories as $key=>$cat_data)
                  <option value='{{$cat_data->id}}' {{(($product->cat_id==$cat_data->id)? 'selected' : '')}}>{{$cat_data->title}}</option>
              @endforeach
          </select>
        </div>
        @php 
          $sub_cat_info=DB::table('categories')->select('title')->where('id',$product->child_cat_id)->get();
        // dd($sub_cat_info);

        @endphp
        {{-- {{$product->child_cat_id}} --}}
        <div class="form-group {{(($product->child_cat_id)? '' : 'd-none')}}" id="child_cat_div">
          <label for="child_cat_id">Sub Category</label>
          <select name="child_cat_id" id="child_cat_id" class="form-control">
              <option value="">--Select any sub category--</option>
              
          </select>
        </div>
        
        <div class="form-group">
          <label for="hsn_code" class="col-form-label">HSN Code</label>
          <input id="hsn_code" type="text" name="hsn_code" placeholder="Enter HSN code"  value="{{$product->hsn_code}}" class="form-control">
          @error('hsn_code')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        
        <div class="form-group">
          <label for="discount" class="col-form-label">Discount(%)</label>
          <input id="discount" type="number" name="discount" min="0" max="100" placeholder="Enter discount"  value="{{$product->discount}}" class="form-control">
          @error('discount')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="gst_percent" class="col-form-label">GST Rate(%)</label>
          <input id="gst_percent" type="number" name="gst_percent" min="0" max="100" placeholder="Enter GST percent"  value="{{$product->gst_percent}}" class="form-control">
          @error('gst_percent')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="brand_id">Brand</label>
          <select name="brand_id" class="form-control">
              <option value="">--Select Brand--</option>
             @foreach($brands as $brand)
              <option value="{{$brand->id}}" {{(($product->brand_id==$brand->id)? 'selected':'')}}>{{$brand->title}}</option>
             @endforeach
          </select>
        </div>

        <!--<div class="form-group">-->
        <!--  <label for="condition">Condition</label>-->
        <!--  <select name="condition" class="form-control">-->
        <!--      <option value="">--Select Condition--</option>-->
        <!--      <option value="default" {{(($product->condition=='default')? 'selected':'')}}>Default</option>-->
        <!--      <option value="new" {{(($product->condition=='new')? 'selected':'')}}>New</option>-->
        <!--      <option value="hot" {{(($product->condition=='hot')? 'selected':'')}}>Hot</option>-->
        <!--  </select>-->
        <!--</div>-->

        <div class="form-group">
          <label for="stock">Quantity <span class="text-danger">*</span></label>
          <input id="quantity" type="number" name="stock" min="0" placeholder="Enter quantity"  value="{{$product->stock}}" class="form-control">
          @error('stock')
            <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        <div class="form-group">
          <label for="inputPhoto" class="col-form-label">Photo <span class="text-danger">*</span></label>
          <input id="inputFile" type="file" name="photo[]" class="form-control" multiple>
          <div id="holder" style="margin-top:15px;max-height:100px;"></div>
          @error('photo')
            <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
            <label for="inputVideo" class="col-form-label">
                Video
            </label>
            <input id="inputVideo" type="file" name="video" class="form-control" accept="video/*">
            <!-- Preview new selected video -->
            <div id="video-holder" style="margin-top:15px;max-height:100px;"></div>
            <!-- Show existing video (edit mode) -->
            @if(!empty($product->video))
                <div style="margin-top:15px;">
                    <video width="150" height="100" controls>
                        <source src="{{ asset('public/product_videos/'.$product->video) }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            @endif
            @error('video')
            <span class="text-danger">{{$message}}</span>
            @enderror
        </div>
        
        <div class="form-group">
          <label for="status" class="col-form-label">Status <span class="text-danger">*</span></label>
          <select name="status" class="form-control">
            <option value="active" {{(($product->status=='active')? 'selected' : '')}}>Active</option>
            <option value="inactive" {{(($product->status=='inactive')? 'selected' : '')}}>Inactive</option>
        </select>
          @error('status')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div id="productSizePrice" class="row mb-3">
            <div class="col-md-11">
              <hr>
              <h6 class="m-0 fw-bold mb-4">Product Size & Price</h6>
            </div>
            
            <div class="col-md-1">
                <label></label>
                <button type="button" class="addfieldSizePrice  btn btn-primary">
                    <svg height="30px" id="Layer_1" version="1.1" viewBox="0 0 50 50" width="30px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <rect fill="none" height="50" width="50"></rect>
                        <line fill="none" stroke="#ffffff" stroke-miterlimit="10" stroke-width="4" x1="9" x2="41" y1="25" y2="25"></line>
                        <line fill="none" stroke="#ffffff" stroke-miterlimit="10" stroke-width="4" x1="25" x2="25" y1="9" y2="41"></line>
                    </svg>
                </button>
            </div>
        </div>
        @if($errors->has('size') || $errors->has('size.*') || $errors->has('price') || $errors->has('price.*'))
            <span class="text-danger">
                {{ 
                    $errors->first('size')
                    ?? $errors->first('size.*')
                }}
                {{
                    $errors->first('price')
                    ?? $errors->first('price.*')
                }}
            </span>
        @endif
          @php
              $sizeData = json_decode($product->size, true);
          @endphp
          @if($sizeData)
            @foreach($sizeData['size'] as $key => $size)
            <div class="row mb-3 size-price-row">
                <div class="col-md-5 size-price-row">
                    <!--<div class="form-group">-->
                        <label for="size">Size</label>
                        <select name="size[]" class="form-control">
                            <option value="">--Select Size--</option>
                            @foreach($postSize as $val)
                                <option value="{{ $val->title }}" {{ $val->title == $size ? 'selected' : '' }}>
                                    {{ $val->title }}
                                </option>
                            @endforeach
                        </select>
                    <!--</div>-->
                </div>

                <div class="col-md-5">
                    <!--<div class="form-group">-->
                        <label for="price" class="col-form-label">Price</label>
                        <input id="price" type="number" name="price[]" placeholder="Enter price" value="{{ $sizeData['price'][$key] }}" class="form-control">
                    <!--</div>-->
                </div>

                <div class="col-md-2">
                    <label></label>
                    <button type="button" class="removefieldSizePrice btn btn-danger">
                        <svg height="30px" id="Layer_1" version="1.1" viewBox="0 0 50 50" width="30px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <rect fill="none" height="50" width="50"></rect>
                            <line fill="none" stroke="#ffffff" stroke-miterlimit="10" stroke-width="4" x1="9" x2="41" y1="25" y2="25"></line>
                        </svg>
                    </button>
                </div>
            </div>
            @endforeach
        @endif
        <div id="dynamic-fields-SizePrice"></div>

        <div id="productcolorimage" class="row mb-5">
            <div class="col-md-11">
            <hr>
              <h6 class="m-0 fw-bold mb-4">Product Color & Image</h6>
            </div>
            <div class="col-md-1">
                <label></label>
                <button type="button" class="addfieldimagecolor  btn btn-primary delete-color">
                    <svg height="30px" id="Layer_1" version="1.1" viewBox="0 0 50 50" width="30px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <rect fill="none" height="50" width="50"></rect>
                        <line fill="none" stroke="#ffffff" stroke-miterlimit="10" stroke-width="4" x1="9" x2="41" y1="25" y2="25"></line>
                        <line fill="none" stroke="#ffffff" stroke-miterlimit="10" stroke-width="4" x1="25" x2="25" y1="9" y2="41"></line>
                    </svg>
                </button>
            </div>
            @foreach($product->color as $index => $color)
              <input type="hidden" name="color_id[]" value="{{ $color->id }}">
              <div class="row color-group" data-color-index="{{ $index }}">
                <div class="col-md-4">
                    <label class="form-label">Color Name</label>
                    <input type="text" id="color_name" name="color_name[]" value="{{ $color->color_name }}" placeholder="Enter Color Name" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Color Code</label>
                    <input type="color" id="color_code" name="color_code[]" value="{{ $color->color_code }}" placeholder="Enter Color Code" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Product color Images</label>
                    <input type="file" id="color_images" name="color_images[{{ $index }}][]" class="form-control" multiple>
                </div>
                <div class="col-md-1">
                    <label></label>
                    <button type="button" class="removeimagecolorfield btn btn-danger delete-color" data-color-id="{{ $color->id }}">
                        <svg height="30px" id="Layer_1" version="1.1" viewBox="0 0 50 50" width="30px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <rect fill="none" height="50" width="50"></rect>
                            <line fill="none" stroke="#ffffff" stroke-miterlimit="10" stroke-width="4" x1="9" x2="41" y1="25" y2="25"></line>
                        </svg>
                    </button>
                </div>
                <div class="col-md-12">
                  <label>Current Images</label>
                  <div class="image-preview">
                      @foreach($color->images as $image)
                          <img src="{{ asset('public/storage/products/'.$image->image) }}" width="100" alt="{{ $color->color_name }}">
                          <input type="checkbox" name="delete_images[]" value="{{ $image->id }}"> Delete
                      @endforeach
                  </div>
                </div>
              </div>
            @endforeach
            <input type="hidden" id="deleted-colors" name="deleted_colors" value="">
        </div>
        <div id="dynamic-fields-imagecolor"></div>
        <div class="form-group mb-3">
           <button class="btn btn-success" type="submit">Update</button>
        </div>
      </form>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{asset('public/backend/summernote/summernote.min.css')}}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css" />

@endpush
@push('scripts')
<script src="{{asset('public/backend/summernote/summernote.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>

<script>
    $(document).ready(function() {
    // Max field limit
    var max_fields = 10;
    var colorCount = 1;

    // Click event for adding new fields
    $('.addfieldSizePrice').on('click', function() {
        if (colorCount < max_fields) {
            var newField = `
            <div class="row mb-5 size-price-row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="size">Size</label>
                        <select name="size[]" class="form-control">
                            <option value="">--Select Size--</option>
                            @foreach($postSize as $val)
                                <option value="{{ $val->title }}">{{ $val->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="price" class="col-form-label">Price</label>
                        <input id="price" type="number" name="price[]" placeholder="Enter price" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <label></label>
                    <button type="button" class="removefieldSizePrice btn btn-danger">
                        <svg height="30px" id="Layer_1" version="1.1" viewBox="0 0 50 50" width="30px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <rect fill="none" height="50" width="50"></rect>
                            <line fill="none" stroke="#ffffff" stroke-miterlimit="10" stroke-width="4" x1="9" x2="41" y1="25" y2="25"></line>
                        </svg>
                    </button>
                </div>
            </div>`;
            
            $('#dynamic-fields-SizePrice').append(newField); // Append the new fields
            colorCount++; // Increment the count
        }
    });

    // Event delegation for removing fields
    $(document).on('click', '.removefieldSizePrice', function() {
        $(this).closest('.size-price-row').remove(); // Remove the closest row
        colorCount--; // Decrement the count
    });
});
    $(document).ready(function() {
        var add_imagecolor_button = $(".addfieldimagecolor");
        var max_fieldss = 10;
        var y = 1;
        let colorCount = {{ count($product->color) }};
        $('.addfieldimagecolor').click(function() {
            var newFieldss = `
              <div class="row mb-5">
                <div class="col-md-4">
                    <label class="form-label">Color Name</label>
                    <input type="text" id="color_name" name="color_name[]" placeholder="Enter Color Name" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Color Code</label>
                    <input type="color" id="color_code" name="color_code[]" placeholder="Enter Color Code" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Product color Images</label>
                    <input type="file" id="color_images" name="color_images[${colorCount}][]" multiple class="form-control">
                </div>
                <div class="col-md-1">
                    <label></label>
                    <button type="button" class="removeimagecolorfield btn btn-danger">
                        <svg height="30px" id="Layer_1" version="1.1" viewBox="0 0 50 50" width="30px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <rect fill="none" height="50" width="50"></rect>
                            <line fill="none" stroke="#ffffff" stroke-miterlimit="10" stroke-width="4" x1="9" x2="41" y1="25" y2="25"></line>
                        </svg>
                    </button>
                </div>
            </div>`;
            $('#dynamic-fields-imagecolor').append(newFieldss);
            colorCount++;
        });

        $(document).on('click', '.removeimagecolorfield', function() {
            $(this).closest('.row').remove();
        });
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('delete-color')) {
            const colorGroup = e.target.closest('.color-group');
            colorGroup.remove();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Handle color deletion
        document.querySelectorAll('.delete-color').forEach(function(button) {
            button.addEventListener('click', function() {
                var colorId = this.getAttribute('data-color-id');
                var colorGroup = this.closest('.color-group');

                // Remove the color group from the form
                colorGroup.remove();

                // Track deleted color IDs in a hidden input
                var deletedColorsInput = document.getElementById('deleted-colors');
                var deletedColors = deletedColorsInput.value ? deletedColorsInput.value.split(',') : [];
                
                if (colorId) {
                    deletedColors.push(colorId);
                }

                // Update the hidden input with the deleted color IDs
                deletedColorsInput.value = deletedColors.join(',');
            });
        });
    });
</script>

<script>
    $(document).ready(function() {
    // $('#title').summernote({
    //   placeholder: "Write short description.....",
    //     tabsize: 2,
    //     height: 150
    // });
    });
    $(document).ready(function() {
      $('#description,#product_features,#size_chart').summernote({
        placeholder: "Write detail Description.....",
          tabsize: 2,
          height: 150
      });
    });
</script>

<script>
  var  child_cat_id='{{$product->child_cat_id}}';
        // alert(child_cat_id);
        $('#cat_id').change(function(){
            var cat_id=$(this).val();

            if(cat_id !=null){
                // ajax call
                $.ajax({
                  url: "{{ url('admin/category') }}/" + cat_id + "/child",
                    type:"POST",
                    data:{
                        _token:"{{csrf_token()}}"
                    },
                    success:function(response){
                        if(typeof(response)!='object'){
                            response=$.parseJSON(response);
                        }
                        var html_option="<option value=''>--Select any one--</option>";
                        if(response.status){
                            var data=response.data;
                            if(response.data){
                                $('#child_cat_div').removeClass('d-none');
                                $.each(data,function(id,title){
                                    html_option += "<option value='"+id+"' "+(child_cat_id==id ? 'selected ' : '')+">"+title+"</option>";
                                });
                            }
                            else{
                                console.log('no response data');
                            }
                        }
                        else{
                            $('#child_cat_div').addClass('d-none');
                        }
                        $('#child_cat_id').html(html_option);

                    }
                });
            }
            else{

            }

        });
        if(child_cat_id!=null){
            $('#cat_id').change();
        }
</script>
@endpush