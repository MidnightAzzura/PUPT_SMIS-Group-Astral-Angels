

<?php $__env->startSection('content'); ?>
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo e(asset('css/ForgetPass.css')); ?>">

<main>
    <div class="forgot-password-container">
        <h2 class="forgot-password-heading">Forgot Password</h2>
        <p class="instruction-text">Enter your email address below and we'll send you a link to reset your password.</p>
        <form action="<?php echo e(route('password.email')); ?>" method="POST" id="forgot-password-form" autocomplete="off">
            <?php echo csrf_field(); ?>
            <div class="input-group">
                <div class="input-container">
                    <span class="input-icon">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input 
                        type="email" 
                        class="form-control modern-input" 
                        name="email" 
                        id="email"
                        placeholder="Email Address" 
                        required 
                        aria-label="Email Address"
                        pattern="[a-zA-Z0-9._%+-]+@gmail\.com$"
                        autocomplete="off"
                    >
                </div>
                <div class="validation-feedback" id="email-feedback"></div>
            </div>
            <button type="submit" class="btn btn-reset" id="submit-btn">
                <span>Send Reset Link</span>
                <i class="fas fa-arrow-right"></i>
            </button>
            <a href="<?php echo e(route('login')); ?>" class="back-to-login">
                <i class="fas fa-chevron-left"></i> Back to Login
            </a>
        </form>
    </div>
</main>


    <footer>
        <div class="footer">
            <p>SMIS Website</p>
            <p>Developed by Astral Angels 2025. All rights reserved</p>
        </div>
    </footer>


<script>
    // SweetAlert2 for validation errors
    <?php if($errors->any()): ?>
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: <?php echo json_encode($error, 15, 512) ?>,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>

    // SweetAlert2 for session success
    <?php if(session('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: <?php echo json_encode(session('success'), 15, 512) ?>,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    <?php endif; ?>

    // SweetAlert2 for session error
    <?php if(session('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: <?php echo json_encode(session('error'), 15, 512) ?>,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    <?php endif; ?>

    // Enhanced email validation with real-time feedback
    document.addEventListener('DOMContentLoaded', function() {
        const emailField = document.getElementById('email');
        const emailFeedback = document.getElementById('email-feedback');
        const submitBtn = document.getElementById('submit-btn');
        
        // Function to validate email format
        function validateEmail(email) {
            const gmailRegex = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;
            return gmailRegex.test(email);
        }
        
        // Real-time validation as user types
        emailField.addEventListener('input', function() {
            const email = this.value.trim();
            
            if (email === '') {
                emailFeedback.textContent = '';
                emailField.classList.remove('input-error');
                return;
            }
            
            if (!validateEmail(email)) {
                emailFeedback.textContent = 'Please enter a valid Gmail address';
                emailField.classList.add('input-error');
            } else {
                emailFeedback.textContent = '';
                emailField.classList.remove('input-error');
            }
        });
        
        // Validate on blur
        emailField.addEventListener('blur', function() {
            const email = this.value.trim();
            
            if (email && !validateEmail(email)) {
                emailFeedback.textContent = 'Only Gmail accounts are accepted';
                emailField.classList.add('input-error');
            }
        });
        
        // Form submission validation
        document.getElementById('forgot-password-form').addEventListener('submit', function(e) {
            const email = emailField.value.trim();
            
            if (!email || !validateEmail(email)) {
                e.preventDefault();
                emailFeedback.textContent = 'Please enter a valid Gmail address';
                emailField.classList.add('input-error');
                return false;
            }
            
            // Show loading state on button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Sending...</span><i class="fas fa-spinner fa-spin"></i>';
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u741082352/domains/smis.ojt-ims-bsit.net/public_html/resources/views/auth/ForgetPass.blade.php ENDPATH**/ ?>