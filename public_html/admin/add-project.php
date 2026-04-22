<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';

$success_message = null;
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($title)) {
        $error_message = 'Project title is required';
    } else {
        $stmt = $pdo->prepare("INSERT INTO projects (title, description) VALUES (?, ?)");
        $stmt->execute([$title, $description]);
        $success_message = 'Project created successfully';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Project</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #f5f5f5; }
        .card { background: white; border: 1px solid #e0e0e0; }
        input, textarea {
            border: 1px solid #d4d4d4;
            padding: 0.5rem;
            width: 100%;
        }
        input:focus, textarea:focus {
            border-color: #000;
            outline: none;
        }
        button { background: #2d2d2d; color: white; padding: 0.5rem 1rem; }
        button:hover { background: #000; }
    </style>
</head>
<body class="p-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-light">Add New Project</h1>
            <a href="index.php" class="text-gray-600 hover:text-black">&larr; Back to Dashboard</a>
        </div>
        
        <div class="card p-8">
            <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 mb-4 rounded">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-4 rounded">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-6">
                    <label class="block text-gray-700 mb-2 font-medium">Project Title</label>
                    <input type="text" name="title" required placeholder="e.g., E-Commerce Platform" class="rounded">
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 mb-2 font-medium">Description</label>
                    <textarea name="description" rows="6" placeholder="Describe the project, your role, technologies used, and key achievements..." class="rounded"></textarea>
                </div>
                
                <button type="submit" class="rounded w-full">Create Project</button>
            </form>
        </div>
        
        <div class="card p-6 mt-6">
            <h2 class="text-xl font-light mb-4">Existing Projects</h2>
            <div id="projects-list" class="space-y-2">
                Loading...
            </div>
        </div>
    </div>
    
    <script>
        async function loadProjects() {
            const response = await fetch('/api/projects.php');
            const result = await response.json();
            if (result.success && result.data.length > 0) {
                document.getElementById('projects-list').innerHTML = result.data.map(project => `
                    <div class="flex justify-between items-center border-b border-gray-100 py-2">
                        <span class="font-medium">${escapeHtml(project.title)}</span>
                        <span class="text-sm text-gray-500">${new Date(project.date_created).toLocaleDateString()}</span>
                    </div>
                `).join('');
            } else {
                document.getElementById('projects-list').innerHTML = '<div class="text-gray-500 text-sm">No projects yet. Create one above.</div>';
            }
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        loadProjects();
    </script>
</body>
</html>