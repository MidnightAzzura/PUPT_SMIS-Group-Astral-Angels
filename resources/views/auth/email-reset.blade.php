<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="x-apple-disable-message-reformatting">
    <title>Password Reset - SMIS</title>
    <!-- Preheader text (hidden but helps with deliverability) -->
    <span style="display: none !important; visibility: hidden; opacity: 0; color: transparent; height: 0; width: 0;">
        Password reset request for your SMIS account
    </span>
   <style>

    .header{
        background-color: #800000;
        color: #fff;
        width: 100%;
        padding: 10px 0;
        text-align: center;
        font-size: 25px;
        border-radius: 8px 8px 0 0;
    }

    .head img{
        max-height: 50px;
        margin-bottom: 5px;
    }


.message{
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 0px;
    text-align: center;
    justify-content: center;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    
}

body {
   font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
   background-color: #ffffff;
   color: #000000;
   margin: 0;
   padding: 20px;
   background-image: url('https://upload.wikimedia.org/wikipedia/en/thumb/f/f9/PUP_Taguig_Campus_-_Main_Building_%28Gen._Santos_Ave.%2C_Bicutan%2C_Taguig%3B_2014-11-09%29.Jpg/1200px-PUP_Taguig_Campus_-_Main_Building_%28Gen._Santos_Ave.%2C_Bicutan%2C_Taguig%3B_2014-11-09%29.Jpg?20161127123544') no-repeat center center fixed;
        }

.email{

    color: #333333 !important;
    font-size: 14px;
    text-decoration: none;
}

a{
    display: inline-block;
    background-color: #800000;
    color: #ffffff !important;
    text-decoration: none;
    border-radius: 10px;
    padding: 10px 15px;
    margin-top: 10px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.3s ease;
    transform: translateY(-2px);
    transform: translateY(0);
}


    /* Footer */
.footer {
    position: fixed;
    font-family: "Times New Roman", Times, serif;
    background-color: #800000;
    color: #fff;
    width: 100%;
    padding: 10px 0;
    text-align: center;
    font-size: 14px;
    margin-top: 30px;
    border-radius: 0 0 8px 8px; /* Optional rounded corners */
}

</style>
</head>

<body>

    <div class="message">
        <div class="header">
            <img src="{{ asset('Pup/puplogo.png') }}" alt="PUP Logo">
        </div>
<p class="email" style="pointer-events: none; user-select: text;">
    Hello Mr/Ms. {{request('email')}}, this is from the SMIS System </p>

        @php
            // Build 10-min expiry and HMAC signature for token|email|expires
            $expires = now()->addMinutes(10)->timestamp;
            $rawKey = config('app.key');
            if (strpos($rawKey, 'base64:') === 0) {
                $rawKey = base64_decode(substr($rawKey, 7));
            }
            $sig = hash_hmac('sha256', $token . '|' . $email . '|' . $expires, $rawKey);
        @endphp

        <p class="email"> You requested a password reset. Click the link below to reset your password:</p>

        <a href="{{ route('email.reset', ['token' => $token, 'email' => $email, 'expires' => $expires, 'sig' => $sig]) }}">
            Reset Password
        </a>
        <p class="email">This link expires in 10 minutes.</p>

        <p class="email"> If you did not request this, you can ignore this email.</p>

    </div>

    <div class="footer">
        <p>SMIS Website</p>
        <p>Developed by Astral Angels 2025. All rights reserved</p>
        </div>
    </body>
</html>


