<?php
header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['action'])) {
    echo json_encode(['error' => 'No action specified']);
    exit;
}

// Absolute paths to JSON files in the data directory
$questionsFile = __DIR__ . '/../data/questions.json';
$leaderboardFile = __DIR__ . '/../data/leaderboard.json';

// 1. Return 10 random questions
if ($_GET['action'] === 'get_questions') {
    if (!file_exists($questionsFile)) {
        echo json_encode(['error' => 'Questions file missing']);
        exit;
    }
    $data = json_decode(file_get_contents($questionsFile), true);
    shuffle($data);
    $selected = array_slice($data, 0, 10);

    $clientQuestions = array_map(function($q) {
        return [
            'id' => $q['id'],
            'question' => $q['question'],
            'answers' => $q['answers']
        ];
    }, $selected);

    session_start();
    $_SESSION['active_questions'] = $selected;

    echo json_encode($clientQuestions);
    exit;
}

// 2. Validate selected answer
if ($_GET['action'] === 'check_answer' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    $input = json_decode(file_get_contents('php://input'), true);
    $qId = intval($input['question_id'] ?? 0);
    $selectedIndex = intval($input['answer_index'] ?? -1);

    $correctIndex = -1;
    if (isset($_SESSION['active_questions'])) {
        foreach ($_SESSION['active_questions'] as $q) {
            if ($q['id'] === $qId) {
                $correctIndex = $q['correct'];
                break;
            }
        }
    }

    echo json_encode([
        'is_correct' => ($selectedIndex === $correctIndex),
        'correct_index' => $correctIndex
    ]);
    exit;
}

// 3. Save new highscore
if ($_GET['action'] === 'save_score' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $name = trim(htmlspecialchars($input['name'] ?? 'Névtelen'));
    $score = intval($input['score'] ?? 0);

    if (empty($name)) $name = 'Névtelen';

    $leaderboard = [];

    if (file_exists($leaderboardFile)) {
        $leaderboard = json_decode(file_get_contents($leaderboardFile), true) ?? [];
    }

    $leaderboard[] = [
        'name' => $name,
        'score' => $score,
        'date' => date('Y-m-d H:i')
    ];

    usort($leaderboard, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });

    $leaderboard = array_slice($leaderboard, 0, 100);

    // Save and verify write success
    $result = file_put_contents($leaderboardFile, json_encode($leaderboard, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    if ($result === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to write to leaderboard file. Check folder write permissions.']);
        exit;
    }

    echo json_encode(['success' => true]);
    exit;
}

// 4. Retrieve leaderboard
if ($_GET['action'] === 'get_leaderboard') {
    $leaderboard = [];
    if (file_exists($leaderboardFile)) {
        $leaderboard = json_decode(file_get_contents($leaderboardFile), true) ?? [];
    }
    echo json_encode($leaderboard);
    exit;
}