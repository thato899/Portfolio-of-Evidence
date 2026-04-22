<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once dirname(__DIR__) . '/includes/db.php';

try {
    $stmt = $pdo->prepare("
        SELECT m.*, p.title as project_title 
        FROM media m 
        LEFT JOIN projects p ON m.project_id = p.id 
        WHERE m.filetype = 'image' 
        AND m.story IS NOT NULL 
        AND m.story != ''
        ORDER BY m.display_order, m.created_at DESC
    ");
    $stmt->execute();
    $images = $stmt->fetchAll();
    
    foreach ($images as &$img) {
        // Updated path for subfolder
        $img['image_url'] = '/portfolio_of_evidence/uploads/images/' . $img['filename'];
        $img['story'] = htmlspecialchars($img['story']);
        $img['hyperlink'] = $img['hyperlink'] ?? null;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $images
    ]);
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>