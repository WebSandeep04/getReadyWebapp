@extends('admin.layouts.app')

@section('title', 'Operations Calendar')

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
    :root {
        --cal-bg: #ffffff;
        --cal-header: rgb(33 37 41);
        --cal-border: #000000;
        --cal-accent: #4f46e5;
        --cal-pickup: #4f46e5;
        --cal-return: #e11d48;
        --cal-sale: #059669;
    }

    /* Zero Radius Global */
    *, ::before, ::after { border-radius: 0 !important; }

    .calendar-wrapper {
        background: var(--cal-bg);
        border: 2px solid var(--cal-border);
        margin-top: 1.5rem;
        box-shadow: 10px 10px 0px rgba(0,0,0,0.05);
    }

    /* Header Design */
    .cal-top-bar {
        background: var(--cal-header);
        color: #fff;
        padding: 0.75rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid var(--cal-border);
        position: relative; /* Base for centering */
    }

    .cal-title {
        font-weight: 800;
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0;
    }

    .cal-nav {
        display: flex;
        align-items: center;
        gap: 1rem;
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }

    .nav-btn {
        background: transparent;
        border: 1px solid rgba(255,255,255,0.3);
        color: #fff;
        padding: 4px 12px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .nav-btn:hover {
        background: #fff;
        color: #000;
    }

    .current-date {
        font-weight: 700;
        font-size: 0.95rem;
        min-width: 150px;
        text-align: center;
    }

    /* Grid Aesthetics */
    #calendar {
        padding: 10px;
        font-family: inherit;
    }

    .fc-col-header-cell {
        background: #f3f4f6;
        padding: 8px 0 !important;
        border: 1px solid var(--cal-border) !important;
    }

    .fc-col-header-cell-cushion {
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.7rem;
        color: #374151;
        text-decoration: none !important;
    }

    .fc-daygrid-day {
        border: 1px solid #e5e7eb !important;
        transition: background 0.2s;
    }

    .fc-day-today {
        background: #f9fafb !important;
    }

    .fc-daygrid-day-number {
        font-weight: 800;
        font-size: 0.75rem;
        padding: 4px 8px !important;
        color: #9ca3af;
        text-decoration: none !important;
    }

    .fc-day-today .fc-daygrid-day-number {
        color: #000;
    }

    /* Equal Columns */
    .fc-scrollgrid-sync-table, .fc-col-header {
        table-layout: fixed !important;
        width: 100% !important;
    }

    /* Compact Cells */
    .fc-daygrid-day-frame {
        min-height: 60px !important;
    }

    /* Event Styling */
    .fc-daygrid-event {
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        margin: 1px 2px !important;
    }

    .event-indicator {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 2px 4px;
        border: 1px solid transparent;
        white-space: nowrap;
        overflow: hidden;
    }

    .ind-p { color: var(--cal-pickup); border-left: 2px solid var(--cal-pickup); background: #eef2ff; }
    .ind-r { color: var(--cal-return); border-left: 2px solid var(--cal-return); background: #fff1f2; }
    .ind-s { color: var(--cal-sale); border-left: 2px solid var(--cal-sale); background: #ecfdf5; }

    /* Modal Styling */
    .modal-content {
        border: 3px solid var(--cal-border);
    }

    .modal-header {
        background: var(--cal-header);
        color: #fff;
        padding: 0.75rem 1rem;
    }

    .modal-title { font-size: 0.9rem; font-weight: 800; text-transform: uppercase; }

    .btn-close-white { filter: invert(1) grayscale(100%) brightness(200%); }

    .table-alert {
        font-size: 0.7rem;
        margin-bottom: 0;
    }

    .table-alert th {
        background: #f9fafb;
        text-transform: uppercase;
        font-weight: 800;
        color: #4b5563;
        border-color: var(--cal-border);
    }

    .table-alert td {
        border-color: var(--cal-border);
        font-weight: 600;
        padding: 8px !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="calendar-wrapper">
        <!-- Custom Top Bar -->
        <div class="cal-top-bar">
            <h1 class="cal-title">Operations Alert</h1>
            <div class="cal-nav">
                <button class="nav-btn" id="prevBtn"><i class="bi bi-chevron-left"></i></button>
                <div class="current-date" id="calTitle">Month Year</div>
                <button class="nav-btn" id="nextBtn"><i class="bi bi-chevron-right"></i></button>
            </div>
            <div>
                <button class="nav-btn" id="todayBtn">Today</button>
            </div>
        </div>

        <!-- FullCalendar -->
        <div id='calendar'></div>
    </div>
</div>

<!-- Detailed Alert Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Daily Operation Alert</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="eventModalBody">
                <!-- Table loaded via JS -->
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-dark btn-sm px-4 fw-bold" data-bs-dismiss="modal">ACKNOWLEDGE</button>
                <a href="#" id="viewOrderBtn" class="btn border-dark btn-sm px-4 fw-bold">ORDER DETAILS</a>
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
            headerToolbar: false, // Using custom header
            showNonCurrentDates: false,
            fixedWeekCount: false,
            dayMaxEvents: 4,
            contentHeight: 'auto',
            
            events: [
                @foreach($orders as $order)
                    @if($order->has_rental_items)
                        {
                            id: '{{ $order->id }}',
                            title: '[P] {{ $order->buyer->name }}',
                            start: '{{ $order->rental_from->format("Y-m-d") }}',
                            extendedProps: {
                                type: 'Pickup',
                                customer: '{{ $order->buyer->name }}',
                                date: '{{ $order->rental_from->format("d/m/Y") }}',
                                time: 'After 8:00 PM',
                                security: '-',
                                rent: '₹{{ number_format($order->total_amount - $order->security_amount, 2) }}',
                                selling: '-'
                            }
                        },
                        {
                            id: '{{ $order->id }}',
                            title: '[R] {{ $order->buyer->name }}',
                            start: '{{ $order->rental_to->format("Y-m-d") }}',
                            extendedProps: {
                                type: 'Return',
                                customer: '{{ $order->buyer->name }}',
                                date: '{{ $order->rental_to->format("d/m/Y") }}',
                                time: 'After 2:00 PM',
                                security: '₹{{ number_format($order->security_amount, 2) }}',
                                rent: '-',
                                selling: '-'
                            }
                        },
                    @endif
                    @if($order->has_purchase_items)
                        {
                            id: '{{ $order->id }}',
                            title: '[S] {{ $order->buyer->name }}',
                            start: '{{ $order->created_at->format("Y-m-d") }}',
                            extendedProps: {
                                type: 'Sale',
                                customer: '{{ $order->buyer->name }}',
                                date: '{{ $order->created_at->format("d/m/Y") }}',
                                time: 'Business Hours',
                                security: '-',
                                rent: '-',
                                selling: '₹{{ number_format($order->total_amount, 2) }}'
                            }
                        },
                    @endif
                @endforeach
            ],

            eventContent: function(arg) {
                let type = arg.event.extendedProps.type;
                let cls = type === 'Pickup' ? 'ind-p' : (type === 'Return' ? 'ind-r' : 'ind-s');
                let el = document.createElement('div');
                el.className = 'event-indicator ' + cls;
                el.innerHTML = arg.event.title;
                return { domNodes: [el] };
            },

            datesSet: function(info) {
                document.getElementById('calTitle').innerText = info.view.title;
            },

            eventClick: function(info) {
                const p = info.event.extendedProps;
                let tableHtml = `
                    <div class="table-responsive">
                        <table class="table table-bordered table-alert text-center mb-0">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Security Refundable</th>
                                    <th>Rent Payable</th>
                                    <th>Selling Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-bold text-dark">GR-${info.event.id.padStart(5, '0')}</td>
                                    <td>${p.date}</td>
                                    <td>${p.time}</td>
                                    <td class="${p.security !== '-' ? 'text-danger fw-bold' : ''}">${p.security}</td>
                                    <td class="${p.rent !== '-' ? 'text-primary fw-bold' : ''}">${p.rent}</td>
                                    <td class="${p.selling !== '-' ? 'text-success fw-bold' : ''}">${p.selling}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 bg-light border-top">
                        <div class="d-flex gap-3 align-items-center">
                            <div style="width: 10px; height: 10px; background: ${p.type === 'Pickup' ? '#4f46e5' : (p.type === 'Return' ? '#e11d48' : '#059669')}"></div>
                            <span class="small fw-bold text-uppercase">Operation Trace: Customer <strong>${p.customer}</strong> scheduled for <strong>${p.type}</strong>.</span>
                        </div>
                    </div>
                `;
                document.getElementById('eventModalBody').innerHTML = tableHtml;
                document.getElementById('viewOrderBtn').href = `/admin/orders?search=${info.event.id}`;
                new bootstrap.Modal(document.getElementById('eventModal')).show();
            }
        });

        calendar.render();

        // Custom Nav Actions
        document.getElementById('prevBtn').onclick = () => calendar.prev();
        document.getElementById('nextBtn').onclick = () => calendar.next();
        document.getElementById('todayBtn').onclick = () => calendar.today();
    });
</script>
@endpush
