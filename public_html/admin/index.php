<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';

// Get statistics
$projectCount = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$mediaCount = $pdo->query("SELECT COUNT(*) FROM media")->fetchColumn();
$storyCount = $pdo->query("SELECT COUNT(*) FROM media WHERE story IS NOT NULL AND story != ''")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #f5f5f5; }
        .card { background: white; border: 1px solid #e0e0e0; }
        .btn { background: #2d2d2d; color: white; padding: 0.5rem 1rem; }
        .btn:hover { background: #000; }
        .btn-secondary { background: #6b6b6b; }
        .btn-secondary:hover { background: #4a4a4a; }
    </style>
</head>
<body class="p-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-light">Admin Dashboard</h1>
            <a href="logout.php" class="text-gray-600 hover:text-black">Logout</a>
        </div>
        
        <div class="grid grid-cols-3 gap-6 mb-8">
            <div class="card p-6 text-center">
                <div class="text-3xl font-light"><?= $projectCount ?></div>
                <div class="text-gray-600 mt-2">Projects</div>
            </div>
            <div class="card p-6 text-center">
                <div class="text-3xl font-light"><?= $mediaCount ?></div>
                <div class="text-gray-600 mt-2">Total Media Files</div>
            </div>
            <div class="card p-6 text-center">
                <div class="text-3xl font-light"><?= $storyCount ?></div>
                <div class="text-gray-600 mt-2">Stories Written</div>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-6 mb-8">
            <div class="card p-6">
                <h2 class="text-xl font-medium mb-4">Quick Actions</h2>
                <div class="space-y-3">
                    <a href="upload.php" class="btn inline-block w-full text-center">Upload New Media</a>
                    <a href="add-project.php" class="btn btn-secondary inline-block w-full text-center">Add New Project</a>
                </div>
            </div>
            
            <div class="card p-6">
                <h2 class="text-xl font-medium mb-4">Recent Media</h2>
                <div id="recent-media" class="space-y-2">
                    Loading...
                </div>
            </div>
        </div>
        
        <div class="card p-6">
            <h2 class="text-xl font-medium mb-4">Media Without Stories</h2>
            <div id="needs-story" class="space-y-2">
                Loading...
            </div>
        </div>
    </div>
    
    <script>
        async function loadRecentMedia() {
            const response = await fetch('/api/gallery.php');
            const result = await response.json();
            if (result.success && result.data.length > 0) {
                const recent = result.data.slice(0, 5);
                document.getElementById('recent-media').innerHTML = recent.map(item => `
                    <div class="flex justify-between items-center border-b border-gray-100 py-2">
                        <span>${item.project_title}</span>
                        <a href="edit-story.php?id=${item.id}" class="text-gray-600 hover:text-black text-sm">Edit Story</a>
                    </div>
                `).join('');
            }
        }
        
        loadRecentMedia();
    </script>
</body>
</html>