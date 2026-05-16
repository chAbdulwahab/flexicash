</main>
<style>/* Footer Styles */
.footer {
    background-color: #333;
    color: #fff;
    padding: 40px 0;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    padding: 0 20px;
}

.footer-section {
    flex: 1;
    margin: 0 15px;
}

.footer-section h4 {
    color: #fff;
    margin-bottom: 20px;
    font-size: 18px;
}

.copyright {
    font-size: 14px;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 10px;
}

.footer-links a {
    color: #fff;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-links a:hover {
    color: #ffd700;
}

.social-links {
    display: flex;
    gap: 15px;
}

.social-links a {
    display: inline-block;
}

.social-links img {
    width: 24px;
    height: 24px;
    transition: transform 0.3s ease;
}

.social-links img:hover {
    transform: scale(1.1);
}

/* Responsive Design */
@media screen and (max-width: 768px) {
    .footer-content {
        flex-direction: column;
        text-align: center;
    }

    .footer-section {
        margin: 20px 0;
    }

    .social-links {
        justify-content: center;
    }
}</style>
<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <p class="copyright">&copy; <?php echo date('Y'); ?> EarningsPK. All rights reserved.</p>
        </div>
        <div class="footer-section">
            <h4>Quick Links</h4>
            <ul class="footer-links">
                <li><a href="../terms.php">Terms of Service</a></li>
                <li><a href="../privacy.php">Privacy Policy</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h4>Connect With Us</h4>
            <div class="social-links">
                <a href="https://t.me/flexiicash"><img src="../assets/images/telegram.png"></a>
                <a href="https://whatsapp.com/channel/0029VbAXfDEBadmVCWCRqA2t"><img src="../assets/images/whatsapp.png" alt="WhatsApp"></a>
            </div>
        </div>
    </div>
</footer>

<script src="../assets/js/script.js"></script>
</body>
</html>