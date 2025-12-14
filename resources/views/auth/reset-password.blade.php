@extends('layout.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/resetpass.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    @php
        use Illuminate\Support\Facades\URL;

        // Accept expiry, signature and email/token inputs
        $expiresParam = request()->has('expires') ? (int) request('expires') : null;
        // Accept email from view variable, route param, querystring or request input
        $emailParam = $email ?? request()->route('email') ?? request('email') ?? request()->query('email', null);
        // Support token via view variable, route parameter or request (query/body)
        $viewToken = $token ?? request()->route('token') ?? request('token') ?? request()->query('token', null);

        $linkError = null;
        $sigValid = null;
        $isExpired = false;
        $unsignedAllowed = false;

        // Helper to compute legacy HMAC signature (kept local to view for compatibility)
        $computeLegacySig = function (?string $token, ?string $email, ?int $expires) {
            if (empty($token) || empty($email) || empty($expires)) {
                return null;
            }
            $rawKey = config('app.key');
            if (strpos($rawKey, 'base64:') === 0) {
                $rawKey = base64_decode(substr($rawKey, 7));
            }
            return hash_hmac('sha256', $token . '|' . $email . '|' . $expires, $rawKey);
        };

        // Prefer Laravel signed routes when a "signature" query param is present
        if ($expiresParam && request()->query('signature', null)) {
            $sigValid = URL::hasValidSignature(request());
            $isExpired = $expiresParam && now()->timestamp > $expiresParam;

            if (!$sigValid) {
                $linkError = 'Invalid or tampered reset link. Please request a new password reset email.';
            } elseif ($isExpired) {
                $linkError = 'This reset link has expired. Please request a new password reset email.';
            }
        }
        // Backwards-compatible handling for legacy 'sig' parameter (keeps existing HMAC logic)
        elseif ($expiresParam && request()->query('sig', null) && $viewToken && $emailParam) {
            $expectedSig = $computeLegacySig($viewToken, $emailParam, $expiresParam);
            $providedSig = request()->query('sig');

            // Use hash_equals for timing-attack resistant comparison
            $sigValid = $expectedSig ? hash_equals($expectedSig, $providedSig) : false;
            $isExpired = now()->timestamp > $expiresParam;

            if (!$sigValid) {
                $linkError = 'Invalid or tampered reset link. Please request a new password reset email.';
            } elseif ($isExpired) {
                $linkError = 'This reset link has expired. Please request a new password reset email.';
            }
        }
        // Allow token+email legacy flow (no expiry/signature)
        elseif ($viewToken && $emailParam) {
            $sigValid = true;
            $unsignedAllowed = true;
        } else {
            // Missing required info -> invalid
            $linkError = 'Invalid reset link. Please request a new password reset email.';
            $sigValid = false;
            $isExpired = true;
        }
    @endphp

    <div class="container">
        <h1>Reset Your Password</h1>
        <!-- Link/session expiry countdown (10 minutes) -->
        <div class="expiry-banner">
            @if (!$linkError)
                @php
                    // compute remaining seconds for initial display (fallback to 10 minutes)
                    $remaining = $expiresParam ? max(0, $expiresParam - now()->timestamp) : 600;
                @endphp
                <small>Link expires in: <strong id="expiry-countdown">{{ gmdate('i:s', $remaining) }}</strong></small>
                @if (isset($unsignedAllowed) && $unsignedAllowed)
                    <div style="font-size:0.85rem;color:#6b7280;margin-top:6px;">
                        Note: this link does not include a signed expiry but is accepted because a valid token and email were provided.
                    </div>
                @endif
            @else
                <small style="color:#b00020">{{ $linkError }}</small>
            @endif
        </div>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if (session('success'))
            <div id="successAlert" class="alert alert-success">
                {{ session('success') }}
                <div class="timer-bar"></div>
                <span id="countdown">5</span>
            </div>
        @endif

        @if (!$linkError)
        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $viewToken }}">
            <input type="hidden" name="email" value="{{ $emailParam ?? request('email') ?? '' }}">

            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <div class="password-input">
                    <!-- Enforce combination via pattern -->
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control"
                        required
                        minlength="8"
                        pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}"
                        title="At least 8 characters with uppercase, lowercase, number, and special character"
                    >
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
                </div>
                <div class="password-requirements">
                    <small>Password must be at least 8 characters and include:</small>
                    <ul class="req-list">
                        <li id="req-lower" class="invalid">Lowercase letter</li>
                        <li id="req-upper" class="invalid">Uppercase letter</li>
                        <li id="req-number" class="invalid">Number</li>
                        <li id="req-special" class="invalid">Special character</li>
                    </ul>
                </div>
                <div class="password-strength mt-2">
                    <div class="strength-meter">
                        <div id="strength-bar" class="strength-bar"></div>
                    </div>
                    <span id="strength-text">Password strength</span>
                    <span id="crack-time" class="ms-2">Time to crack: Instant</span>
                </div>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <div class="password-input">
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('password_confirmation')"></i>
                </div>
                <small id="confirm-help" class="text-muted"></small>
            </div>

            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>
        @else
            <a class="btn btn-primary" href="{{ route('login') }}">Back to Login</a>
        @endif
    </div>

    <script>
        // Make expiry available to JS (seconds since epoch)
        window.RESET_EXPIRES = {{ $expiresParam ?: 'null' }};
        window.RESET_LINK_ERROR = {!! json_encode($linkError) !!};
        window.LOGIN_ROUTE = "{{ route('login') }}";
        // Do NOT expose the original token as a global variable here.
        // The token remains in the hidden form input and client JS will read it from there.
    </script>

    <!-- moved JS to public/js/reset-password.js -->
    <script src="{{ asset('js/reset-password.js') }}" defer></script>

    <!-- moved inline styles to public/css/resetpass.css for responsive handling -->

@endsection
