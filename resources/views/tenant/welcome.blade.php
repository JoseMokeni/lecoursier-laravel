<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Tenant {{ $tenantId }}</title>
    <style>
        :root {
            --bg-color: #ffffff;
            --text-color: #333333;
            --card-bg: #f5f5f5;
            --card-border: #e0e0e0;
        }

        [data-theme="dark"] {
            --bg-color: #121212;
            --text-color: #e0e0e0;
            --card-bg: #1e1e1e;
            --card-border: #333333;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: all 0.3s ease;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            text-align: center;
        }

        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 8px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .theme-toggle {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            color: var(--text-color);
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .theme-toggle:hover {
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <button class="theme-toggle" id="theme-toggle">
        Toggle Dark/Light Mode
    </button>

    <div class="container">
        <h1>Welcome to Your Multi-tenant Application</h1>

        <div class="card">
            <h2>Tenant Information</h2>
            <p>You are currently viewing tenant: <strong>{{ $tenantId }}</strong></p>
        </div>
    </div>

    <script>
        // Check for saved theme preference or use user's system preference
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const savedTheme = localStorage.getItem('theme');

        // Apply the theme
        if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
            document.documentElement.setAttribute('data-theme', 'dark');
        }

        // Toggle theme function
        document.getElementById('theme-toggle').addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';

            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    </script>
</body>

</html>
