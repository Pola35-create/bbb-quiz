let playerName = "";
let questions = [];
let currentQIndex = 0;
let totalScore = 0;
let timerInterval = null;
let timeLeft = 15;
let isAnsweringBlocked = false;

const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

function playBeep(freq, duration) {
    try {
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.value = freq;
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start();
        gain.gain.exponentialRampToValueAtTime(0.00001, audioCtx.currentTime + duration);
        osc.stop(audioCtx.currentTime + duration);
    } catch (e) {}
}

function showView(viewId) {
    document.querySelectorAll('.view').forEach(el => el.classList.remove('active'));
    document.getElementById(viewId).classList.add('active');
}

async function startGame() {
    const input = document.getElementById('player-name-input');
    if (!input.value.trim()) {
        alert('Kérjük, add meg a neved a kezdéshez!');
        return;
    }
    playerName = input.value.trim();
    totalScore = 0;
    currentQIndex = 0;

    try {
        const res = await fetch('api.php?action=get_questions');
        questions = await res.json();

        if (!questions || questions.length === 0) {
            alert('Hiba történt a kérdések betöltésekor.');
            return;
        }

        showView('view-quiz');
        loadQuestion();
    } catch (e) {
        alert('Hálózati hiba a kérdések betöltésekor.');
    }
}

function loadQuestion() {
    if (currentQIndex >= 10) {
        finishGame();
        return;
    }

    isAnsweringBlocked = false;
    const q = questions[currentQIndex];

    document.getElementById('q-number').innerText = `${currentQIndex + 1}/10`;
    document.getElementById('score-text').innerText = totalScore;
    document.getElementById('question-text').innerText = q.question;

    const grid = document.getElementById('answers-grid');
    grid.innerHTML = '';

    q.answers.forEach((ans, idx) => {
        const btn = document.createElement('button');
        btn.className = 'answer-btn';
        btn.innerText = ans;
        btn.onclick = () => handleAnswer(idx, q.id, btn);
        grid.appendChild(btn);
    });

    startTimer();
}

function startTimer() {
    clearInterval(timerInterval);
    timeLeft = 15;
    updateTimerDisplay();

    const startTime = Date.now();
    const duration = 15000;

    timerInterval = setInterval(() => {
        const elapsed = Date.now() - startTime;
        const remaining = Math.max(0, duration - elapsed);
        timeLeft = remaining / 1000;

        updateTimerDisplay();

        if (remaining <= 0) {
            clearInterval(timerInterval);
            handleTimeout();
        }
    }, 50);
}

function updateTimerDisplay() {
    const timerBar = document.getElementById('timer-bar');
    const timeText = document.getElementById('time-text');

    const percentage = (timeLeft / 15) * 100;
    timerBar.style.width = `${percentage}%`;
    timeText.innerText = `${Math.ceil(timeLeft)}s`;
}

async function handleAnswer(selectedIndex, qId, clickedBtn) {
    if (isAnsweringBlocked) return;
    isAnsweringBlocked = true;
    clearInterval(timerInterval);

    const timeEarnedRatio = Math.max(0, timeLeft / 15);
    const questionScore = Math.round(500 * timeEarnedRatio);

    try {
        const res = await fetch('api.php?action=check_answer', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ question_id: qId, answer_index: selectedIndex })
        });
        const data = await res.json();
        const allBtns = document.querySelectorAll('.answer-btn');

        if (data.is_correct) {
            clickedBtn.classList.add('correct');
            totalScore += questionScore;
            document.getElementById('score-text').innerText = totalScore;
            playBeep(800, 0.2);
        } else {
            clickedBtn.classList.add('wrong');
            if (allBtns[data.correct_index]) {
                allBtns[data.correct_index].classList.add('correct');
            }
            playBeep(200, 0.3);
        }

        setTimeout(() => {
            currentQIndex++;
            loadQuestion();
        }, 1200);

    } catch (e) {
        console.error(e);
    }
}

function handleTimeout() {
    if (isAnsweringBlocked) return;
    isAnsweringBlocked = true;
    playBeep(180, 0.4);

    const q = questions[currentQIndex];
    fetch('api.php?action=check_answer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ question_id: q.id, answer_index: -1 })
    }).then(res => res.json()).then(data => {
        const allBtns = document.querySelectorAll('.answer-btn');
        if (allBtns[data.correct_index]) {
            allBtns[data.correct_index].classList.add('correct');
        }
        setTimeout(() => {
            currentQIndex++;
            loadQuestion();
        }, 1200);
    });
}

async function finishGame() {
    document.getElementById('res-player-name').innerText = playerName;
    document.getElementById('final-score').innerText = totalScore;

    showView('view-result');

    await fetch('api.php?action=save_score', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: playerName, score: totalScore })
    });
}

async function showLeaderboard() {
    try {
        const res = await fetch('api.php?action=get_leaderboard');
        const data = await res.json();

        const list = document.getElementById('leaderboard-list');
        list.innerHTML = '';

        if (data.length === 0) {
            list.innerHTML = '<li style="text-align:center; color: var(--text-dim); padding: 20px;">Még nincs mentett eredmény. Legyél te az első!</li>';
        } else {
            data.forEach((item, index) => {
                const li = document.createElement('li');
                li.className = 'leaderboard-item';
                li.innerHTML = `
                    <span class="rank">#${index + 1}</span>
                    <span class="player-name">${escapeHtml(item.name)}</span>
                    <span class="player-score">${item.score} pont</span>
                `;
                list.appendChild(li);
            });
        }

        showView('view-leaderboard');
    } catch (e) {
        alert('Hiba a ranglista betöltésekor.');
    }
}

function resetToHome() {
    document.getElementById('player-name-input').value = '';
    showView('view-home');
}

function escapeHtml(text) {
    return text.replace(/[&<>"']/g, function(m) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[m];
    });
}