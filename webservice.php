<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// require login
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not authenticated']);
        exit();
    }
}

// require teacher role
function require_teacher() {
    require_login();
    if ($_SESSION['role'] !== 'teacher') {
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }
}

switch ($action) {

    // get all concepts
    case 'get_concepts':
    require_login();
    $unit_id = $_GET['unit_id'] ?? null;

    if ($unit_id) {
        $stmt = $pdo->prepare('
            SELECT c.id, c.unit_id, c.term, c.definition, c.example, c.ai_prompt_template,
                   u.unit_number, u.name as unit_name
            FROM concepts c
            JOIN units u ON c.unit_id = u.id
            WHERE c.unit_id = ?
            ORDER BY c.term ASC
        ');
        $stmt->execute([$unit_id]);
    } else {
        $stmt = $pdo->prepare('
            SELECT c.id, c.unit_id, c.term, c.definition, c.example, c.ai_prompt_template,
                   u.unit_number, u.name as unit_name
            FROM concepts c
            JOIN units u ON c.unit_id = u.id
            ORDER BY u.unit_number ASC, c.term ASC
        ');
        $stmt->execute();
    }

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    break;

    // single concept
    case 'get_concept':
        require_login();
        $id = $_GET['id'] ?? null;
        if (!$id) { echo json_encode(['error' => 'No ID provided']); exit(); }

        $stmt = $pdo->prepare('
            SELECT c.id, c.term, c.definition, c.example, c.ai_prompt_template,
                   u.unit_number, u.name as unit_name
            FROM concepts c
            JOIN units u ON c.unit_id = u.id
            WHERE c.id = ?
        ');
        $stmt->execute([$id]);
        $concept = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$concept) { echo json_encode(['error' => 'Not found']); exit(); }
        echo json_encode($concept);
        break;

    // all units
    case 'get_units':
        require_login();
        $stmt = $pdo->prepare('SELECT * FROM units ORDER BY unit_number ASC');
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    // single unit
    case 'get_prompt':
        require_login();
        $id = $_GET['concept_id'] ?? null;
        if (!$id) { echo json_encode(['error' => 'No concept ID']); exit(); }

        $stmt = $pdo->prepare('SELECT term, ai_prompt_template FROM concepts WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !$row['ai_prompt_template']) {
            echo json_encode(['error' => 'No prompt available for this concept']);
            exit();
        }
        echo json_encode(['term' => $row['term'], 'prompt' => $row['ai_prompt_template']]);
        break;

    // add bookmark
    case 'add_bookmark':
        require_login();
        $concept_id = $_POST['concept_id'] ?? null;
        if (!$concept_id) { echo json_encode(['error' => 'No concept ID']); exit(); }

        // check not already bookmarked
        $stmt = $pdo->prepare('SELECT id FROM bookmarks WHERE user_id = ? AND concept_id = ?');
        $stmt->execute([$_SESSION['user_id'], $concept_id]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Already bookmarked']);
            exit();
        }

        $stmt = $pdo->prepare('INSERT INTO bookmarks (user_id, concept_id) VALUES (?, ?)');
        $stmt->execute([$_SESSION['user_id'], $concept_id]);
        echo json_encode(['success' => true, 'message' => 'Bookmarked']);
        break;

    // remove bookmark
    case 'remove_bookmark':
        require_login();
        $concept_id = $_POST['concept_id'] ?? null;
        if (!$concept_id) { echo json_encode(['error' => 'No concept ID']); exit(); }

        $stmt = $pdo->prepare('DELETE FROM bookmarks WHERE user_id = ? AND concept_id = ?');
        $stmt->execute([$_SESSION['user_id'], $concept_id]);
        echo json_encode(['success' => true, 'message' => 'Bookmark removed']);
        break;

    // get bookmarks
    case 'get_bookmarks':
        require_login();
        $stmt = $pdo->prepare('
            SELECT c.id, c.term, c.definition, u.unit_number, u.name as unit_name
            FROM bookmarks b
            JOIN concepts c ON b.concept_id = c.id
            JOIN units u ON c.unit_id = u.id
            WHERE b.user_id = ?
            ORDER BY u.unit_number ASC, c.term ASC
        ');
        $stmt->execute([$_SESSION['user_id']]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    // update
    case 'update_progress':
        require_login();
        $concept_id = $_POST['concept_id'] ?? null;
        $status     = $_POST['status'] ?? null;
        $allowed    = ['not_started', 'in_progress', 'mastered'];

        if (!$concept_id || !in_array($status, $allowed)) {
            echo json_encode(['error' => 'Invalid input']);
            exit();
        }

        // insert or update
        $stmt = $pdo->prepare('
            INSERT INTO progress (user_id, concept_id, status)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE status = ?, updated_at = CURRENT_TIMESTAMP
        ');
        $stmt->execute([$_SESSION['user_id'], $concept_id, $status, $status]);
        echo json_encode(['success' => true, 'status' => $status]);
        break;

    // progress
    case 'get_progress':
        require_login();
        $stmt = $pdo->prepare('
            SELECT c.id, c.term, u.unit_number, u.name as unit_name, p.status
            FROM progress p
            JOIN concepts c ON p.concept_id = c.id
            JOIN units u ON c.unit_id = u.id
            WHERE p.user_id = ?
            ORDER BY u.unit_number ASC, c.term ASC
        ');
        $stmt->execute([$_SESSION['user_id']]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    // teacher - add concept
    case 'add_concept':
        require_teacher();
        $unit_id  = $_POST['unit_id'] ?? null;
        $term     = trim($_POST['term'] ?? '');
        $def      = trim($_POST['definition'] ?? '');
        $example  = trim($_POST['example'] ?? '');
        $prompt   = trim($_POST['ai_prompt_template'] ?? '');

        if (!$unit_id || !$term || !$def) {
            echo json_encode(['error' => 'Unit, term and definition are required']);
            exit();
        }

        $stmt = $pdo->prepare('
            INSERT INTO concepts (unit_id, term, definition, example, ai_prompt_template)
            VALUES (?, ?, ?, ?, ?)
        ');
        $stmt->execute([$unit_id, $term, $def, $example, $prompt]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    // teacher - edit concept
    case 'edit_concept':
        require_teacher();
        $id      = $_POST['id'] ?? null;
        $unit_id = $_POST['unit_id'] ?? null;
        $term    = trim($_POST['term'] ?? '');
        $def     = trim($_POST['definition'] ?? '');
        $example = trim($_POST['example'] ?? '');
        $prompt  = trim($_POST['ai_prompt_template'] ?? '');

        if (!$id || !$unit_id || !$term || !$def) {
            echo json_encode(['error' => 'ID, unit, term and definition are required']);
            exit();
        }

        $stmt = $pdo->prepare('
            UPDATE concepts
            SET unit_id = ?, term = ?, definition = ?, example = ?, ai_prompt_template = ?
            WHERE id = ?
        ');
        $stmt->execute([$unit_id, $term, $def, $example, $prompt, $id]);
        echo json_encode(['success' => true]);
        break;

    // teacher - delete concept
    case 'delete_concept':
        require_teacher();
        $id = $_POST['id'] ?? null;
        if (!$id) { echo json_encode(['error' => 'No ID provided']); exit(); }

        $stmt = $pdo->prepare('DELETE FROM concepts WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Unknown action: ' . $action]);
        break;
}
?>

