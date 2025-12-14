

<?php $__env->startSection('content'); ?>
    <link href="<?php echo e(asset('css/asset.css')); ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Prevent scrolling - larger login design */
        body {
            overflow: hidden;
        }
        .login-container { 
            width: 100%; 
            max-width: 500px; 
            padding: 2rem !important;
        }
        .login-btn { 
            width: 100%;
            padding: 0.75rem;
            font-size: 1.1rem;
        }
        .password-toggle { cursor: pointer; }
        .logo-img { 
            max-width: 30%; 
            height: auto; 
            margin-bottom: 0.75rem !important;
        }
        .custom-title {
            font-size: 1.2rem;
            margin-bottom: 0.5rem !important;
            line-height: 1.3;
        }
        h3 {
            font-size: 1.8rem;
            margin-bottom: 0.75rem !important;
        }
        .mb-3 {
            margin-bottom: 0.75rem !important;
        }
        .mt-3 {
            margin-top: 0.75rem !important;
            font-size: 1rem;
        }
        .login-wrapper {
            height: 100vh;
            overflow: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 0;
        }
        .alert {
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }
        .form-control {
            padding: 0.75rem;
            font-size: 1rem;
        }
        .footer-spacer {
            display: none;
        }
        footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            z-index: 1000;
        }
        @media (max-width: 576px) {
            .login-container { 
                box-shadow: none !important; 
                padding: 1.5rem !important; 
                margin: 0.5rem; 
                border-radius: 10px; 
            }
            .custom-title { font-size: 1rem; }
            .logo-img { max-width: 28%; margin-bottom: 0.5rem !important; }
            .form-control { font-size: 0.95rem; padding: 0.65rem; }
            h3 { font-size: 1.5rem; margin-bottom: 0.6rem !important; }
            .login-btn { font-size: 1rem; padding: 0.65rem; }
        }
        @media (max-height: 700px) {
            .logo-img { max-width: 25%; margin-bottom: 0.5rem !important; }
            .custom-title { font-size: 1rem; margin-bottom: 0.4rem !important; }
            h3 { font-size: 1.4rem; margin-bottom: 0.5rem !important; }
            .login-container { padding: 1.5rem !important; }
            .mb-3 { margin-bottom: 0.6rem !important; }
            .mt-3 { margin-top: 0.6rem !important; font-size: 0.9rem; }
            .alert { padding: 0.6rem; font-size: 0.9rem; }
            .form-control { padding: 0.6rem; }
            .login-btn { padding: 0.6rem; font-size: 1rem; }
        }
        @media (max-height: 600px) {
            .logo-img { max-width: 22%; margin-bottom: 0.4rem !important; }
            .custom-title { font-size: 0.9rem; }
            h3 { font-size: 1.2rem; }
            .login-wrapper { padding: 0.5rem 0; }
            .login-container { padding: 1rem !important; }
        }
    </style>
<div class="login-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
                <div class="login-container shadow rounded bg-white text-center">
        <img src="<?php echo e(asset('Pup/puplogo.png')); ?>" class="img-fluid mb-3 logo-img" alt="PUP Logo">
        <h2 class="custom-title">Supply Management Information System</h2>
        <h3 class="mb-3">Login</h3>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger" role="alert">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <p class="mb-0"><?php echo e($error); ?></p>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <?php if(session('success')): ?>
            <div class="alert alert-success" role="alert">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if(session('login_attempts') && session('login_attempts') < 3): ?>
            <div class="alert alert-warning" role="alert">
                You have <?php echo e(3 - session('login_attempts')); ?> login attempts remaining.
            </div>
        <?php endif; ?>

        <?php if(session('cooldown_until')): ?>
            <?php
                // compute unix timestamp and initial remaining seconds
                $untilTs = \Carbon\Carbon::parse(session('cooldown_until'))->timestamp;
                $nowTs = \Carbon\Carbon::now()->timestamp;
                $initialLeft = max(0, $untilTs - $nowTs);
            ?>
            <div class="alert alert-warning" id="cooldown-alert" role="alert" data-until="<?php echo e($untilTs); ?>">
                Please wait <span id="countdown"><?php echo e($initialLeft); ?></span> seconds before trying again.
            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('login')); ?>" method="post" id="login-form" autocomplete="on">
            <?php echo csrf_field(); ?>
            <?php echo view('honeypot::honeypotFormFields'); ?>
           <div class="mb-3">
    <input type="text" class="form-control" name="identifier" 
           placeholder="Office or Email" 
           required autofocus autocomplete="username"
           inputmode="email" autocapitalize="none" spellcheck="false">
   
</div>
            <div class="mb-3 input-group">
                <input type="password" class="form-control" name="password" id="login-password" placeholder="Password" required autocomplete="current-password" aria-label="Password" inputmode="text" autocapitalize="none" spellcheck="false">
                <button type="button" class="input-group-text password-toggle" aria-label="Show or hide password" aria-pressed="false" onclick="togglePassword('login-password')" tabindex="0">
                    <i class="fas fa-eye toggle-password"></i>
                </button>
            </div>
            <button type="submit" class="login-btn" id="login-btn" <?php if(session('cooldown_until')): ?> disabled <?php endif; ?>>
                <span id="login-btn-text">Login</span>
                <span id="login-btn-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>
        </form>

        <div class="mt-3">
            <span> Can't remember? Press it --> </span>
            <a href="<?php echo e(route('password.request')); ?>" class="text-decoration-none" tabindex="0">Forgot Password?</a>
        </div>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="footer-spacer"></div>
