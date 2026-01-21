@extends('admin.layouts.app')

@section('title', 'Manage Garment Conditions')
@section('page_title', 'Manage Garment Conditions')

@section('content')
@include('admin.components.setup-crud', [
    'config' => [
        'slug' => 'garment-conditions',
        'singular' => 'Garment Condition',
        'plural' => 'Garment Conditions',
        'badge' => 'Quality · Care',
        'hero_title' => 'Signal garment quality transparently',
        'hero_subtitle' => 'Label condition states so renters know exactly what to expect on delivery day.',
        'cta_label' => 'New Condition Tag',
        'status_label' => 'Condition tier',
        'search_placeholder' => 'Search condition labels...',
        'empty_title' => 'No conditions defined',
        'empty_description' => 'Define Luxe, Loved, or Sample-ready states to build trust instantly.',
        'total' => $total ?? 0,
        'routes' => [
            'json' => route('garment_conditions.json'),
            'store' => route('garment_conditions.store'),
            'update' => url('/admin/garment-conditions'),
            'delete' => url('/admin/garment-conditions'),
        ],
        'modal' => [
            'field_label' => 'Condition name',
            'field_placeholder' => 'e.g. Couture Mint',
            'add_title' => 'Add Garment Condition',
            'edit_title' => 'Edit Garment Condition',
        ],
    ],
])
@endsection