
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <style>
        :root {
            --primary-dark: #2c3e50;
            --accent-color: #ffd700;
            --header-height: 60px;
            --transition-timing: 0.3s ease;
        }

        /* Header Styles */
        .header {
            background-color: var(--primary-dark);
            color: white;
            height: var(--header-height);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            height: 100%;
        }

        .logo h1 {
            margin: 0;
            font-size: 1.5rem;
        }

        .logo a {
            color: #fff;
            text-decoration: none;
            transition: color var(--transition-timing);
        }

        .logo a:hover {
            color: var(--accent-color);
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all var(--transition-timing);
            color: white;
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--accent-color);
            transition: all var(--transition-timing);
        }

        .nav-link:hover {
            color: var(--accent-color);
        }

        .nav-link:hover::after {
            width: 100%;
            left: 0;
        }

        .hamburger {
            display: none;
            flex-direction: column;
            justify-content: center;
            width: 32px;
            height: 32px;
            cursor: pointer;
            z-index: 1100;
            background: none;
            border: none;
            padding: 0;
        }

        .hamburger span {
            height: 3px;
            width: 100%;
            background: #fff;
            margin: 4px 0;
            border-radius: 2px;
            transform-origin: left center;
            transition: all var(--transition-timing);
        }

        @media screen and (max-width: 768px) {
            body.menu-open {
                overflow: hidden;
            }

            .hamburger {
                display: flex;
            }

            .nav-links {
                position: fixed;
                top: var(--header-height);
                left: 0;
                width: 100%;
                height: calc(100vh - var(--header-height));
                background-color: var(--primary-dark);
                flex-direction: column;
                padding: 2rem 0;
                transform: translateX(100%);
                transition: transform var(--transition-timing);
                overflow-y: auto;
            }

            .nav-links.open {
                transform: translateX(0);
            }

            .nav-link {
                width: 80%;
                text-align: center;
                padding: 1rem;
                margin: 0.5rem 0;
            }

            .nav-link::after {
                display: none;
            }

            .hamburger.open span:nth-child(1) {
                transform: rotate(45deg) translate(4px, -1px);
                width: 110%;
            }

            .hamburger.open span:nth-child(2) {
                opacity: 0;
                transform: translateX(-20px);
            }

            .hamburger.open span:nth-child(3) {
                transform: rotate(-45deg) translate(4px, 1px);
                width: 110%;
            }
        }
    </style>
</head>
<body>
   

    <script>
    
    </script>
</body>
</html>