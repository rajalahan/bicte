<!doctype html>
<html lang="ne">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login · {{ config('app.name') }}</title>
<link href="https://fonts.googleapis.com/css2?family=Mukta:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
  body { font-family: 'Mukta','Inter',sans-serif; background: linear-gradient(135deg,#0b3d91,#082c6c); min-height:100vh; display:flex; align-items:center; justify-content:center; }
  .login-card { background:#fff; padding:36px 32px; border-radius:18px; box-shadow:0 30px 60px rgba(0,0,0,.25); max-width:420px; width:90%; }
  .login-card h1 { font-size:24px; margin-bottom:6px; color:#0b3d91; }
  .form-control { padding:12px 14px; }
  .btn-primary { background:#0b3d91; border-color:#0b3d91; padding:12px; font-weight:600; }
  .btn-primary:hover { background:#082c6c; border-color:#082c6c; }
</style>
</head>
<body>
<div class="login-card">
    <h1><i class="bi bi-shield-lock-fill"></i> Admin Login</h1>
    <p class="text-muted mb-4">लहान नगरपालिका · डिजिटल सूचना केन्द्र</p>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="post" action="{{ route('admin.login') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">इमेल</label>
            <input type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">पासवर्ड</label>
            <input type="password" class="form-control" name="password" required>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">मलाई याद राख्नुहोस्</label>
        </div>
        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right"></i> Login</button>
    </form>
</div>
</body>
</html>
