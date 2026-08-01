<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Smart School ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #1a1d2e 0%, #4361ee 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { max-width: 420px; width: 100%; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card card mx-auto">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="fas fa-graduation-cap fa-3x text-primary"></i>
                    <h3 class="mt-3 fw-bold">Smart School ERP</h3>
                    <p class="text-muted">Sign in to your account</p>
                </div>
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                @if(session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                <form method="POST" action="{{ route('admin.login.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
