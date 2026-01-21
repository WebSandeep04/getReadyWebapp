@extends('admin.layouts.app')

@section('title', 'Manage Colors')
@section('page_title', 'Manage Colors')

@section('content')
@include('admin.components.setup-crud', [
    'config' => [
        'slug' => 'colors',
        'singular' => 'Color',
        'plural' => 'Colors',
        'badge' => 'Palette · Mood',
        'hero_title' => 'Paint the rental story with vivid shades',
        'hero_subtitle' => 'Curate iconic hues and gradient moments to keep browsing joyful.',
        'cta_label' => 'New Color',
        'status_label' => 'Palette vibe',
        'search_placeholder' => 'Search color swatches...',
        'empty_title' => 'No colors added',
        'empty_description' => 'Map your hero colors to tags & quick filters for stylists.',
        'total' => $total ?? 0,
        'routes' => [
            'json' => route('colors.json'),
            'store' => route('colors.store'),
            'update' => url('/admin/colors'),
            'delete' => url('/admin/colors'),
        ],
        'modal' => [
            'field_label' => 'Color name',
            'field_placeholder' => 'e.g. Champagne Glow',
            'add_title' => 'Add Color',
            'edit_title' => 'Edit Color',
        ],
    ],
])
@endsection