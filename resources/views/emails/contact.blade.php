<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau message de contact - Le Coursier</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 0;
            background-color: #f9f9f9;
        }

        .container {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin: 20px;
        }

        .header {
            background-color: #1a56db;
            background-image: linear-gradient(135deg, #1a56db, #3182ce);
            color: white;
            padding: 25px 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .logo {
            margin-bottom: 15px;
        }

        .logo img {
            height: 40px;
        }

        .content {
            padding: 30px 25px;
            background-color: #ffffff;
        }

        .field {
            margin-bottom: 20px;
            border-bottom: 1px solid #edf2f7;
            padding-bottom: 15px;
        }

        .field:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #2d3748;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .value {
            padding: 5px 0;
            color: #4a5568;
            line-height: 1.6;
        }

        .footer {
            background-color: #f8fafc;
            font-size: 13px;
            color: #718096;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #edf2f7;
        }

        .footer p {
            margin: 5px 0;
        }

        .highlight {
            background-color: #ebf4ff;
            border-left: 4px solid #1a56db;
            padding: 10px 15px;
            margin: 5px 0;
        }

        @media only screen and (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 5px;
            }

            .content {
                padding: 20px 15px;
            }

            .header {
                padding: 20px 15px;
            }

            .header h1 {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <!-- You can add your logo here -->
                <!-- <img src="{{ asset('images/logo.png') }}" alt="Le Coursier" /> -->
                <strong>LE COURSIER</strong>
            </div>
            <h1>Nouveau message de contact</h1>
        </div>

        <div class="content">
            <div class="field">
                <div class="label">Nom</div>
                <div class="value">{{ $data['name'] }}</div>
            </div>

            <div class="field">
                <div class="label">Email</div>
                <div class="value">{{ $data['email'] }}</div>
            </div>

            @if (!empty($data['phone']))
                <div class="field">
                    <div class="label">Téléphone</div>
                    <div class="value">{{ $data['phone'] }}</div>
                </div>
            @endif

            <div class="field">
                <div class="label">Sujet</div>
                <div class="value highlight">
                    @switch($data['subject'])
                        @case('demo')
                            Demande de démonstration
                        @break

                        @case('pricing')
                            Informations sur les tarifs
                        @break

                        @case('support')
                            Support technique
                        @break

                        @default
                            Autre demande
                    @endswitch
                </div>
            </div>

            <div class="field">
                <div class="label">Message</div>
                <div class="value">{{ nl2br(e($data['message'])) }}</div>
            </div>
        </div>

        <div class="footer">
            <p>Ce message a été envoyé depuis le formulaire de contact de Le Coursier.</p>
            <p>&copy; {{ date('Y') }} Le Coursier. Tous droits réservés.</p>
        </div>
    </div>
</body>

</html>
