@extends('admin.layouts.app')

@section('title', 'Manage Fabric Types')
@section('page_title', 'Manage Fabric Types')

@section('content')
@include('admin.components.setup-crud', [
    'config' => [
        'slug' => 'fabric-types',
        'singular' => 'Fabric Type',
        'plural' => 'Fabric Types',
        'badge' => 'Textiles · Weaves',
        'hero_title' => 'Detail every texture & fabric story',
        'hero_subtitle' => 'Catalog silks, satins, chiffons, and couture blends with a refined dashboard.',
        'cta_label' => 'New Fabric Type',
        'status_label' => 'Usage vibe',
        'search_placeholder' => 'Search fabric families...',
        'empty_title' => 'No fabric types logged',
        'empty_description' => 'Document the fibers you rent most often to speed up listings.',
        'total' => $total ?? 0,
        'routes' => [
            'json' => route('fabric_types.json'),
            'store' => route('fabric_types.store'),
            'update' => url('/admin/fabric-types'),
            'delete' => url('/admin/fabric-types'),
        ],
        'modal' => [
            'field_label' => 'Fabric type name',
            'field_placeholder' => 'e.g. Velvet Luxe',
            'add_title' => 'Add Fabric Type',
            'edit_title' => 'Edit Fabric Type',
        ],
    ],
])
@endsection