<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | getReady</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            background: #fff;
            border: none;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            padding: 2.5rem;
        }
        .brand-logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: #000;
            text-decoration: none;
            display: block;
            margin-bottom: 2rem;
            text-align: center;
            letter-spacing: -0.5px;
        }
        .form-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #4b5563;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border: 1px solid #e5e7eb;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            border-radius: 0 !important;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: #000;
            box-shadow: none;
        }
        .btn-dark {
            background: #000;
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            border-radius: 0 !important;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.2s;
        }
        .btn-dark:hover {
            background: #222;
            transform: translateY(-1px);
        }
        .alert {
            font-size: 0.8rem;
            border-radius: 0 !important;
            border: none;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <a href="/" class="brand-logo">GETREADY <span class="fw-light">ADMIN</span></a>
        
        @if($errors->has('login_error'))
            <div class="alert alert-danger px-3 py-2 mb-4">
                <i class="bi bi-exclamation-circle me-2"></i> {{ $errors->first('login_error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success px-3 py-2 mb-4">
                <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.login.post') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter username" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn btn-dark">Log In to Dashboard</button>
        </form>

        <div class="text-center mt-4">
            <a href="/" class="text-muted small text-decoration-none">← Back to Storefront</a>
        </div>
    </div>

</body>
</html>
