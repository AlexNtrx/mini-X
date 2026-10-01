<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Huoltokatko - Mini X</title>
    <link rel="stylesheet" href="./css/main.css?v=1.1.2">
    <style>
        .maintenance-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
            text-align: center;
            background: #0f1419;
            color: #ffffff;
        }
        .maintenance-icon {
            font-size: 64px;
            margin-bottom: 20px;
            animation: bounce 2s infinite ease-in-out;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .maintenance-title {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 12px;
            color: #1d9bf0;
        }
        .maintenance-desc {
            font-size: 16px;
            color: #8b98a5;
            max-width: 480px;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .maintenance-btn {
            display: inline-block;
            padding: 10px 24px;
            background: #1d9bf0;
            color: #ffffff;
            border-radius: 9999px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: background 0.2s;
        }
        .maintenance-btn:hover {
            background: #1a8cd8;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-icon">🚧</div>
        <h1 class="maintenance-title">Palvelussa on huoltokatko</h1>
        <p class="maintenance-desc">
            Päivitämme parhaillaan Mini-X -palvelua tarjotaksemme entistä paremman käyttökokemuksen. 
            Palaamme takaisin pian! Kiitos kärsivällisyydestäsi.
        </p>
        <a href="index.php?page=logout" class="maintenance-btn">Kirjaudu ulos</a>
    </div>
</body>
</html>
