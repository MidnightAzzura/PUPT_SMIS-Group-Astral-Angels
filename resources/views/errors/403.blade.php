{{-- filepath: d:\Web Dev 3-1\Activity 1_Dela Cruz\SMIS31\resources\views\errors\403.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access Denied | {{ config('app.name', 'Laravel') }}</title>
    <meta name="theme-color" content="#0f172a">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/error.css') }}">
</head>
<body>
    <div class="bg-pattern" aria-hidden="true"></div>

    <main class="flex-center position-ref full-height" role="main" aria-labelledby="error-title">
        <div class="container card">
            <header class="header">
                <h1 id="error-title" class="title">Access Denied</h1>
                <p class="subtitle">You don't have permission to view this page.</p>
            </header>

            <section class="content">
                <div class="illustration" aria-hidden="true">
                    {{-- simple inline SVG lock / shield for modern look --}}
                    <svg width="160" height="160" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" class="lock-svg">
                        <defs>
                            <linearGradient id="g" x1="0" x2="1">
                                <stop offset="0%" stop-color="#60a5fa"/>
                                <stop offset="100%" stop-color="#7c3aed"/>
                            </linearGradient>
                        </defs>
                        <rect width="64" height="64" rx="12" fill="url(#g)" opacity="0.12"/>
                        <g transform="translate(12 10)" fill="none" stroke="#e2e8f0" stroke-width="2">
                            <rect x="6" y="18" width="28" height="18" rx="3" fill="#0f172a" stroke="rgba(226,232,240,0.12)"/>
                            <path d="M20 18V12a6 6 0 0 0-12 0v6" stroke="#e2e8f0" stroke-linecap="round"/>
                            <circle cx="24" cy="27" r="2" fill="#e2e8f0"/>
                        </g>
                    </svg>
                </div>

                <div class="message-block">
                    <div class="error-code" aria-hidden="true">403</div>
                    <p class="error-message">
                        Sorry — you don’t have access to this resource. If you believe this is a mistake, contact an administrator or try one of the options below.
                    </p>

                    <ul class="suggestions" aria-hidden="true">
                        <li>Check you are signed in with the correct account.</li>
                        <li>Return to the previous page or go to your dashboard.</li>
                    </ul>

                    <div class="button-container">
                        <a href="{{ url()->previous() ?? route('home') }}" class="btn" aria-label="Go back to previous page">Go Back</a>

                        {{-- compute dashboard URL safely to avoid case/null issues --}}
                        @php
                            $role = strtolower(optional(auth()->user())->role ?? '');
                            $dashboardUrl = auth()->check() && $role === 'admin'
                                ? route('Admin.admin')
                                : route('dashboard.use');
                        @endphp

                        <a href="{{ $dashboardUrl }}" 
                           class="btn btn-primary" aria-label="Go to dashboard">Go to Dashboard</a>
                    </div>
                </div>
            </section>

            <footer class="footer" role="contentinfo">© {{ date('Y') }} {{ config('app.name', 'Laravel') }}</footer>
        </div>
    </main>

</body>
</html>