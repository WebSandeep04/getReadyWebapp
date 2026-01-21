@extends('admin.layouts.app')

@section('title', 'Manage Body Type Fits')
@section('page_title', 'Manage Body Type Fits')

@section('content')
@include('admin.components.setup-crud', [
    'config' => [
        'slug' => 'body-type-fits',
        'singular' => 'Body Type Fit',
        'plural' => 'Body Type Fits',
        'badge' => 'Fit · Confidence',
        'hero_title' => 'Champion every body with curated fits',
        'hero_subtitle' => 'Label silhouettes that flatter each body type so stylists can recommend with confidence.',
        'cta_label' => 'New Fit Tag',
        'status_label' => 'Confidence tag',
        'search_placeholder' => 'Search fit personas...',
        'empty_title' => 'No fit personas yet',
        'empty_description' => 'Define flattering fits to power personalization and recommendations.',
        'total' => $total ?? 0,
        'routes' => [
            'json' => route('body_type_fits.json'),
            'store' => route('body_type_fits.store'),
            'update' => url('/admin/body-type-fits'),
            'delete' => url('/admin/body-type-fits'),
        ],
        'modal' => [
            'field_label' => 'Fit persona name',
            'field_placeholder' => 'e.g. Hourglass Harmony',
            'add_title' => 'Add Body Type Fit',
            'edit_title' => 'Edit Body Type Fit',
        ],
    ],
])
@endsection