@extends('backend.layouts.master')

@section('title','Review Edit')

@section('main-content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
    </div>
@endif
<div class="card">
  <h5 class="card-header">Review Edit</h5>
  <div class="card-body">
    <form action="{{route('admin.review.update', $review->id)}}" method="POST">
      @csrf
      <div class="form-group">
        <label for="name">Review By:</label>
        <input type="text" disabled class="form-control" value="{{$review->user_info->name}}">
      </div>
      <div class="form-group">
        <label for="review">Review</label>
        <textarea name="review" id="" cols="20" rows="10" class="form-control">{{$review->review}}</textarea>
        @error('review') <span class="text-danger">{{ $message }}</span> @enderror
      </div>
      <div class="form-group">
        <label for="status">Status :</label>
        <select name="status" id="" class="form-control">
          <option value="">--Select Status--</option>
          <option value="active" {{(($review->status=='active')? 'selected' : '')}}>Active</option>
          <option value="inactive" {{(($review->status=='inactive')? 'selected' : '')}}>Inactive</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Update</button>
    </form>
  </div>
</div>
@endsection

@push('styles')
<style>
    .order-info,.shipping-info{
        background:#ECECEC;
        padding:20px;
    }
    .order-info h4,.shipping-info h4{
        text-decoration: underline;
    }
</style>
@endpush