<?php
session_start();
require_once "db-connect.php";

// Получаем список всех профессий
$professions_query = "SELECT * FROM professions ORDER BY name ASC";
$professions_result = $conn->query($professions_query);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профессии - ProfHub</title>
    <link href="css/header.css" rel="stylesheet">
    <link href="css/background.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #1a1a1a;
            color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background-color: transparent;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .navbar-brand {
            width: 40px;
            height: 40px;
        }

        .navbar-brand img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .navbar-nav {
            display: flex;
            gap: 2rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        .nav-link.active {
            color: #007bff;
        }

        .hero-section {
            background: rgba(0, 0, 0, 0.7);
            padding: 2rem;
            text-align: center;
            border-radius: 10px;
            margin: 6rem auto 2rem;
            max-width: 1200px;
            backdrop-filter: blur(10px);
        }

        .hero-section h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: normal;
        }

        .hero-section p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0;
        }

        .professions-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .profession-card {
            background-color: rgba(42, 42, 42, 0.8);
            border-radius: 10px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            display: flex;
            flex-direction: column;
        }

        .profession-icon {
            width: 24px;
            height: 24px;
            margin-bottom: 1rem;
            filter: invert(1);
        }

        .profession-title {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: white;
            font-weight: normal;
        }

        .profession-description {
            color: #cccccc;
            margin-bottom: 1.5rem;
            line-height: 1.5;
            flex-grow: 1;
        }

        .btn-more {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: transparent;
            border: 1px solid #007bff;
            color: #007bff;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 0.9rem;
            text-align: center;
        }

        .btn-more:hover {
            background-color: #007bff;
            color: white;
        }

        footer {
            background-color: transparent;
            padding: 2rem;
            margin-top: auto;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-logo {
            width: 40px;
            height: 40px;
        }

        .footer-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .footer-links a {
            color: #cccccc;
            text-decoration: none;
            margin-left: 2rem;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: white;
        }

        .background {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -1;
            background: linear-gradient(45deg, #1a1a1a, #2a2a2a);
        }

        @media (max-width: 900px) {
            .professions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 600px) {
            .professions-grid {
                grid-template-columns: 1fr;
                padding: 1rem;
            }
        }
        .profession-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.7rem;
        }
        .salary {
            color: #ffd700;
            font-weight: 600;
            font-size: 1.1rem;
        }
        .profession-req {
            color: #8ecfff;
            font-size: 0.95rem;
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .profession-req i {
            color: #007bff;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="background"></div>
    <div class="main-content" style="margin-top: 7rem;">
    <div class="hero-section">
            <h1 class="main-title">Каталог IT-профессий</h1>
            <p class="main-subtitle">Исследуйте различные профессии в сфере информационных технологий и найдите свой путь в IT.</p>
    </div>

        <div class="professions-grid fade-in-grid">
        <?php while ($profession = $professions_result->fetch_assoc()): ?>
                <div class="profession-card fade-in-card">
                <img src="images/document-icon.svg" alt="" class="profession-icon">
                <h2 class="profession-title"><?php echo htmlspecialchars($profession['name']); ?></h2>
                    <div class="profession-meta">
                        <span class="salary"><i class="fas fa-coins"></i> <?php echo htmlspecialchars($profession['salary']); ?> ₽</span>
                    </div>
                <p class="profession-description">
                        <?php echo htmlspecialchars(mb_substr($profession['description'], 0, 90)) . (mb_strlen($profession['description']) > 90 ? '...' : ''); ?>
                </p>
                    <div class="profession-req">
                        <span><i class="fas fa-list"></i> <?php echo htmlspecialchars(mb_substr($profession['requirements'], 0, 60)) . (mb_strlen($profession['requirements']) > 60 ? '...' : ''); ?></span>
                    </div>
                <a href="profession.php?id=<?php echo $profession['id']; ?>" class="btn-more">Подробнее</a>
            </div>
        <?php endwhile; ?>
    </div>
    </div>
    <footer>
        <div class="footer-content">
            <a href="index.php" class="footer-logo">
                <img src="images/logotip.jpg" alt="ProfHub">
            </a>
            <div class="footer-links">
                <a href="about.php">О нас</a>
                <a href="contact.php">Контакты</a>
                <a href="privacy.php">Конфиденциальность</a>
            </div>
        </div>
    </footer>
    <style>
    .main-title {
        color: #fff;
        font-size: 2.7rem;
        font-weight: 700;
        margin-bottom: 0.7rem;
        text-shadow: 0 4px 24px #000, 0 1px 0 #007bff;
        letter-spacing: 1px;
    }
    .main-subtitle {
        color: #e0e0e0;
        font-size: 1.2rem;
        opacity: 0.95;
        margin-bottom: 0;
    }
    .hero-section {
        background: rgba(0, 0, 0, 0.8);
        padding: 2.5rem 2rem 2rem 2rem;
        text-align: center;
        border-radius: 16px;
        margin: 7rem auto 3.5rem;
        max-width: 900px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    }
    .professions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 2.5rem;
        padding: 2rem 0 3rem 0;
        justify-items: center;
        align-items: stretch;
    }
    .profession-card {
        background: rgba(255,255,255,0.10);
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        padding: 2.2rem 1.7rem 1.7rem 1.7rem;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        min-width: 260px;
        max-width: 350px;
        transition: box-shadow 0.3s, transform 0.3s, background 0.3s;
        border: 1.5px solid rgba(0,123,255,0.08);
        position: relative;
        overflow: hidden;
    }
    .profession-card:hover {
        box-shadow: 0 16px 48px rgba(0,123,255,0.18);
        transform: translateY(-8px) scale(1.04);
        background: rgba(0,123,255,0.10);
        border-color: #007bff44;
    }
    .profession-title {
        color: #fff;
        font-weight: 700;
        font-size: 1.25rem;
        margin-bottom: 0.7rem;
        letter-spacing: 0.5px;
    }
    .profession-meta {
        margin-bottom: 0.5rem;
        font-size: 1.08rem;
    }
    .salary {
        color: #ffd700;
        font-weight: 600;
        font-size: 1.08rem;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }
    .profession-description {
        color: #e0e0e0;
        margin-bottom: 1rem;
        min-height: 2.5em;
        font-size: 1.05rem;
    }
    .profession-req {
        color: #8ecfff;
        font-size: 0.97rem;
        margin-bottom: 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .profession-req i {
        color: #007bff;
    }
    .btn-more {
        background: linear-gradient(90deg, #007bff 60%, #0056b3 100%);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 0.7rem 1.5rem;
        font-weight: 600;
        font-size: 1.08rem;
        text-decoration: none;
        transition: background 0.2s, box-shadow 0.2s, transform 0.2s;
        box-shadow: 0 2px 8px rgba(0,123,255,0.10);
        margin-top: auto;
        letter-spacing: 0.5px;
    }
    .btn-more:hover {
        background: linear-gradient(90deg, #0056b3 60%, #007bff 100%);
        color: #fff;
        box-shadow: 0 4px 16px rgba(0,123,255,0.18);
        transform: scale(1.06);
    }
    .profession-icon {
        width: 36px;
        height: 36px;
        margin-bottom: 1.1rem;
        filter: invert(1);
        opacity: 0.95;
    }
    /* Анимация появления */
    .fade-in-grid {
        animation: fadeIn 0.8s cubic-bezier(.39,.575,.56,1) both;
    }
    .fade-in-card {
        opacity: 0;
        animation: fadeInCard 0.8s cubic-bezier(.39,.575,.56,1) both;
        animation-delay: 0.2s;
    }
    .fade-in-card:nth-child(2) { animation-delay: 0.35s; }
    .fade-in-card:nth-child(3) { animation-delay: 0.5s; }
    .fade-in-card:nth-child(4) { animation-delay: 0.65s; }
    .fade-in-card:nth-child(5) { animation-delay: 0.8s; }
    @keyframes fadeIn {
        0% { opacity: 0; transform: translateY(30px); }
        100% { opacity: 1; transform: none; }
    }
    @keyframes fadeInCard {
        0% { opacity: 0; transform: translateY(40px) scale(0.98); }
        100% { opacity: 1; transform: none; }
    }
    @media (max-width: 900px) {
        .professions-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .hero-section {
            margin: 7rem auto 2rem;
        }
    }
    @media (max-width: 600px) {
        .professions-grid {
            grid-template-columns: 1fr;
            padding: 1rem 0 2rem 0;
        }
        .main-content {
            padding: 0 0.2rem 2rem 0.2rem;
        }
        .hero-section {
            padding: 1.2rem 0.5rem 1.2rem 0.5rem;
        }
    }
    </style>
</body>
</html>
