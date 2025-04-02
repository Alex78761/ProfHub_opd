<div class="video-background">
    <video autoplay muted loop>
        <source src="images/fontop.mp4" type="video/mp4">
    </video>
</div>
<div class="overlay"></div>

<header>
    <nav>
        <div class="logo">
            <a href="index.php">
                <img src="images/logotip.jpg" alt="ProfHub" height="40">
            </a>
        </div>
        <ul class="nav-links">
            <li><a href="professions.php">Профессии</a></li>
            <li><a href="tests.php" class="active">Тесты</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="profile.php">Личный кабинет</a></li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li><a href="admin.php">Панель управления</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Выйти</a></li>
            <?php else: ?>
                <li><a href="login.php">Войти</a></li>
                <li><a href="register.php">Регистрация</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<style>
header {
    position: relative;
    z-index: 1000;
    padding: 20px 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(10px);
}

nav {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo img {
    height: 40px;
    width: auto;
}

.nav-links {
    display: flex;
    gap: 20px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-links a {
    color: #fff;
    text-decoration: none;
    font-size: 16px;
    transition: color 0.3s ease;
}

.nav-links a:hover {
    color: #007bff;
}

.nav-links a.active {
    color: #007bff;
}

.video-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -2;
}

.video-background video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: -1;
}
</style> 