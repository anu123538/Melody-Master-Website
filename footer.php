<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-section about">
            <h3>Melody <span>Masters</span></h3>
            <p>Your premium destination for high-quality musical instruments and accessories. We bring harmony to your doorstep with authentic sounds.</p>
            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
            </div>
        </div>

        <div class="footer-section links">
            <h4>Quick Explorer</h4>
            <ul>
                <li><a href="index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
                <li><a href="shop.php"><i class="fas fa-chevron-right"></i> Shop Products</a></li>
                <li><a href="register.php"><i class="fas fa-chevron-right"></i> Create Account</a></li>
                <li><a href="cart.php"><i class="fas fa-chevron-right"></i> My Cart</a></li>
            </ul>
        </div>

        <div class="footer-section contact">
            <h4>Get In Touch</h4>
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <p>support@melodymasters.com</p>
            </div>
            <div class="contact-item">
                <i class="fas fa-phone-alt"></i>
                <p>+94 112 345 678</p>
            </div>
            <div class="contact-item">
                <i class="fas fa-map-marker-alt"></i>
                <p>123 Music Lane, Colombo, Sri Lanka</p>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; <?php echo date("Y"); ?> <span>Melody Masters</span> Music Store. Crafted for Musicians.</p>
    </div>
</footer>

<style>
    :root {
        --footer-bg: #111122; /* Navbar එකට සමාන තද නිල් පැහැය */
        --footer-text: #bdc3c7;
        --footer-gold: #d4af37; /* Soft Gold */
        --footer-gold-glow: rgba(212, 175, 55, 0.2);
    }

    .main-footer {
        background-color: var(--footer-bg);
        color: var(--footer-text);
        padding: 70px 0 30px;
        margin-top: 50px;
        border-top: 1px solid rgba(212, 175, 55, 0.15);
        font-family: 'Poppins', sans-serif;
    }

    .footer-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 40px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 25px;
    }

    /* Brand Styling */
    .footer-section h3 {
        color: #fff;
        font-size: 1.8rem;
        margin-bottom: 20px;
        letter-spacing: 1px;
    }
    .footer-section h3 span {
        color: var(--footer-gold);
    }

    .footer-section h4 {
        color: #fff;
        margin-bottom: 25px;
        font-size: 1.1rem;
        position: relative;
        padding-bottom: 10px;
    }

    .footer-section h4::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 40px;
        height: 2px;
        background: var(--footer-gold);
    }

    .footer-section p {
        line-height: 1.8;
        font-size: 14px;
        color: #999;
    }

    /* Links Styling */
    .footer-section ul { list-style: none; padding: 0; }
    .footer-section ul li { margin-bottom: 12px; }
    .footer-section ul li a {
        color: var(--footer-text);
        text-decoration: none;
        transition: 0.3s;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .footer-section ul li a i { font-size: 10px; color: var(--footer-gold); }
    .footer-section ul li a:hover {
        color: var(--footer-gold);
        transform: translateX(5px);
    }

    /* Social Icons */
    .social-icons {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }
    .social-icons a {
        width: 38px;
        height: 38px;
        background: rgba(255,255,255,0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: var(--footer-gold);
        text-decoration: none;
        transition: 0.4s;
        border: 1px solid rgba(212, 175, 55, 0.2);
    }
    .social-icons a:hover {
        background: var(--footer-gold);
        color: #111;
        box-shadow: 0 0 15px var(--footer-gold-glow);
        transform: translateY(-3px);
    }

    /* Contact Items */
    .contact-item {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
    }
    .contact-item i {
        color: var(--footer-gold);
        font-size: 16px;
    }
    .contact-item p { margin: 0; }

    /* Footer Bottom */
    .footer-bottom {
        text-align: center;
        border-top: 1px solid rgba(255,255,255,0.05);
        padding-top: 30px;
        margin-top: 50px;
        font-size: 13px;
        color: #666;
    }
    .footer-bottom span { color: var(--footer-gold); }

    /* Responsive */
    @media (max-width: 768px) {
        .main-footer { text-align: center; }
        .footer-section h4::after { left: 50%; transform: translateX(-50%); }
        .footer-section ul li a { justify-content: center; }
        .social-icons { justify-content: center; }
        .contact-item { justify-content: center; }
    }
</style>