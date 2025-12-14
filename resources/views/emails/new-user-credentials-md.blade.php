@component('mail::message')

{{-- Styled header with optional logo --}}
@slot('header')
<table role="presentation" style="width:100%;border-collapse:collapse;margin-bottom:8px;">
<tr>
<td style="background:linear-gradient(90deg,#0ea5e9,#6366f1);padding:18px;text-align:center;border-radius:6px;color:#ffffff;">
    @if(file_exists(public_path('images/logo.png')))
        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" style="max-width:72px;height:auto;display:block;margin:0 auto 8px;border-radius:6px;background:rgba(255,255,255,0.06);padding:6px;">
    @endif
    <div style="font-weight:700;font-size:18px;">Welcome to {{ config('app.name') }}</div>
    <div style="font-size:12px;opacity:0.95;margin-top:6px;">Your account is ready — get started securely</div>
</td>
</tr>
</table>
@endslot

{{-- CSS-only timer to hide button after 5 minutes (300s) and show an expired notice --}}
<style>
@keyframes hideAfter5m { to { opacity:0; max-height:0; margin:0; padding:0; visibility:hidden; } }
@keyframes showAfter5m { to { opacity:1; max-height:40px; margin-top:10px; } }
</style>

<div style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;color:#0f172a;">

<p style="margin:0 0 12px;">Hello <strong>{{ $userName }}</strong>,</p>

<p style="margin:0 0 18px;color:#374151;">Your account has been created. Below are your login details — change your password immediately after first login for security.</p>

{{-- Credential card --}}
<div style="border-radius:8px;border:1px solid #e6eef8;padding:12px;background:#f8fbff;margin-bottom:16px;">
    <table role="presentation" style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="vertical-align:middle;padding:8px 10px;width:34%;font-weight:700;color:#1f2937;">Account Credentials</td>
            <td style="text-align:right;padding:8px 10px;vertical-align:middle;">
                <div style="display:inline-block;background:#f1f5f9;padding:6px 10px;border-radius:999px;font-family:monospace;color:#0f172a;font-size:13px;margin-right:8px;">{{ $userEmail }}</div>
                <div style="display:inline-block;background:#fef3c7;padding:6px 10px;border-radius:999px;font-family:monospace;color:#92400e;font-size:13px;">{{ $userPassword }}</div>
            </td>
        </tr>
    </table>
    <div style="margin-top:8px;font-size:12px;color:#6b7280;">Tip: keep these credentials private and change your password on first sign-in.</div>
</div>

{{-- CTA with spacing (hide after 5 minutes) --}}
<div class="hide-after-5m" style="text-align:center;margin:10px 0 18px;overflow:hidden;animation:hideAfter5m 0s linear 300s forwards;">
@component('mail::button', ['url' => ($resetUrl ?? url('forgetpass'))])
Reset Password
@endcomponent
</div>

{{-- Visible raw reset URL for copy/paste (like your previous template) --}}
<div style="text-align:center;margin:6px 0 18px;word-break:break-all;">
    @if(!empty($resetUrl))
        <div style="font-size:12px;color:#6b7280;margin-top:6px;">Link expires in 10 minutes.</div>
    @else
        <div style="font-size:13px;color:#6b7280;">If the reset link does not appear, please request a new password reset from the app.</div>
    @endif
</div>


{{-- Security box --}}
<div style="border-left:3px solid #eef2ff;background:#fbfdff;padding:12px;border-radius:6px;color:#374151;margin-bottom:12px;">
<strong style="display:block;margin-bottom:6px;">Security tips</strong>
<ul style="margin:0;padding-left:18px;color:#6b7280;">
<li>Change your password immediately.</li>
<li>Press the forget password link on the email.</li>
<li>Do not share your password via email or chat.</li>
</ul>
</div>

<p style="margin:0 0 6px;color:#9ca3af;">
    If you did not request this account, contact
    <a href="mailto:smispupttaguig@gmail.com?subject=Unauthorized%20Account%20Request&body={{ urlencode('I did not request this account for ' . ($userEmail ?? '')) }}" style="color:#2563eb;text-decoration:none;">smispupttaguig@gmail.com</a>
</p>

<p style="margin:8px 0 0;color:#9ca3af;font-size:13px;">Thanks,<br>{{ config('app.name') }} Team</p>

</div>

@endcomponent
