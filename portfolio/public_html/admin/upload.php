<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once dirname(__DIR__) . '/includes/db.php';

// Get projects for dropdown
$projects = $pdo->query("SELECT id, title FROM projects ORDER BY date_created DESC")->fetchAll();

$upload_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = $_POST['project_id'] ?? null;
    $filetype = $_POST['filetype'] ?? 'image';
    
    if (!$project_id) {
        $upload_error = 'Please select a project';
    } elseif (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file'];
        $original_name = $file['name'];
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        
        $allowed_images = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowed_docs = ['pdf', 'doc', 'docx', 'txt'];
        $allowed_videos = ['mp4', 'webm', 'mov'];
        
        $is_valid = false;
        if ($filetype === 'image' && in_array($extension, $allowed_images)) {
            $is_valid = true;
        } elseif ($filetype === 'document' && in_array($extension, $allowed_docs)) {
            $is_valid = true;
        } elseif ($filetype === 'video' && in_array($extension, $allowed_videos)) {
            $is_valid = true;
        }
        
        if (!$is_valid) {
            $upload_error = 'Invalid file type for selected category';
        } else {
            $timestamp = time();
            $safe_name = preg_replace('/[^a-zA-Z0-9._-]/', '', $original_name);
            $filename = $timestamp . '_' . $safe_name;
            
            // Absolute path for your hosting
            $upload_dir = dirname(dirname(__DIR__)) . '/uploads/' . $filetype . 's/';
            $upload_path = $upload_dir . $filename;
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $stmt = $pdo->prepare("
                    INSERT INTO media (project_id, filename, filepath, filetype) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$project_id, $filename, $upload_path, $filetype]);
                $media_id = $pdo->lastInsertId();
                
                header("Location: edit-story.php?id=$media_id&success=1");
                exit;
            } else {
                $upload_error = 'Failed to move uploaded file';
            }
        }
    } else {
        $upload_error = 'Please select a file to upload';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Media</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #f5f5f5; }
        .card { background: white; border: 1px solid #e0e0e0; }
        input, select, textarea {
            border: 1px solid #d4d4d4;
            padding: 0.5rem;
            width: 100%;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #000;
            outline: none;
        }
        button { background: #2d2d2d; color: white; padding: 0.5rem 1rem; }
        button:hover { background: #000; }
        .dropzone {
            border: 2px dashed #d4d4d4;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
        }
        .dropzone.dragover {
            border-color: #000;
            background: #fafafa;
        }
    </style>
</head>
<body class="p-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-light">Upload Media</h1>
            <a href="index.php" class="text-gray-600 hover:text-black">&larr; Back to Dashboard</a>
        </div>
        
        <div class="card p-8">
            <?php if ($upload_error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-4 rounded">
                    <?= htmlspecialchars($upload_error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" id="upload-form">
                <div class="mb-6">
                    <label class="block text-gray-700 mb-2 font-medium">Select Project</label>
                    <select name="project_id" required>
                        <option value="">Choose a project</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?= $project['id'] ?>"><?= htmlspecialchars($project['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 mb-2 font-medium">File Type</label>
                    <select name="filetype" id="filetype" required>
                        <option value="image">Image (JPG, PNG, GIF, WEBP)</option>
                        <option value="document">Document (PDF, DOC, TXT)</option>
                        <option value="video">Video (MP4, WEBM, MOV)</option>
                    </select>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 mb-2 font-medium">Select File</label>
                    <div id="dropzone" class="dropzone rounded">
                        <p class="text-gray-600">Drag and drop a file here or click to browse</p>
                        <input type="file" name="file" id="file-input" class="hidden" required>
                        <div id="file-name" class="mt-2 text-sm text-gray-500"></div>
                    </div>
                </div>
                
                <button type="submit" class="w-full rounded">Upload and Continue to Story</button>
            </form>
        </div>
    </div>
    
    <script>
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('file-input');
        const fileName = document.getElementById('file-name');
        
        dropzone.addEventListener('click', () => fileInput.click());
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });
        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('dragover');
        });
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                fileName.textContent = files[0].name;
            }
        });
        
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                fileName.textContent = fileInput.files[0].name;
            }
        });
    </script>
</body>
</html>