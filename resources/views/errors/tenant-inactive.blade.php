<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Inactive</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f5f5f5;
        }

        .error-container {
            max-width: 600px;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            color: #d9534f;
            margin-bottom: 20px;
        }

        p {
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            background: #5cb85c;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: bold;
        }

        .btn:hover {
            background: #4cae4c;
        }
    </style>
</head>

<body>
    <div class="error-container">
        <h1>Tenant Account Inactive</h1>
        <p>The tenant account you're trying to access is currently inactive.</p>
        <p>Please contact your administrator to activate this tenant account.</p>
        <a href="{{ url('/dashboard') }}" class="btn">Return Home</a>
    </div>
</body>

</html>
