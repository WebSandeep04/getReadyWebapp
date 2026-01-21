@extends('admin.layouts.app')

@section('title', 'Manage Categories')
@section('page_title', 'Manage Categories')

@section('content')
@include('admin.components.setup-crud', [
    'config' => [
        'slug' => 'categories',
        'singular' => 'Category',
        'plural' => 'Categories',
        'badge' => 'Inventory · Categories',
        'hero_title' => 'Design rich & vibrant collections',
        'hero_subtitle' => 'Organize outfits, drop seasonal edits, and keep the catalog fresh. Create, edit, and curate categories with a single tap.',
        'cta_label' => 'New Category',
        'status_label' => 'Collection Tag',
        'search_placeholder' => 'Search categories by name...',
        'empty_title' => 'No categories yet',
        'empty_description' => 'Click “New Category” to add your first collection and start grouping looks.',
        'total' => $total ?? 0,
        'routes' => [
            'json' => route('categories.json'),
            'store' => route('categories.store'),
            'update' => url('/admin/categories'),
            'delete' => url('/admin/categories'),
        ],
        'modal' => [
            'field_label' => 'Category name',
            'field_placeholder' => 'e.g. Bridal Edit',
        ],
    ],
])
@endsection