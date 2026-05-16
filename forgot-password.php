<?php
session_start();
require 'includes/config.php';

// Generate CSRF token if doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - FlexiCash</title>
    <style>
        :root {
            --primary-dark: #1a237e;
            --primary-light: #534bae;
            --accent-color: #ffd700;
            --text-light: #ffffff;
            --gradient-primary: linear-gradient(135deg, #1a237e, #534bae);
            --gradient-accent: linear-gradient(135deg, #ffd700, #ffa000);
            --shadow-soft: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-strong: 0 8px 32px rgba(0, 0, 0, 0.15);
        }

        .container {
            max-width: 800px;
            margin: 6rem auto;
            padding: 0 1.5rem;
            animation: fadeIn 0.5s ease;
        }

        .auth-card {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-strong);
            padding: 2.5rem;
            margin: 2rem auto;
            transition: transform 0.3s ease;
        }

        .auth-card:hover {
            transform: translateY(-5px);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-header h2 {
            font-size: 2rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .text-muted {
            color: #6b7280;
        }

        .contact-info {
            margin-top: 2rem;
            padding: 2rem;
            background: #f8fafc;
            border-radius: 15px;
            text-align: center;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .contact-info:hover {
            background: #f1f5f9;
            border-color: var(--primary-light);
        }

        .email-link {
            display: inline-block;
            color: var(--primary-dark);
            text-decoration: none;
            font-weight: 600;
            margin: 1rem 0;
            padding: 0.75rem 1.5rem;
            background: rgba(26, 35, 126, 0.1);
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .email-link:hover {
            background: rgba(26, 35, 126, 0.15);
            transform: translateY(-2px);
            text-decoration: none;
        }

        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }

        .text-link {
            color: var(--primary-dark);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .text-link:hover {
            color: var(--primary-light);
            text-decoration: underline;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        .mt-4 {
            margin-top: 1.5rem;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .container {
                margin: 4rem auto;
                padding: 0 1rem;
            }

            .auth-card {
                padding: 2rem;
            }

            .auth-header h2 {
                font-size: 1.75rem;
            }

            .contact-info {
                padding: 1.5rem;
            }
        }

        @media screen and (max-width: 480px) {
            .container {
                margin: 3rem auto;
            }

            .auth-card {
                padding: 1.5rem;
            }

            .auth-header h2 {
                font-size: 1.5rem;
            }

            .contact-info {
                padding: 1.25rem;
            }

            .email-link {
                padding: 0.625rem 1.25rem;
            }
        }

        /* Touch Device Optimizations */
        @media (hover: none) {
            .auth-card:hover {
                transform: none;
            }

            .email-link:hover {
                transform: none;
            }

            .email-link {
                min-height: 44px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <main class="main-content">
        <div class="container">
            <div class="auth-card">
                <div class="auth-header">
                    <h2 class="text-center">Forgot Password</h2>
                    <p class="text-center text-muted">Contact Admin for Password Reset</p>
                </div>

                <div class="contact-info">
                    <p>To reset your password, please contact our admin at:</p>
                    <p><a href="mailto:verstile.earning@gmail.com" class="email-link">verstile.earning@gmail.com</a></p>
                    <p class="text-muted mt-3">Please include your registered email address in your request.</p>
                </div>

                <div class="auth-footer mt-4">
                    <p class="text-center">Remember your password? <a href="login.php" class="text-link">Login here</a></p>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>