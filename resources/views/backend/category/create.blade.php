@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">Add Category</h5>
    <div class="card-body">
      <form method="post" action="{{route('category.store')}}" enctype="multipart/form-data">
        {{csrf_field()}}
        <div class="form-group">
          <label for="inputTitle" class="col-form-label">Title <span class="text-danger">*</span></label>
          <input id="inputTitle" type="text" name="title" placeholder="Enter title"  value="{{old('title')}}" class="form-control">
          @error('title')
          <span class="text-danger">{{$message}}</span>
          @enderror
          @error('slug')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="summary" class="col-form-label">Summary</label>
          <textarea class="form-control" id="summary" name="summary">{{old('summary')}}</textarea>
          @error('summary')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="is_parent">Is Parent</label><br>
          <input type="checkbox" name='is_parent' id='is_parent' value='1' {{ old('is_parent') ? 'checked' : '' }}> Yes                   
        </div>
        @error('is_parent')
          <span class="text-danger">{{$message}}</span>
        @enderror
        {{-- {{$parent_cats}} --}}

        <div class="form-group" id='parent_cat_div'>
          <label for="parent_id">Child Category</label>
          <select name="parent_id" class="form-control">
              <option value="">--Select any category--</option>
              @foreach($parent_cats as $parent_cat)
                  <option value="{{ $parent_cat->id }}" {{ old('parent_id') == $parent_cat->id ? 'selected' : '' }}>
                      {{ $parent_cat->title }}
                  </option>
              @endforeach
          </select>
          @error('parent_id')
            <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="inputPhoto" class="col-form-label">Photo</label>
          <input id="inputFile" type="file" name="photo" class="form-control">
          <div id="holder" style="margin-top:15px;max-height:100px;"></div>
          @error('photo')
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
        
        <div class="form-group">
          <label for="inputTitle" class="col-form-label">GST (in %) <span class="text-danger">*</span></label>
          <input id="inputTitle" type="text" name="gst" placeholder="Enter GST"  value="{{old('gst')}}" class="form-control">
          @error('gst')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        
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
@endpush
@push('scripts')
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script src="{{asset('public/backend/summernote/summernote.min.js')}}"></script>
<script>
    // $('#lfm').filemanager('image');

    // $(document).ready(function() {
    //   $('#is_parent').trigger('change');
    
    //   $('#summary').summernote({
    //     placeholder: "Write short description.....",
    //       tabsize: 2,
    //       height: 120
    //   });
    // });

//   $('#is_parent').change(function(){
//     var is_checked=$('#is_parent').prop('checked');
//     if(is_checked){
//       $('#parent_cat_div').addClass('d-none');
//       $('#parent_cat_div').val('');
//     }
//     else{
//       $('#parent_cat_div').removeClass('d-none');
//     }
//   })
</script>

<script>
$(document).ready(function() {

    // ✅ Function to toggle child category div
    function toggleParentCategory() {
        var is_checked = $('#is_parent').is(':checked'); // use is(':checked') for reliability
        if (is_checked) {
            $('#parent_cat_div').hide(); // hide the div if parent is checked
        } else {
            $('#parent_cat_div').show(); // show the div if parent is unchecked
        }
    }

    // ✅ Run on page load (respects old('is_parent'))
    toggleParentCategory();

    // ✅ Run on checkbox change
    $('#is_parent').change(function() {
        toggleParentCategory();
    });

    // Summernote initialization
    $('#summary').summernote({
        placeholder: "Write short description.....",
        tabsize: 2,
        height: 120
    });

    // Laravel filemanager init
    $('#lfm').filemanager('image');

});
</script>

@endpush