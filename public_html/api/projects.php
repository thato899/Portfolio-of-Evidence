<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once dirname(__DIR__) . '/includes/db.php';

try {
    $stmt = $pdo->query("
        SELECT id, title, description, date_created 
        FROM projects 
        ORDER BY date_created DESC
    ");
    $projects = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $projects
    ]);
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>