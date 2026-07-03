@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
<div class="container py-5">
    <h1 class="mb-4">Contact Us</h1>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4 p-md-5">
                    <h4 class="mb-4">Get In Touch</h4>
                    <p class="mb-4">We are here to help and answer any question you might have. We look forward to hearing from you.</p>
                    
                    <div class="d-flex align-items-start mb-4">
                        <i class="bi bi-geo-alt-fill text-gold fs-3 me-3"></i>
                        <div>
                            <h5 class="mb-1">Registered Office</h5>
                            <p class="mb-0 text-muted">
                                Flat 1807, D Wing, Mahaavir Exotique,<br>
                                Upper Kharghar, Navi Mumbai,<br>
                                Maharashtra
                            </p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start mb-4">
                        <i class="bi bi-envelope-fill text-gold fs-3 me-3"></i>
                        <div>
                            <h5 class="mb-1">Email Support</h5>
                            <p class="mb-0 text-muted">
                                <a href="mailto:Contactgetready2@gmail.com" class="text-decoration-none text-muted">Contactgetready2@gmail.com</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100 bg-light">
                <div class="card-body p-4 p-md-5 d-flex flex-column justify-content-center text-center">
                    <h4 class="mb-3">Need Immediate Assistance?</h4>
                    <p class="text-muted mb-4">Our support team is available to help you with your orders, returns, and any other inquiries.</p>
                    <a href="mailto:Contactgetready2@gmail.com" class="btn btn-dark btn-lg">Email Us Now</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
