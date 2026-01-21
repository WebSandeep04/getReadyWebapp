@extends('admin.layouts.app')

@section('title', 'Manage Sizes')
@section('page_title', 'Manage Sizes')

@section('content')
@include('admin.components.setup-crud', [
    'config' => [
        'slug' => 'sizes',
        'singular' => 'Size',
        'plural' => 'Sizes',
        'badge' => 'Fit · Sizing',
        'hero_title' => 'Own the sizing grid customers trust',
        'hero_subtitle' => 'Keep conversions high with crisp, consistent size tags across every outfit.',
        'cta_label' => 'New Size',
        'status_label' => 'Fit mood',
        'search_placeholder' => 'Search size shortcuts...',
        'empty_title' => 'No sizes set',
        'empty_description' => 'List the sizes you stock to drive better matching & analytics.',
        'total' => $total ?? 0,
        'routes' => [
            'json' => route('sizes.json'),
            'store' => route('sizes.store'),
            'update' => url('/admin/sizes'),
            'delete' => url('/admin/sizes'),
        ],
        'modal' => [
            'field_label' => 'Size name',
            'field_placeholder' => 'e.g. XS / 34',
            'add_title' => 'Add Size',
            'edit_title' => 'Edit Size',
        ],
    ],
])
@endsection