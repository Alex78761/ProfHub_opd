<footer>
    <div class="footer-content">
        <div class="footer-logo">
            <img src="images/logotip.jpg" alt="ProfHub" height="30">
        </div>
        <div class="footer-links">
            <a href="about.php">О нас</a>
            <a href="contact.php">Контакты</a>
            <a href="privacy.php">Конфиденциальность</a>
        </div>
    </div>
</footer>

<style>
footer {
    position: relative;
    z-index: 1000;
    padding: 20px 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(10px);
    margin-top: 40px;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.footer-logo img {
    height: 30px;
    width: auto;
}

.footer-links {
    display: flex;
    gap: 20px;
}

.footer-links a {
    color: #fff;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.3s ease;
}

.footer-links a:hover {
    color: #007bff;
}
</style> 