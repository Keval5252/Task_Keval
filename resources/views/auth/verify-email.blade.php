<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
</head>
<body>
    <h1>Verify Your Email Address</h1>
    <p>A verification link has been sent to your email address. Please check your inbox.</p>
    <form action="{{ route('verification.resend') }}" method="POST">
        @csrf
        <button type="submit">Resend Verification Email</button>
    </form>
</body>
</html>