<footer>
    <div class="footer">
<p>SMIS Website</p>
<p>Developed by Astral Angels 2025. All rights reserved</p>
</div>
</footer>

<script>
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        // Support for icon inside button
        let icon = null;
        if (input.nextElementSibling && input.nextElementSibling.querySelector('.toggle-password')) {
            icon = input.nextElementSibling.querySelector('.toggle-password');
        } else if (input.nextElementSibling && input.nextElementSibling.classList.contains('toggle-password')) {
            icon = input.nextElementSibling;
        }
        if (!icon) return;
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Show spinner on login button when submitting
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('login-form');
        const loginBtn = document.getElementById('login-btn');
        const btnText = document.getElementById('login-btn-text');
        const btnSpinner = document.getElementById('login-btn-spinner');
        if (loginForm && loginBtn && btnText && btnSpinner) {
            loginForm.addEventListener('submit', function() {
                btnText.classList.add('d-none');
                btnSpinner.classList.remove('d-none');
                loginBtn.disabled = true;
            });
        }
    });

    // Auto dismiss alerts after 3 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            }, 3000);
        });
    });

    // Cooldown timer functionality (uses server timestamp to keep client in sync)
    document.addEventListener('DOMContentLoaded', function() {
        const LS_KEY = 'smis_cooldown_until';
        const loginForm = document.getElementById('login-form');
        const submitButton = loginForm ? loginForm.querySelector('button[type="submit"]') : null;

        // read server element if present
        const serverAlert = document.getElementById('cooldown-alert');
        const serverUntil = serverAlert ? parseInt(serverAlert.getAttribute('data-until'), 10) : null;

        // read localStorage persisted cooldown (anti-refresh)
        let storedUntil = parseInt(localStorage.getItem(LS_KEY), 10);
        if (isNaN(storedUntil)) storedUntil = null;

        // If server provided a cooldown and it's later than stored, overwrite stored
        if (serverUntil && (!storedUntil || serverUntil > storedUntil)) {
            localStorage.setItem(LS_KEY, serverUntil);
            storedUntil = serverUntil;
        }

        // Authoritative timestamp to use (localStorage takes precedence if present)
        const authoritativeUntil = storedUntil;

        // helper to (re)create alert UI if server didn't render it (so refresh doesn't clear UI)
        function ensureCooldownAlert(untilTs) {
            let alert = document.getElementById('cooldown-alert');
            const nowTs = Math.floor(Date.now() / 1000);
            const left = Math.max(0, untilTs - nowTs);
            if (!alert) {
                alert = document.createElement('div');
                alert.className = 'alert alert-warning';
                alert.id = 'cooldown-alert';
                alert.setAttribute('role', 'alert');
                alert.setAttribute('data-until', untilTs);
                alert.innerHTML = 'Please wait <span id="countdown">' + left + '</span> seconds before trying again.';
                // insert before the form so it appears in same place as original
                if (loginForm && loginForm.parentNode) {
                    loginForm.parentNode.insertBefore(alert, loginForm);
                } else {
                    document.body.appendChild(alert);
                }
            } else {
                alert.setAttribute('data-until', untilTs);
                const countdownEl = document.getElementById('countdown');
                if (countdownEl) countdownEl.textContent = left;
            }
            return alert;
        }

        // start countdown using authoritativeUntil; persist in localStorage so refresh keeps it
        if (authoritativeUntil && submitButton) {
            // ensure UI exists
            const alertEl = ensureCooldownAlert(authoritativeUntil);

            // disable the submit button immediately
            submitButton.disabled = true;

            let timeLeft = Math.max(0, authoritativeUntil - Math.floor(Date.now() / 1000));
            const countdownEl = document.getElementById('countdown');
            if (countdownEl) countdownEl.textContent = timeLeft;

            const interval = setInterval(() => {
                timeLeft = Math.max(0, authoritativeUntil - Math.floor(Date.now() / 1000));
                if (countdownEl) countdownEl.textContent = timeLeft;

                if (timeLeft <= 0) {
                    clearInterval(interval);
                    // clear persisted cooldown so refresh won't restart it
                    localStorage.removeItem(LS_KEY);
                    // remove alert from DOM
                    const existing = document.getElementById('cooldown-alert');
                    if (existing && existing.parentNode) existing.parentNode.removeChild(existing);
                    // re-enable submit
                    submitButton.disabled = false;
                }
            }, 1000);
        } else {
            // no authoritative cooldown: ensure stored key cleared if expired
            if (storedUntil && storedUntil <= Math.floor(Date.now() / 1000)) {
                localStorage.removeItem(LS_KEY);
            }
            // if server didn't render alert and no authoritative cooldown, ensure button enabled
            if (submitButton) submitButton.disabled = false;
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
    const identifierField = document.querySelector('input[name="identifier"]');
    const loginForm = document.getElementById('login-form');
    
    if (identifierField && loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const value = identifierField.value.trim();
            
            // Only validate if it looks like an email (contains @)
            if (value.includes('@')) {
                if (!value.toLowerCase().endsWith('@gmail.com')) {
                    e.preventDefault();
                    alert('If using email to login, only Gmail accounts are accepted.');
                    identifierField.focus();
                }
            }
        });
    }
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Web Dev 3-1\Activity 1_Dela Cruz\SMIS40\resources\views/auth/login.blade.php ENDPATH**/ ?>