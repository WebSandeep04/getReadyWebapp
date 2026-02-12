@extends('admin.layouts.app')

@section('title', 'Alert Calendar')
@section('page_title', 'Rental Alert Calendar')

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
    *, ::before, ::after { border-radius: 0 !important; }
    .calendar-card {
        background: #fff;
        box-shadow: 0 8px 25px rgba(0,0,0,0.05);
        padding: 1.5rem;
        height: 100%;
        border: none;
    }
    #calendar {
        background: #fff;
        padding: 10px;
    }
    .fc-header-toolbar {
        margin-bottom: 2rem !important;
    }
    .fc-button-primary {
        background-color: #000 !important;
        border-color: #000 !important;
    }
    .fc-daygrid-event {
        font-size: 0.75rem;
        padding: 2px 5px;
        cursor: pointer;
    }
    .fc-event-title {
        font-weight: 600;
    }
    .event-rental {
        background-color: #e0f2fe !important;
        border-color: #bae6fd !important;
        color: #0369a1 !important;
    }
    .event-return {
        background-color: #fef2f2 !important;
        border-color: #fecaca !important;
        color: #991b1b !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="calendar-card">
                <div id='calendar'></div>
            </div>
        </div>
    </div>
</div>

<!-- Order Detail Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="eventModalBody">
                <!-- Content loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <a href="#" id="viewOrderBtn" class="btn btn-dark btn-sm">View Full Order</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            events: [
                @foreach($orders as $order)
                {
                    id: '{{ $order->id }}',
                    title: 'GR-{{ str_pad($order->id, 5, "0", STR_PAD_LEFT) }} (Pickup)',
                    start: '{{ $order->rental_from->format("Y-m-d") }}',
                    className: 'event-rental',
                    extendedProps: {
                        buyer: '{{ $order->buyer->name ?? "Unknown" }}',
                        status: '{{ $order->status }}',
                        type: 'Pickup'
                    }
                },
                {
                    id: '{{ $order->id }}',
                    title: 'GR-{{ str_pad($order->id, 5, "0", STR_PAD_LEFT) }} (Return)',
                    start: '{{ $order->rental_to->format("Y-m-d") }}',
                    className: 'event-return',
                    extendedProps: {
                        buyer: '{{ $order->buyer->name ?? "Unknown" }}',
                        status: '{{ $order->status }}',
                        type: 'Return'
                    }
                },
                @endforeach
            ],
            eventClick: function(info) {
                const orderId = info.event.id;
                const props = info.event.extendedProps;
                
                let bodyHtml = `
                    <div class="mb-2"><strong>Order ID:</strong> GR-${orderId.padStart(5, '0')}</div>
                    <div class="mb-2"><strong>Event Type:</strong> ${props.type}</div>
                    <div class="mb-2"><strong>Customer:</strong> ${props.buyer}</div>
                    <div class="mb-2"><strong>Order Status:</strong> <span class="badge ${getStatusBadgeClass(props.status)}">${props.status}</span></div>
                    <div class="mb-0"><strong>Date:</strong> ${info.event.start.toLocaleDateString('en-GB')}</div>
                `;
                
                document.getElementById('eventModalBody').innerHTML = bodyHtml;
                document.getElementById('viewOrderBtn').href = `{{ route('admin.orders') }}?search=${orderId}`;
                
                var myModal = new bootstrap.Modal(document.getElementById('eventModal'));
                myModal.show();
            }
        });
        calendar.render();

        function getStatusBadgeClass(status) {
            switch(status) {
                case 'Delivered': return 'bg-info';
                case 'Returned': return 'bg-success';
                case 'Cancelled': return 'bg-secondary';
                default: return 'bg-warning text-dark';
            }
        }
    });
</script>
@endpush
