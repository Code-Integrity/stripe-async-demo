<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Async Secure Demo</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #f6f9fc;
            margin: 0;
        }

        .card {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(50, 50, 93, .11), 0 1px 3px rgba(0, 0, 0, .08);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        h2 {
            color: #32325d;
            margin-bottom: 10px;
        }

        .price {
            font-size: 24px;
            color: #6772e5;
            font-weight: bold;
            margin-bottom: 20px;
        }

        button {
            background: #6772e5;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            transition: all 0.15s ease;
        }

        button:hover {
            background: #5469d4;
        }
    </style>
</head>

<body>
    <div class="card">
        <h2>{{ $item->name }}</h2>
        <div class="price">¥{{ number_format($item->price) }}</div>

        <form action="{{ route('purchase.checkout') }}" method="POST">
            @csrf
            <button type="submit">Secure Checkout via Stripe</button>
        </form>
    </div>
</body>

</html>