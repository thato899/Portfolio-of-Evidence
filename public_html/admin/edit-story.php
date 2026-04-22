<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';

$media_id = $_GET['id'] ?? null;
if (!$media_id) {
    header('Location: index.php');
    exit;
}

// Get media details
$stmt = $pdo->prepare("SELECT m.*, p.title as project_title FROM media m LEFT JOIN projects p ON m.project_id = p.id WHERE m.id = ?");
$stmt->execute([$media_id]);
$media = $stmt->fetch();

if (!$media) {
    header('Location: index.php');
    exit;
}

$success_message = $_GET['success'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $story = trim($_POST['story'] ?? '');
    $hyperlink = trim($_POST['hyperlink'] ?? '');
    
    // Remove emojis from story text
    $story = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $story);
    
    $update = $pdo->prepare("UPDATE media SET story = ?, hyperlink = ? WHERE id = ?");
    $update->execute([$story, $hyperlink ?: null, $media_id]);
    
    $success_message = 'Story and link saved successfully';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Story | <?= htmlspecialchars($media['project_title']) ?></title>
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
        .preview-image {
            max-height: 300px;
            width: auto;
            margin: 0 auto;
        }
    </style>
</head>
<body class="p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-light">Write the Story</h1>
            <a href="index.php" class="text-gray-600 hover:text-black">&larr; Back to Dashboard</a>
        </div>
        
        <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 mb-4 rounded">
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card p-6">
                <h2 class="text-xl font-light mb-4">Media Preview</h2>
                <?php if ($media['filetype'] === 'image'): ?>
                    <img src="<?= $media['filepath'] ?>" alt="Preview" class="preview-image rounded">
                <?php elseif ($media['filetype'] === 'video'): ?>
                    <video src="<?= $media['filepath'] ?>" controls class="w-full rounded"></video>
                <?php else: ?>
                    <div class="bg-gray-100 p-8 text-center rounded">
                        <p class="text-gray-600">Document: <?= htmlspecialchars($media['filename']) ?></p>
                        <a href="<?= $media['filepath'] ?>" target="_blank" class="text-gray-600 hover:text-black mt-2 inline-block">View Document</a>
                    </div>
                <?php endif; ?>
                
                <div class="mt-4 text-sm text-gray-600">
                    <p><strong>Project:</strong> <?= htmlspecialchars($media['project_title']) ?></p>
                    <p><strong>File:</strong> <?= htmlspecialchars($media['filename']) ?></p>
                    <p><strong>Type:</strong> <?= $media['filetype'] ?></p>
                </div>
            </div>
            
            <div class="card p-6">
                <h2 class="text-xl font-light mb-4">Story Details</h2>
                <form method="POST">
                    <div class="mb-6">
                        <label class="block text-gray-700 mb-2 font-medium">Story Behind This Picture</label>
                        <textarea name="story" rows="8" required placeholder="Describe what this image represents, the context, challenges, solutions, or any relevant details..." class="rounded"><?= htmlspecialchars($media['story'] ?? '') ?></textarea>
                        <p class="text-xs text-gray-400 mt-1">Write a compelling story for potential employers</p>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 mb-2 font-medium">Hyperlink (Optional)</label>
                        <input type="url" name="hyperlink" value="<?= htmlspecialchars($media['hyperlink'] ?? '') ?>" placeholder="https://github.com/your-repo or https://live-demo.com" class="rounded">
                        <p class="text-xs text-gray-400 mt-1">Link to GitHub repository, live demo, or related article</p>
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="submit" class="rounded flex-1">Save Story and Link</button>
                        <a href="upload.php" class="bg-gray-600 text-white px-4 py-2 rounded text-center hover:bg-gray-800">Upload Another</a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card p-6 mt-6">
            <h2 class="text-xl font-light mb-4">Writing Tips for Employers</h2>
            <ul class="space-y-2 text-gray-700">
                <li class="flex items-start gap-2">
                    <span class="text-gray-400">•</span>
                    <span>Explain the technical challenge you solved</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-gray-400">•</span>
                    <span>Mention technologies and tools used</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-gray-400">•</span>
                    <span>Describe your specific contribution if it was a team project</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-gray-400">•</span>
                    <span>Share measurable results or outcomes</span>
                </li>
            </ul>
        </div>
    </div>
</body>
</html>