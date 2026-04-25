<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Access-Control-Allow-Origin: *');
require_once dirname(__DIR__) . '/includes/db.php';

try {
    // Simpler query without JOIN to debug
    $stmt = $pdo->prepare("
        SELECT * FROM media 
        WHERE filetype = 'image' 
        AND story IS NOT NULL 
        AND story != ''
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $images = $stmt->fetchAll();
    
    // Manually add project title if needed
    foreach ($images as &$img) {
        // Get project title separately
        $projStmt = $pdo->prepare("SELECT title FROM projects WHERE id = ?");
        $projStmt->execute([$img['project_id']]);
        $project = $projStmt->fetch();
        
        $img['project_title'] = $project ? $project['title'] : 'Unknown Project';
        $img['image_url'] = '/portfolio_of_evidence/uploads/images/' . $img['filename'];
        $img['story'] = htmlspecialchars($img['story']);
        $img['hyperlink'] = $img['hyperlink'] ?? null;
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($images),
        'data' => $images
    ]);
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>