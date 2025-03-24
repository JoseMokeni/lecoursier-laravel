<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur Le Coursier</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #3b82f6;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .content {
            padding: 20px;
            background-color: #f9fafb;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.8em;
            color: #6b7280;
        }

        .credentials {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }

        .btn {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenue sur Le Coursier</h1>
        </div>

        <div class="content">
            <p>Bonjour {{ $userData['name'] }},</p>

            <p>Votre compte a été créé avec succès sur la plateforme Le Coursier. Voici vos informations de connexion:
            </p>

            <div class="credentials">
                <p><strong>Code de l'entreprise:</strong> {{ $companyCode }}</p>
                <p><strong>Nom d'utilisateur:</strong> {{ $userData['username'] }}</p>
                <p><strong>Mot de passe:</strong>
                    {{ isset($userData['original_password']) ? $userData['original_password'] : 'Celui que vous avez défini lors de la création' }}
                </p>
            </div>

            <p>Vous pouvez vous connecter en cliquant sur le bouton ci-dessous:</p>

            <p style="text-align: center;">
                <a href="{{ url('/') }}" class="btn">Se connecter</a>
            </p>

            <p>Pour des raisons de sécurité, nous vous recommandons de changer votre mot de passe après votre première
                connexion.</p>

            <p>Si vous avez des questions ou besoin d'assistance, n'hésitez pas à nous contacter.</p>

            <p>Cordialement,<br>L'équipe Le Coursier</p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Le Coursier. Tous droits réservés.</p>
        </div>
    </div>
</body>

</html>
