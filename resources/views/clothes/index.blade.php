@extends('layouts.app')

@section('title', 'My Listed Clothes')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/listed-clothes.css') }}">
@endsection

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="text-warning mb-4">My Listed Clothes</h2>
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            
            @if($clothes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Gender</th>
                                <th>Size</th>
                                <th>Condition</th>
                                <th>Rent Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clothes as $cloth)
                                <tr>
                                    <td>
                                        @if($cloth->images->count() > 0)
                                            <img src="{{ asset('storage/' . $cloth->images->first()->image_path) }}" 
                                                 alt="{{ $cloth->title }}" 
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px; border-radius: 5px;">
                                                <span class="text-muted small">No Image</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $cloth->title }}</td>
                                    <td>{{ $cloth->category }}</td>
                                    <td>{{ $cloth->gender }}</td>
                                    <td>{{ $sizes->find($cloth->size)->name ?? 'Unknown' }}</td>
                                    <td>{{ $cloth->condition }}</td>
                                    <td>₹{{ $cloth->rent_price }}</td>
                                    <td>
                                        <span class="badge {{ $cloth->is_approved === 1 ? 'badge-success' : 'badge-warning' }}">
                                            {{ $cloth->is_approved === 1 ? 'Approved' : 'Pending Approval' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('clothes.show', $cloth->id) }}" 
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                                                                 <a href="{{ route('listed.clothes.edit', $cloth->id) }}" 
                                            class="btn btn-primary btn-sm">
                                             <i class="fas fa-edit"></i> Edit
                                         </a>
                                        <form action="{{ route('listed.clothes.destroy', $cloth->id) }}" 
                                              method="POST" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Are you sure you want to delete this cloth?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <h4 class="text-muted">No clothes listed yet</h4>
                    <p class="text-muted">Start selling your clothes by listing them!</p>
                    <a href="{{ route('sell') }}" class="btn btn-warning">List Your First Item</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 