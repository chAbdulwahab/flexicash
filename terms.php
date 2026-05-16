<?php
session_start(); // Ensure session is started, ideally at the very top
$page_title = "Terms of Service - FlexiCash";
// The line "include 'includes/header.php';" was here.
// If it's essential for PHP logic (not HTML output), it should be placed before any HTML output.
// For this example, I'm creating a self-contained HTML structure for terms.php.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <?php /* Consider adding a link to your main stylesheet if you have one, e.g., <link rel="stylesheet" href="css/style.css"> */ ?>
    <style>
        :root {
            --primary-dark: #1a237e;
            --primary-light: #534bae;
            --accent-color: #ffd700;
            --text-light: #ffffff;
            --transition-speed: 0.3s;
            --header-height: 70px;
            --gradient-primary: linear-gradient(135deg, #1a237e, #534bae);
            --gradient-accent: linear-gradient(135deg, #ffd700, #ffa000);
            --shadow-soft: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-strong: 0 8px 32px rgba(0, 0, 0, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            padding-top: var(--header-height); /* Account for fixed header */
        }

        /* Header Styles */
        .header {
            background: var(--gradient-primary);
            padding: 0 1rem; /* Simplified padding */
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: var(--shadow-soft);
            height: var(--header-height);
            display: flex; /* For vertical alignment of nav-container */
            align-items: center; /* For vertical alignment of nav-container */
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .logo h1 {
            margin: 0; /* Remove default h1 margin */
            font-size: 1.8rem; /* Adjust size as needed */
        }

        .logo h1 a {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 700;
        }

        .nav-links {
            display: flex;
            gap: 0.8rem; /* Adjust gap */
            align-items: center;
        }

        .nav-links a {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 0.8rem;
            border-radius: 6px; /* Slightly smaller radius */
            transition: background var(--transition-speed);
        }

    
        /* Hamburger Menu */
        .hamburger {
            display: none;
            flex-direction: column;
            justify-content: space-around;
            width: 28px; /* Slightly smaller */
            height: 22px; /* Slightly smaller */
            cursor: pointer;
            background: transparent;
            border: none;
            padding: 0;
        }

        .hamburger span {
            display: block;
            height: 3px;
            width: 100%;
            background: var(--text-light);
            border-radius: 3px;
            transition: all 0.3s ease-in-out;
        }

        .hamburger.open span:nth-child(1) {
            transform: translateY(9.5px) rotate(45deg);
        }
        .hamburger.open span:nth-child(2) {
            opacity: 0;
        }
        .hamburger.open span:nth-child(3) {
            transform: translateY(-9.5px) rotate(-45deg);
        }

        /* Main Content Area for Terms */
        .terms-content-area {
            max-width: 800px;
            margin: 2rem auto; /* Top margin for content below fixed header */
            padding: 2rem 2.5rem; /* More padding */
            background: #fff;
            border-radius: 12px; /* Softer radius */
            box-shadow: var(--shadow-strong); /* Stronger shadow for prominence */
        }

        .terms-content-area h1 {
            font-size: 2.4rem; /* Larger main title */
            color: var(--primary-dark);
            margin-bottom: 1.5rem;
            text-align: left; /* Align to left for readability */
        }

        .terms-content-area h2 {
            font-size: 1.7rem; /* Slightly larger section titles */
            color: var(--primary-light);
            margin-top: 2rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-dark); /* Accent border */
        }

        .terms-content-area p,
        .terms-content-area ul {
            margin-bottom: 1.2rem; /* More spacing */
            line-height: 1.7; /* Improved readability */
            color: #333; /* Darker text for better contrast */
        }

        .terms-content-area ul {
            padding-left: 20px; /* Standard list indent */
        }
        .terms-content-area li {
            margin-bottom: 0.6rem; /* More spacing for list items */
        }
        .terms-content-area a { /* Style links within terms content */
            color: var(--primary-dark);
            text-decoration: underline;
        }
        .terms-content-area a:hover {
            color: var(--primary-light);
        }


        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding-top: calc(var(--header-height) - 10px); /* Adjust if header height changes on mobile */
            }
            .header {
                 height: calc(var(--header-height) - 10px); /* Slightly smaller header on mobile */
            }
            .nav-links {
                display: none;
                position: absolute;
                top: calc(var(--header-height) - 10px); /* Align below header */
                left: 0;
                right: 0;
                background: var(--primary-dark);
                padding: 1rem;
                flex-direction: column;
                gap: 0.8rem; /* More space in dropdown */
                box-shadow: var(--shadow-strong);
                border-top: 1px solid var(--primary-light);
            }
            .nav-links.active {
                display: flex;
            }
            .hamburger {
                display: flex;
            }
            .terms-content-area {
                margin: 1.5rem auto;
                padding: 1.5rem;
            }
            .terms-content-area h1 {
                font-size: 2rem;
            }
            .terms-content-area h2 {
                font-size: 1.5rem;
            }
        }

        /* Basic Footer */
        .site-footer {
            text-align: center;
            padding: 2.5rem 1rem;
            background-color: #343a40; /* Darker footer */
            color: var(--text-light);
            margin-top: 3rem;
            font-size: 0.95rem;
        }
        .site-footer p {
            margin-bottom: 0.5rem;
        }
        .site-footer a {
            color: var(--accent-color);
            text-decoration: none;
        }
        .site-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <header class="header">
        <nav class="nav-container">
            <div class="logo">
                <h1><a href="index.php">FlexiCash</a></h1>
            </div>
            <button class="hamburger" id="hamburger" aria-label="Toggle navigation" aria-expanded="false" type="button">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="nav-links" id="navLinks">
                <?php if(isset($_SESSION['user_id'])) : ?>
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="withdraw.php" class="nav-link">Withdraws</a>
                    <a href="referrals.php" class="nav-link">Referrals</a>
                    <a href="videos.php" class="nav-link">Video</a>
                    <a href="logout.php" class="nav-link">Logout</a>
                <?php else : ?>
                    <a href="index.php" class="nav-link">Home</a>
                    <a href="login.php" class="nav-link">Login</a>
                    <a href="register.php" class="nav-link">Register</a>
                    <a href="terms.php" class="nav-link active">Terms</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="terms-content-area">
        <h1>Terms of Service for FlexiCash</h1>
        <p>Welcome to FlexiCash! These Terms of Service  govern your use of our website, flexicash.infy.uk ,and the services offered through it. By accessing or using our platform, you agree to comply with and be bound by these Terms. If you do not agree to these Terms, please do not use our Site or services.</p>

        <h2>1. Acceptance of Terms</h2>
        <p>By registering for an account or using FlexiCash, you confirm that you have read, understood, and agree to be bound by these Terms, as well as our Privacy Policy and all applicable laws and regulations.</p>

        <h2>2. How Users Earn Money</h2>
        <p>FlexiCash offers users the opportunity to earn money through the following primary method:</p>
        <ul>
            <li><strong>Watching Videos:</strong> Users can make a deposit to activate their earning potential. Once activated, users can watch sponsored YouTube videos on our platform. For each video watched according to our guidelines, users will receive earnings credited to their FlexiCash account.</li>
            <li><strong>Withdrawals:</strong> The minimum amount for a single withdrawal is 50 PKR. Withdrawal conditions, including referral requirements, are detailed in Section 5.</li>
        </ul>

        <h2>3. How FlexiCash Earns Money</h2>
        <p>FlexiCash generates revenue through the following channels, which allows us to provide earning opportunities to our users:</p>
        <ul>
            <li><strong>Sponsored Videos:</strong> We partner with content creators and businesses who pay us to feature their YouTube videos on our platform.</li>
            <li><strong>Advertisements:</strong> We display advertisements on our website. Revenue from these ads contributes to the platform's operational costs and user earnings pool.</li>
        </ul>

        <h2>4. Our Future Goals</h2>
        <p>FlexiCash is committed to growing and enhancing the user experience. If our platform receives a positive response and achieves sustainable growth, we plan to introduce additional earning features and opportunities in the future. We value user feedback and will consider it in our development roadmap.</p>

        <h2>5. Referral System</h2>
        <p>Our referral system is an important part of our growth strategy, especially as a new platform aiming to build a strong user base. To ensure active participation and community growth, the following conditions apply:</p>
        <ul>
            <li><strong>Necessity of Referrals:</strong> To maintain an active earning account and be eligible for continuous withdrawals, users are encouraged to participate in our referral program.</li>
            <li><strong>Referral Condition:</strong> Users are required to have at least one (1) active referral within every nine (9) day period OR before being eligible for every seventh (7th) withdrawal, whichever condition is met sooner. An "active referral" is a user who has registered using your referral link and has made their first deposit.</li>
            <li><strong>Withdrawal Unit:</strong> One standard withdrawal is equivalent to 50 PKR.</li>
            <li>Failure to meet the referral condition may temporarily pause withdrawal capabilities until the condition is met.</li>
        </ul>
        <p>We believe this system will help us grow a vibrant community, benefiting all users in the long run.</p>

        <h2>6. User Responsibilities</h2>
        <ul>
            <li>You are responsible for maintaining the confidentiality of your account information, including your password, and for all activities that occur under your account.</li>
            <li>You agree to provide accurate and current information during registration and to update it as necessary.</li>
            <li>You must be at least 18 years old or the age of majority in your jurisdiction to use our services.</li>
        </ul>

        <h2>7. Prohibited Activities</h2>
        <p>Users may not use the FlexiCash platform for any unlawful, fraudulent, or malicious activities. This includes, but is not limited to:</p>
        <ul>
            <li>Using bots, scripts, or other automated methods to watch videos or interact with the platform.</li>
            <li>Creating multiple accounts for a single individual.</li>
            <li>Attempting to exploit any vulnerabilities in the system.</li>
            <li>Engaging in any activity that disrupts or interferes with the platform's operation.</li>
        </ul>
        <p>Violation of these rules may result in account suspension or termination and forfeiture of earnings.</p>

        <h2>8. Changes to Terms</h2>
        <p>FlexiCash reserves the right to modify or replace these Terms at any time. We may update these terms up to twice in a calendar month. We will endeavor to notify users of significant changes, but it is your responsibility to review these Terms periodically. Continued use of the Site after any such changes constitutes your acceptance of the new Terms.</p>

        <h2>9. Disclaimer of Warranties</h2>
        <p>FlexiCash provides its services "as is" and "as available" without any warranties, express or implied. We do not guarantee that the service will be uninterrupted, error-free, or that earnings will be guaranteed.</p>

        <h2>10. Limitation of Liability</h2>
        <p>To the fullest extent permitted by law, FlexiCash shall not be liable for any indirect, incidental, special, consequential, or punitive damages, or any loss of profits or revenues, whether incurred directly or indirectly, or any loss of data, use, goodwill, or other intangible losses, resulting from your access to or use of or inability to access or use the services.</p>

        <h2>11. Contact Us</h2>
        <p>If you have any questions about these Terms, please contact us at support@flexicash.infy.uk.</p>
        <p>Thank you for using FlexiCash!</p>
    </main>


    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const hamburger = document.getElementById('hamburger');
        const navLinks = document.getElementById('navLinks');

        if (hamburger && navLinks) {
            hamburger.addEventListener('click', function(e) {
                e.stopPropagation(); // Prevent click from immediately closing menu if bubbling
                navLinks.classList.toggle('active');
                hamburger.classList.toggle('open');
                const isExpanded = navLinks.classList.contains('active');
                hamburger.setAttribute('aria-expanded', isExpanded);
            });

            // Close menu if clicking outside of it
            document.addEventListener('click', function(e) {
                if (navLinks.classList.contains('active') && !navLinks.contains(e.target) && !hamburger.contains(e.target)) {
                    navLinks.classList.remove('active');
                    hamburger.classList.remove('open');
                    hamburger.setAttribute('aria-expanded', 'false');
                }
            });
            
            // Optional: Close menu when a nav link is clicked (useful for single-page apps or # links)
            navLinks.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function() {
                    // This check is important if your links cause full page reloads
                    if (window.innerWidth <= 768 && navLinks.classList.contains('active')) { 
                        navLinks.classList.remove('active');
                        hamburger.classList.remove('open');
                        hamburger.setAttribute('aria-expanded', 'false');
                    }
                });
            });
        }
    });
    </script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>