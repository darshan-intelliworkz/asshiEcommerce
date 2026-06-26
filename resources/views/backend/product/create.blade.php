@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">Add Product</h5>
    <div class="card-body">
      <form method="post" action="{{route('product.store')}}" enctype="multipart/form-data">
        {{csrf_field()}}
        <div class="form-group">
          <label for="inputProductCode" class="col-form-label">Product Code <span class="text-danger">*</span></label>
          <input id="inputProductCode" type="text" name="product_code" placeholder="Enter product code"  value="{{old('product_code')}}" class="form-control">
          @error('product_code')
          <span class="text-danger">{{$message}}</span>
          @enderror
          @error('slug')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="inputTitle" class="col-form-label">Title <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="title" name="title" value="{{old('title')}}">
           
          @error('title')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="description" class="col-form-label">Description</label>
          <textarea class="form-control" id="description" name="description">{{old('description')}}</textarea>
          @error('description')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="product_features" class="col-form-label">Product Features</label>
          <textarea class="form-control" id="product_features" name="product_features">{{old('product_features')}}</textarea>
          @error('product_features')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="size_chart" class="col-form-label">Size Chart</label>
          <textarea class="form-control" id="size_chart" name="size_chart">{{old('size_chart')}}</textarea>
          @error('size_chart')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="is_featured">Is Featured</label><br>
          <input type="checkbox" name='is_featured' id='is_featured' value='1' checked> Yes                        
        </div>
        
        <div class="form-group">
          <label for="cat_id">Category <span class="text-danger">*</span></label>
          <select name="cat_id" id="cat_id" class="form-control">
              <option value="">--Select any category--</option>
              @foreach($categories as $key=>$cat_data)
                  <option value='{{$cat_data->id}}'>{{$cat_data->title}}</option>
              @endforeach
          </select>
          @error('cat_id')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group d-none" id="child_cat_div">
          <label for="child_cat_id">Sub Category</label>
          <select name="child_cat_id" id="child_cat_id" class="form-control">
              <option value="">--Select any category--</option>
              {{-- @foreach($parent_cats as $key=>$parent_cat)
                  <option value='{{$parent_cat->id}}'>{{$parent_cat->title}}</option>
              @endforeach --}}
          </select>
        </div>

        <div class="form-group">
          <label for="discount" class="col-form-label">Discount(%)</label>
          <input id="discount" type="number" name="discount" min="0" max="100" placeholder="Enter discount"  value="{{old('discount')}}" class="form-control">
          @error('discount')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="gst_percent" class="col-form-label">GST Rate(%)</label>
          <input id="gst_percent" type="number" name="gst_percent" min="0" max="100" placeholder="Enter GST percent"  value="{{old('gst_percent')}}" class="form-control">
          @error('gst_percent')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        

        <div class="form-group">
          <label for="brand_id">Brand</label>
          {{-- {{$brands}} --}}

          <select name="brand_id" class="form-control">
              <option value="">--Select Brand--</option>
             @foreach($brands as $brand)
              <option value="{{$brand->id}}">{{$brand->title}}</option>
             @endforeach
          </select>
        </div>

        <!--<div class="form-group">-->
        <!--  <label for="condition">Condition</label>-->
        <!--  <select name="condition" class="form-control">-->
        <!--      <option value="">--Select Condition--</option>-->
        <!--      <option value="default">Default</option>-->
        <!--      <option value="new">New</option>-->
        <!--      <option value="hot">Hot</option>-->
        <!--  </select>-->
        <!--</div>-->
        
        <div class="form-group">
          <label for="hsn_code">HSN Code <span class="text-danger">*</span></label>
          <input id="hsn_code" type="text" name="hsn_code" placeholder="Enter HSN code"  value="{{old('hsn_code')}}" class="form-control">
          @error('hsn_code')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        
        <div class="form-group">
          <label for="stock">Quantity <span class="text-danger">*</span></label>
          <input id="quantity" type="number" name="stock" min="0" placeholder="Enter quantity"  value="{{old('stock')}}" class="form-control">
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
          <input id="inputVideo" type="file" name="video" class="form-control" multiple accept="video/*">
          <div id="video-holder" style="margin-top:15px;max-height:100px;"></div>
          @error('video')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        
        <div class="form-group">
          <label for="status" class="col-form-label">Status <span class="text-danger">*</span></label>
          <select name="status" class="form-control">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
          </select>
          @error('status')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div id="productSizePrice" class="row mb-5">
            <div class="col-md-12">
              <hr>
              <h6 class="m-0 fw-bold mb-4">Product Size & Price</h6>
              
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label for="size">Size <span class="text-danger">*</span></label>
                <select name="size[]" class="form-control">
                    <option value="">--Select Condition--</option>
                    @foreach($postSize as $key=>$val)
                      <option value="{{ $val->title }}">{{ $val->title }}</option>
                    @endforeach 
                </select>
                @error('size')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
                @error('size.*')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
            </div>
            <div class="col-md-5">
                <div class="form-group">
                    <label for="price" class="col-form-label">Price <span class="text-danger">*</span></label>
                    <input id="price" type="number" name="price[]" placeholder="Enter price"  class="form-control">
                    @error('price')
                      <span class="text-danger">{{ $message }}</span>
                    @enderror
                    @error('price.*')
                      <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="col-md-2">
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
        <div id="dynamic-fields-SizePrice"></div>
        <div id="productcolorimage" class="row mb-5">
            <div class="col-md-12">
              <hr>
              <h6 class="m-0 fw-bold mb-4">Product Color & Image</h6>
              
            </div>

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
                <input type="file" id="color_images" name="color_images[0][]" class="form-control" multiple>
            </div>
            <div class="col-md-1">
                <label></label>
                <button type="button" class="addfieldimagecolor  btn btn-primary">
                    <svg height="30px" id="Layer_1" version="1.1" viewBox="0 0 50 50" width="30px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <rect fill="none" height="50" width="50"></rect>
                        <line fill="none" stroke="#ffffff" stroke-miterlimit="10" stroke-width="4" x1="9" x2="41" y1="25" y2="25"></line>
                        <line fill="none" stroke="#ffffff" stroke-miterlimit="10" stroke-width="4" x1="25" x2="25" y1="9" y2="41"></line>
                    </svg>
                </button>
            </div>
        </div>
        <div id="dynamic-fields-imagecolor"></div>
        <div class="form-group mb-3">
          <button type="reset" class="btn btn-warning">Reset</button>
           <button class="btn btn-success" type="submit">Submit</button>
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
        var add_imagecolor_button = $(".addfieldSizePrice");
        var max_fieldss = 10;
        var y = 1;
        let colorCount = 1;
        $('.addfieldSizePrice').click(function() {
            var newFieldss = `
              <div class="row mb-5">
                <div class="col-md-5">
                  <div class="form-group">
                    <label for="size">Size</label>
                    <select name="size[]" class="form-control">
                        <option value="">--Select Condition--</option>
                        @foreach($postSize as $key=>$val)
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
                <div class="col-md-1">
                    <label></label>
                    <button type="button" class="removefieldSizePrice btn btn-danger">
                        <svg height="30px" id="Layer_1" version="1.1" viewBox="0 0 50 50" width="30px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <rect fill="none" height="50" width="50"></rect>
                            <line fill="none" stroke="#ffffff" stroke-miterlimit="10" stroke-width="4" x1="9" x2="41" y1="25" y2="25"></line>
                        </svg>
                    </button>
                </div>
            </div>`;
            $('#dynamic-fields-SizePrice').append(newFieldss);
            colorCount++;
        });

        $(document).on('click', '.removefieldSizePrice', function() {
            $(this).closest('.row').remove();
        });
    });
    $(document).ready(function() {
        var add_imagecolor_button = $(".addfieldimagecolor");
        var max_fieldss = 10;
        var y = 1;
        let colorCount = 1;
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
</script>

<script>
    $(document).ready(function() {
    //   $('#title').summernote({
    //     placeholder: "Write short description.....",
    //       tabsize: 2,
    //       height: 100
    //   });
    });

    $(document).ready(function() {
      $('#description,#product_features, #size_chart').summernote({
        placeholder: "Write Details.....",
          tabsize: 2,
          height: 150
      });
    });
    // $('select').selectpicker();

</script>

<script>
  $('#cat_id').change(function(){
    var cat_id=$(this).val();
    // alert(cat_id);
    if(cat_id !=null){
      // Ajax call
      $.ajax({
        url: "{{ url('admin/category') }}/" + cat_id + "/child",
        data:{
          _token:"{{csrf_token()}}",
          id:cat_id
        },
        type:"POST",
        success:function(response){
          if(typeof(response) !='object'){
            response=$.parseJSON(response)
          }
          // console.log(response);
          var html_option="<option value=''>----Select sub category----</option>"
          if(response.status){
            var data=response.data;
            // alert(data);
            if(response.data){
              $('#child_cat_div').removeClass('d-none');
              $.each(data,function(id,title){
                html_option +="<option value='"+id+"'>"+title+"</option>"
              });
            }
            else{
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
  })
</script>
@endpush