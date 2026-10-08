<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Burger || Blues || Beer Kvíz</title>
    <link rel="icon" type="image/jpeg" href="logo.jpg" sizes="32x32">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">
    <div class="header">
        <img src="logo.jpg" alt="BBB Logo" class="header-logo">
        <div class="header-text">
            <h1>Burger || Blues || Beer</h1>
            <p>Szeged • Erzsébet-Liget</p>
        </div>
    </div>

    <!-- HOME VIEW -->
    <div id="view-home" class="view active">
        <div class="form-group">
            <label for="player-name-input">Adja meg a nevét a játékhoz:</label>
            <input type="text" id="player-name-input" placeholder="Pl. Burger Mester" maxlength="20" autocomplete="off">
        </div>
        <button class="btn btn-primary" onclick="startGame()">Játék Indítása 🍔</button>
        <button class="btn btn-secondary" onclick="showLeaderboard()">Ranglista Megtekintése 🏆</button>
    </div>

    <!-- QUIZ VIEW -->
    <div id="view-quiz" class="view">
        <div class="game-status">
            <div class="stat-box">
                <div class="stat-label">Kérdés</div>
                <div class="stat-value" id="q-number">1/10</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Hátralévő idő</div>
                <div class="stat-value" id="time-text">15s</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Pontszám</div>
                <div class="stat-value" id="score-text">0</div>
            </div>
        </div>

        <div class="timer-container">
            <div id="timer-bar" class="timer-bar"></div>
        </div>

        <div class="question-text" id="question-text">Kérdés betöltése...</div>

        <div class="answers-grid" id="answers-grid"></div>
    </div>

    <!-- RESULT VIEW -->
    <div id="view-result" class="view">
        <h2 style="text-align: center;">Gratulálunk, <span id="res-player-name"></span>!</h2>
        <p style="text-align: center; color: var(--text-dim); margin-top: 5px;">Végeredményed a kvízben:</p>
        
        <div class="result-score-display">
            <div class="result-score-number" id="final-score">0</div>
            <div style="color: var(--accent-blue); font-weight: bold;">ÖSSZES PONTSZÁM</div>
        </div>

        <button class="btn btn-primary" onclick="showLeaderboard()">Ranglista Élőben 🏆</button>
        <button class="btn btn-secondary" onclick="resetToHome()">Új Játék 🔄</button>
    </div>

    <!-- LEADERBOARD VIEW -->
    <div id="view-leaderboard" class="view">
        <h2 style="text-align: center; margin-bottom: 15px;">Dicsőségcsarnok 🏆</h2>
        <ul class="leaderboard-list" id="leaderboard-list"></ul>
        <button class="btn btn-primary" onclick="resetToHome()">Vissza a Főoldalra</button>
    </div>
</div>

<script src="js/app.js"></script>
</body>
</html>