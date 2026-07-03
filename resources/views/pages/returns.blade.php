@extends('layouts.app')

@section('title', 'Cancellation and Returns Policy')

@section('styles')
<style>
    /* Premium Page Aesthetics */
    .premium-page-wrapper {
        background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
        min-height: 100vh;
        padding: 5rem 0;
        font-family: 'Inter', sans-serif;
    }
    
    .premium-header {
        text-align: center;
        margin-bottom: 4rem;
        animation: fadeInDown 0.8s ease-out;
    }
    
    .premium-header h1 {
        font-size: 3rem;
        font-weight: 800;
        background: linear-gradient(90deg, #1f2937 0%, #4b5563 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        letter-spacing: -1px;
    }
    
    .premium-header p {
        font-size: 1.1rem;
        color: #6b7280;
        max-width: 600px;
        margin: 1rem auto 0;
    }

    /* Glassmorphism Card */
    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 24px;
        padding: 3rem;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
        animation: fadeInUp 0.8s ease-out;
    }

    /* Section Styling */
    .policy-section {
        margin-bottom: 3rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid rgba(229, 231, 235, 0.5);
    }
    .policy-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .policy-title {
        display: flex;
        align-items: center;
        gap: 1rem;
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 1.5rem;
    }

    .policy-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        color: #4f46e5;
        border-radius: 14px;
        font-size: 1.2rem;
    }

    .policy-subsection {
        background: rgba(255, 255, 255, 0.5);
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid rgba(229, 231, 235, 0.5);
    }

    .policy-subsection:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.03);
    }

    .policy-subsection h6 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #374151;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .policy-subsection h6 i {
        color: #8b5cf6;
    }

    .policy-text {
        color: #4b5563;
        line-height: 1.7;
        font-size: 0.95rem;
        margin-bottom: 0;
    }

    /* Info Highlight */
    .info-highlight {
        background: linear-gradient(to right, rgba(59, 130, 246, 0.05), rgba(139, 92, 246, 0.05));
        border-left: 4px solid #3b82f6;
        padding: 1.25rem;
        border-radius: 0 12px 12px 0;
        margin: 1.5rem 0;
        font-weight: 500;
        color: #1e3a8a;
    }

    /* Animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@section('content')
<div class="premium-page-wrapper">
    <div class="container">
        <div class="premium-header">
            <h1>Cancellation & Returns</h1>
            <p>Clear, transparent, and fair policies tailored for your luxury rental experience.</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="glass-card">
                    
                    <!-- 1. Cancellation -->
                    <div class="policy-section">
                        <div class="policy-title">
                            <div class="policy-icon"><i class="bi bi-x-octagon"></i></div>
                            1. Order Cancellations
                        </div>
                        
                        <div class="policy-subsection">
                            <h6><i class="bi bi-person-x"></i> Cancellation by User</h6>
                            <p class="policy-text">
                                Users can seamlessly cancel their orders directly from the <strong>Orders Dashboard</strong>. 
                                <br><br>
                                <strong>Pending or Confirmed Orders:</strong> If your order has not yet been dispatched, you can cancel it for a <strong>full refund</strong>, which will be processed automatically to your original payment method.
                            </p>
                        </div>
                        
                        <div class="policy-subsection">
                            <h6><i class="bi bi-shield-x"></i> Cancellation by GETREADY</h6>
                            <p class="policy-text">
                                GETREADY reserves the right, at its sole discretion, to refuse or cancel any order for reasons such as product unavailability, payment anomalies, inaccurate shipping details, or suspected malpractice. 
                                <br><br>
                                If your order is cancelled after your payment method has been charged, the full amount will be reversed back to your account in a timely manner.
                            </p>
                        </div>
                    </div>

                    <!-- 2. Returns (Rental) -->
                    <div class="policy-section">
                        <div class="policy-title">
                            <div class="policy-icon"><i class="bi bi-box-arrow-in-left"></i></div>
                            2. Rental Returns
                        </div>
                        
                        <div class="policy-subsection">
                            <h6><i class="bi bi-calendar-check"></i> Standard Return After Use</h6>
                            <p class="policy-text">
                                You are responsible for returning the rented product(s) to GET READY on the <strong>Return Date</strong> (the day immediately following the last day of the Rental Period). Products must be returned in satisfactory condition. 
                            </p>
                            <div class="info-highlight">
                                <i class="bi bi-clock-history me-2"></i><strong>Early Returns:</strong> You have the option to initiate an early return through the site/app at any time before your scheduled return date!
                            </div>
                            <p class="policy-text">
                                <strong>Delay up to 8 Days:</strong> For each day of delay, a Late Fee equivalent to one (1) day’s rental amount will apply.<br>
                                <strong>Delay Beyond 8 Days:</strong> GET READY shall be entitled to charge an amount equivalent to 100% of the MRP of the product(s). Late fees and MRP charges will be adjusted against your Security Deposit.
                            </p>
                        </div>

                        <div class="policy-subsection">
                            <h6><i class="bi bi-exclamation-triangle"></i> Instant Return on Delivery (Issues)</h6>
                            <p class="policy-text">
                                Please check your product(s) properly at the time of delivery for size, color, style, or damage. 
                                If you face any issues, you must initiate an <strong>Instant Return</strong> via the app/website within <strong>4 hours</strong> of receiving the delivery. No requests will be entertained after this window.
                            </p>
                        </div>
                        
                        <div class="policy-subsection">
                            <h6><i class="bi bi-truck"></i> Pick-Up Logistics</h6>
                            <p class="policy-text">
                                The product(s) shall be picked up from your Shipping Address by our designated logistics partner on or before the return date. If you need to reschedule, please contact GETREADY via the details provided in your pickup notification.
                            </p>
                        </div>
                    </div>

                    <!-- 3. Refunds -->
                    <div class="policy-section">
                        <div class="policy-title">
                            <div class="policy-icon"><i class="bi bi-cash-stack"></i></div>
                            3. Refunds Process
                        </div>
                        <div class="policy-subsection">
                            <p class="policy-text">
                                All refunds are processed securely through our Third-Party Payment Gateways. The actual time taken for the refund to reflect in your account is subject to the processing timelines of your respective bank or card issuer (typically 5-7 business days). GET READY ensures that all refund requests are forwarded promptly.
                            </p>
                        </div>
                    </div>

                    <!-- 4. Sale Items -->
                    <div class="policy-section">
                        <div class="policy-title">
                            <div class="policy-icon"><i class="bi bi-bag-check"></i></div>
                            4. Sale Items (Purchases)
                        </div>
                        <div class="policy-subsection">
                            <p class="policy-text">
                                <strong>4-Hour Issue Reporting:</strong> Just like rentals, if you face any issues with an item you purchased, you can report it and request a return within <strong>4 hours</strong> of delivery for a full refund.
                                <br><br>
                                <strong>Final Sale:</strong> After the 4-hour window has passed, all purchased items are considered final sale and cannot be returned or cancelled. GETREADY owes no liability to the user for any damage caused during an unauthorized return attempt.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
