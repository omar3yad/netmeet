<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>NetMeet - Offline</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: -apple-system, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #00bef2 0%, #0099c8 100%);
            color: #fff;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .box {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 40px 30px;
            border-radius: 20px;
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        h1 { margin: 0 0 12px; font-size: 24px; }
        p { margin: 0 0 24px; opacity: 0.9; line-height: 1.5; }
        button {
            background: #fff;
            color: #0099c8;
            border: none;
            padding: 14px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }
        .icon { font-size: 48px; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon">📡</div>
        <h1>{{ __('No Internet Connection') }}</h1>
        <p>{{ __('Please check your connection and try again') }}</p>
        <button onclick="window.location.href='/'">{{ __('Try Again') }}</button>
    </div>
    <script>
        // Auto-retry every 3 seconds
        setInterval(() => {
            if (navigator.onLine) {
                window.location.href = '/';
            }
        }, 3000);
    </script>
</body>
</html>
