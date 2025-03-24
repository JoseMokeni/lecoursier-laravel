<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour du mot de passe - Le Coursier</title>
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
            <h1>Mise à jour du mot de passe</h1>
        </div>

        <div class="content">
            <p>Bonjour {{ $userData['name'] }},</p>

            <p>Votre mot de passe a été mis à jour avec succès sur la plateforme Le Coursier.</p>

            @if ($newPassword)
                <div class="credentials">
                    <p><strong>Code de l'entreprise:</strong> {{ $companyCode }}</p>
                    <p><strong>Nom d'utilisateur:</strong> {{ $userData['username'] }}</p>
                    <p><strong>Nouveau mot de passe:</strong> {{ $newPassword }}</p>
                </div>

                <p>Veuillez conserver ces informations en lieu sûr.</p>
            @else
                <p>Si vous n'avez pas demandé cette modification, veuillez contacter immédiatement l'administrateur de
                    votre compte.</p>
            @endif

            <p>Vous pouvez vous connecter en cliquant sur le bouton ci-dessous:</p>

            <p style="text-align: center;">
                <a href="{{ url('/') }}" class="btn">Se connecter</a>
            </p>

            <p>Si vous avez des questions ou besoin d'assistance, n'hésitez pas à nous contacter.</p>

            <p>Cordialement,<br>L'équipe Le Coursier</p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Le Coursier. Tous droits réservés.</p>
        </div>
    </div>
</body>

</html>
