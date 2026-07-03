@extends('layouts.app')

@section('title', 'About Us')

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
        font-size: 3.5rem;
        font-weight: 900;
        background: linear-gradient(90deg, #1f2937 0%, #4b5563 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        letter-spacing: -1px;
    }
    
    .premium-header p {
        font-size: 1.25rem;
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
        padding: 4rem 3rem;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
        animation: fadeInUp 0.8s ease-out;
    }

    .welcome-text {
        font-size: 1.5rem;
        font-weight: 700;
        color: #d4af37; /* Premium Gold */
        text-transform: uppercase;
        letter-spacing: 2px;
        text-align: center;
        margin-bottom: 1rem;
    }

    .lead-text {
        text-align: center;
        font-size: 1.15rem;
        color: #4b5563;
        max-width: 700px;
        margin: 0 auto 4rem;
        line-height: 1.8;
    }

    /* Grid layout for sections */
    .about-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    @media (min-width: 768px) {
        .about-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    .about-card {
        background: rgba(255, 255, 255, 0.5);
        border-radius: 20px;
        padding: 2rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid rgba(229, 231, 235, 0.5);
    }

    .about-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.04);
    }

    .about-card-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        color: #4f46e5;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .about-card h5 {
        font-size: 1.25rem;
        font-weight: 800;
        color: #111827;
        margin-bottom: 1rem;
    }

    .about-card p {
        color: #4b5563;
        line-height: 1.7;
        margin-bottom: 0;
    }

    /* Feature List Styling */
    .feature-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .feature-list li {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1rem;
        color: #4b5563;
        line-height: 1.6;
    }

    .feature-list li i {
        color: #10b981; /* Emerald */
        font-size: 1.2rem;
        margin-top: 2px;
    }

    .full-width {
        grid-column: 1 / -1;
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
            <h1>About GET READY</h1>
            <p>Elevating your wardrobe, one rental at a time.</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="glass-card">
                    
                    <h3 class="welcome-text">Welcome to GET READY</h3>
                    <p class="lead-text">
                        Your premier destination for high-end fashion rental. We believe in experiencing luxury without the commitment, empowering you to wear exactly what you want, when you want it.
                    </p>
                    
                    <div class="about-grid">
                        
                        <!-- Who We Are -->
                        <div class="about-card full-width">
                            <div class="about-card-icon"><i class="bi bi-building"></i></div>
                            <h5>Who We Are</h5>
                            <p>
                                GET READY is maintained by DP and Co., a partnership firm incorporated under the provisions of the Companies Act, 2013, having its registered office in New Delhi, India. We operate the internet portal GETREADY located at <strong>www.Getready.co.in</strong> and the GETREADY mobile apps on Android and iOS.
                            </p>
                        </div>

                        <!-- Our Mission -->
                        <div class="about-card">
                            <div class="about-card-icon" style="background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); color: #16a34a;"><i class="bi bi-compass"></i></div>
                            <h5>Our Mission</h5>
                            <p>
                                We aim to revolutionize how people access fashion by providing a sustainable, convenient, and affordable way to wear high-quality clothing. We believe that everyone deserves to look and feel their best without the need for excessive consumption or spending.
                            </p>
                        </div>

                        <!-- What We Do -->
                        <div class="about-card">
                            <div class="about-card-icon" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #d97706;"><i class="bi bi-layers"></i></div>
                            <h5>What We Do</h5>
                            <p>
                                Get Ready allows registered users to access our platform to browse, list, rent, or borrow clothing. Users may also list selected items for sale or purchase products offered for sale on the platform. By promoting a circular fashion economy, we help extend the lifecycle of garments.
                            </p>
                        </div>

                        <!-- Why Choose Us -->
                        <div class="about-card full-width">
                            <div class="about-card-icon" style="background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%); color: #db2777;"><i class="bi bi-star"></i></div>
                            <h5>Why Choose Us?</h5>
                            <ul class="feature-list mt-3">
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <div><strong>100% Original:</strong> Guarantee for all products available on our platform.</div>
                                </li>
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <div><strong>Flexibility:</strong> Rent for a few days or subscribe to our Unlimited plan for maximum flexibility.</div>
                                </li>
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <div><strong>Convenience:</strong> Easy delivery and pickup directly from your doorstep.</div>
                                </li>
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <div><strong>Sustainability:</strong> Be a part of the solution by sharing fashion instead of just buying it.</div>
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
