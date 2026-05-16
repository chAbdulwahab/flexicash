<?php
session_start();
require '../includes/config.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Initialize database connection
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle blog post submission with image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_blog'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $created_at = date('Y-m-d H:i:s');
    
    // Handle image upload if present
    $image_path = '';
    if (isset($_FILES['blog_image']) && $_FILES['blog_image']['error'] === 0) {
        $upload_dir = '../uploads/blog_images/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['blog_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
        
        if (in_array($file_extension, $allowed_extensions)) {
            $image_path = $upload_dir . uniqid() . '.' . $file_extension;
            move_uploaded_file($_FILES['blog_image']['tmp_name'], $image_path);
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO blogs (title, content, image_path, created_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $content, $image_path, $created_at);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Blog post added successfully!";
    } else {
        $_SESSION['error'] = "Error adding blog post: " . $conn->error;
    }
    
    header('Location: blogs.php');
    exit();
}

// Get all blog posts
$result = $conn->query("SELECT * FROM blogs ORDER BY created_at DESC");
$blogs = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Management - Earnings Platform</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <!-- Include TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/781fki51bcak1n0euhl4tjgnif3cjutde8p6iqg4ctmb3bz4/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#blog_content',
            height: 500,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount', 'emoticons',
                'codesample', 'paste'
            ],
            toolbar: 'undo redo | formatselect | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | image media link emoticons codesample | help',
            menubar: 'file edit view insert format tools table help',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.6; }',
            images_upload_url: 'upload.php',
            automatic_uploads: true,
            images_reuse_filename: true,
            paste_data_images: true,
            relative_urls: false,
            remove_script_host: false,
            convert_urls: true,
            branding: false,
            promotion: false,
            file_picker_types: 'image',
            image_title: true,
            image_description: true,
            image_caption: true,
            image_dimensions: false,
            file_picker_callback: function(cb, value, meta) {
                var input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                input.onchange = function() {
                    var file = this.files[0];
                    var maxSize = 5 * 1024 * 1024; // 5MB
                    
                    if (file.size > maxSize) {
                        alert('Image size should not exceed 5MB');
                        return;
                    }
                    
                    var reader = new FileReader();
                    reader.onload = function() {
                        var id = 'blobid' + (new Date()).getTime();
                        var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                        var base64 = reader.result.split(',')[1];
                        var blobInfo = blobCache.create(id, file, base64);
                        blobCache.add(blobInfo);
                        cb(blobInfo.blobUri(), { 
                            title: file.name,
                            alt: file.name 
                        });
                    };
                    reader.readAsDataURL(file);
                };
                input.click();
            }
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save(); // Automatically save content to textarea
                });
            }
        });
    </script>
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <div class="logo">
                <a href="../index.php">FlexiCash</a>
            </div>
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="users.php" class="nav-link">Users</a>
                <a href="deposits.php" class="nav-link">Deposits</a>
                <a href="blogs.php" class="nav-link active">Blogs</a>
                <a href="withdrawals.php" class="nav-link">Withdraws</a>
                <a href="../logout.php" class="nav-link">Logout</a>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <h1>Blog Management</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Enhanced Blog Form -->
        <div class="card" style="margin-bottom: 2rem;">
            <h2>Add New Blog Post</h2>
            <form method="POST" action="blogs.php" enctype="multipart/form-data" id="blogForm">
                <div class="form-group">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Featured Image</label>
                    <input type="file" name="blog_image" class="form-input" accept="image/*">
                    <small class="form-text">Supported formats: JPG, JPEG, PNG, GIF</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Content</label>
                    <textarea id="blog_content" name="content" class="form-input" style="visibility: visible; min-height: 300px;" required></textarea>
                </div>

                <button type="submit" name="add_blog" class="btn btn-primary" id="publishBtn">Publish Blog Post</button>
            </form>
        </div>

        <!-- Blog Posts List -->
        <div class="card">
            <h2>Published Blog Posts</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($blogs as $blog): ?>
                    <tr>
                        <td><?= htmlspecialchars($blog['title']) ?></td>
                        <td><?= date('M d, Y H:i', strtotime($blog['created_at'])) ?></td>
                        <td>
                            <a href="edit_blog.php?id=<?= $blog['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="delete_blog.php?id=<?= $blog['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this blog post?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <style>
        /* Main Content Styles */
        .main-content {
            max-width: 1200px;
            margin: 6rem auto 2rem;
            padding: 0 1rem;
        }

        /* Card Styles */
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            border-color: var(--primary-dark);
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
            outline: none;
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26, 35, 126, 0.2);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        /* Table Styles */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .table th {
            background: #f8fafc;
            font-weight: 600;
        }

        .table tr:hover {
            background: #f8fafc;
        }

        /* Alert Styles */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #34d399;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #f87171;
        }

        /* TinyMCE Editor Styles */
        .tox-tinymce {
            border-radius: 8px !important;
            border: 2px solid #e2e8f0 !important;
        }

        .tox-tinymce:focus-within {
            border-color: var(--primary-dark) !important;
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1) !important;
        }

        /* Image Preview Styles */
        .preview-image {
            max-width: 200px;
            margin-top: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .main-content {
                margin-top: 5rem;
            }

            .card {
                padding: 1.5rem;
            }

            .table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>

    <script>
        // Image preview
        document.querySelector('input[name="blog_image"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.createElement('img');
                    preview.src = e.target.result;
                    preview.className = 'preview-image';
                    const container = document.querySelector('input[name="blog_image"]').parentNode;
                    const existingPreview = container.querySelector('.preview-image');
                    if (existingPreview) {
                        container.removeChild(existingPreview);
                    }
                    container.appendChild(preview);
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>

    <script>
        // Replace the form submission script with this updated version
        document.getElementById('blogForm').addEventListener('submit', function(e) {
            // Get the TinyMCE content before preventing default
            const content = tinymce.get('blog_content').getContent().trim();
            const title = this.querySelector('input[name="title"]').value.trim();
            
            // Check if required fields are filled
            if (!title) {
                e.preventDefault();
                alert('Please enter a title for the blog post');
                return false;
            }
            
            if (!content) {
                e.preventDefault();
                alert('Please enter content for the blog post');
                return false;
            }
            
            // Update the hidden textarea with TinyMCE content
            document.getElementById('blog_content').value = content;
            
            // Disable the publish button to prevent double submission
            const publishBtn = document.getElementById('publishBtn');
            publishBtn.disabled = true;
            publishBtn.innerHTML = '<span>Publishing...</span>';
            
            // Allow the form to submit
            return true;
        });
    
        // Image preview with validation
        document.querySelector('input[name="blog_image"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            if (file) {
                if (file.size > maxSize) {
                    alert('Image size should not exceed 5MB');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.createElement('img');
                    preview.src = e.target.result;
                    preview.className = 'preview-image';
                    const container = document.querySelector('input[name="blog_image"]').parentNode;
                    const existingPreview = container.querySelector('.preview-image');
                    if (existingPreview) {
                        container.removeChild(existingPreview);
                    }
                    container.appendChild(preview);
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>