<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Opening App...</title>

<style>
    body {
        margin: 0;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background: linear-gradient(135deg, #6a11cb, #2575fc);
        font-family: Arial, sans-serif;
        color: white;
    }

    .container {
        text-align: center;
        animation: fadeIn 1.2s ease-in-out;
    }

    .logo {
        width: 120px;
        height: auto;
        margin-bottom: 20px;
        border-radius: 18px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.25);
        background: white;
        padding: 10px;
    }

    h3 {
        margin: 20px 0 10px;
        font-size: 22px;
        letter-spacing: 1px;
    }

    .sub-text {
        font-size: 14px;
        opacity: 0.85;
        margin-bottom: 20px;
    }

    .loader {
        width: 60px;
        height: 60px;
        border: 6px solid rgba(255,255,255,0.3);
        border-top: 6px solid #fff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: auto;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
</head>

<body>

<div class="container">

    <!-- App Logo -->
    <img 
        src="{{ asset('assets/img/app_logo.png') }}" 
        alt="Famory App Logo" 
        class="logo"
    >

    <h3>Opening in App...</h3>
    <div class="sub-text">Please wait while we redirect you</div>

    <!-- Loader -->
    <div class="loader"></div>

</div>

<!-- <script>
    setTimeout(function () {
        window.location.href = "https://play.google.com/store/apps/details?id=io.famory.app";
    }, 2000);
</script> -->

<script>
(function () {

    var userAgent = navigator.userAgent || navigator.vendor || window.opera;

    function isIOS() {
        return /iPad|iPhone|iPod/.test(userAgent) && !window.MSStream;
    }

    function isAndroid() {
        return /android/i.test(userAgent);
    }

    function redirectFallback() {

        // Android fallback
        if (isAndroid()) {
            window.location.href =
                "https://play.google.com/store/apps/details?id=io.famory.app";
            return;
        }

        // iOS fallback
        if (isIOS()) {
            window.location.href =
                "https://apps.apple.com/app/87G8Z5TC3H.io.famory.app";
            return;
        }

        // Desktop fallback
        window.location.href = "https://famoryapp.com";
    }

    // iOS Universal Link attempt happens automatically.
    // If app not installed → fallback after delay
    setTimeout(redirectFallback, 2500);

})();
</script>



</body>
</html>
