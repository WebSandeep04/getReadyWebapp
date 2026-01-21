@extends('admin.layouts.app')

@section('title', 'Manage Bottom Types')
@section('page_title', 'Manage Bottom Types')

@section('content')
@include('admin.components.setup-crud', [
    'config' => [
        'slug' => 'bottom-types',
        'singular' => 'Bottom Type',
        'plural' => 'Bottom Types',
        'badge' => 'Silhouettes · Bottoms',
        'hero_title' => 'Map silhouettes that define every look',
        'hero_subtitle' => 'Track lehengas, skirts, pants, and draped cuts so stylists can filter fast.',
        'cta_label' => 'New Bottom Type',
        'status_label' => 'Style signal',
        'search_placeholder' => 'Search bottom silhouettes...',
        'empty_title' => 'No silhouettes mapped',
        'empty_description' => 'Curate bottoms to power filters, outfit builders, and analytics.',
        'total' => $total ?? 0,
        'routes' => [
            'json' => route('bottom_types.json'),
            'store' => route('bottom_types.store'),
            'update' => url('/admin/bottom-types'),
            'delete' => url('/admin/bottom-types'),
        ],
        'modal' => [
            'field_label' => 'Bottom type name',
            'field_placeholder' => 'e.g. Draped Saree Skirt',
            'add_title' => 'Add Bottom Type',
            'edit_title' => 'Edit Bottom Type',
        ],
    ],
])
@endsection