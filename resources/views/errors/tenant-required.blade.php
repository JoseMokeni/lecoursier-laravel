<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Required</title>
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
            color: #f0ad4e;
            margin-bottom: 20px;
        }

        p {
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            background: #5bc0de;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: bold;
        }

        .btn:hover {
            background: #46b8da;
        }
    </style>
</head>

<body>
    <div class="error-container">
        <h1>Tenant ID Required</h1>
        <p>A tenant ID is required to access this resource.</p>
        <p>Please ensure you're accessing the application through the correct URL or contact your administrator for
            assistance.</p>
        <a href="{{ url('/') }}" class="btn">Return Home</a>
    </div>
</body>

</html>
