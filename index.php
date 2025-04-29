<?php
session_start();
require_once "db-connect.php";

// Получаем последние добавленные профессии
$latest_professions_query = "SELECT * FROM professions ORDER BY id DESC LIMIT 3";
$latest_professions_result = $conn->query($latest_professions_query);

// Получаем статистику
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM professions) as total_professions,
    (SELECT COUNT(*) FROM tests) as total_tests,
    (SELECT COUNT(*) FROM users) as total_users";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProfHub — IT-профессии будущего</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/main.css" rel="stylesheet">
    <link href="/css/header.css" rel="stylesheet">
    <style>
        /* Основной полупрозрачный фон для страницы */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7); /* Полупрозрачный черный фон */
            z-index: -1;
        }

        header {
            background: rgba(0, 0, 0, 0.98) !important;
            border-bottom: 1px solid rgba(40, 40, 45, 0.5);
            z-index: 1000 !important;
        }
        
        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -2;
            overflow: hidden;
        }
        
        .video-background video {
            min-width: 100%;
            min-height: 100%;
            object-fit: cover;
        }

        /* Стили для карточек профессий (убрали hover-эффект с подробной информацией) */
        .it-popular-card {
            background: rgba(0, 0, 0, 0.97) !important;
            border: 1px solid rgba(0, 0, 0, 0.3); /* Менее прозрачный фон */
            color: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.18);
            padding: 1.5rem 1.2rem;
            margin-bottom: 1.5rem;
            min-height: 320px;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .it-popular-card:hover {
            background: rgba(0, 0, 0, 0.97) !important;
            transform: translateY(-5px);
            box-shadow: 0 8px 32px rgba(0, 123, 255, 0.2);
        }

        /* Удаляем блок с дополнительной информацией */
        .it-popular-card .card-more {
            display: none !important;
        }

        /* Остальные ваши стили остаются без изменений */
        .it-popular-card h5, 
        .it-popular-card p, 
        .it-popular-card a {
            color: #fff !important;
        }

        .it-popular-card h5 {
            color:rgb(255, 255, 255) !important;
            font-weight: 700;
            margin-bottom: 0.7em;
        }

        .it-popular-card .icon {
            font-size: 2em;
            margin-right: 0.5em;
            color:rgb(255, 255, 255);
        }

        .it-section-title {
            color:rgb(249, 251, 251);
            font-weight: 700;
            margin: 2.5rem 0 1.2rem 0;
            text-align: center;
            font-size: 2.1em;
        }
        .it-benefits-list {
            background: rgba(0, 0, 0, 0.92);
            border-radius: 18px;
            padding: 2em 2em 1em 2em;
            color: #fff;
            box-shadow: 0 4px 24px rgba(0,0,0,0.18);
            margin-bottom: 2em;
        }
        .it-benefits-list li {
            color: #fff;
            text-shadow: 0 2px 8px rgba(0,0,0,0.18);
        }
        .it-benefits-list i {
            color:rgb(78, 75, 75);
            text-shadow: 0 2px 8px rgba(0,0,0,0.18);
        }
        .it-steps {
            background: rgba(1, 1, 1, 0.92);
            border-radius: 18px;
            padding: 2em 2em 1em 2em;
            color: #fff;
            box-shadow: 0 4px 24px rgba(0,0,0,0.18);
            margin-bottom: 2em;
        }
        .it-steps li {
            color: #fff;
            text-shadow: 0 2px 8px rgba(0,0,0,0.18);
            font-size: 1.1em;
            margin-bottom: 0.7em;
        }
        .it-steps i {
            color:rgb(255, 255, 255);
            margin-right: 0.5em;
            text-shadow: 0 2px 8px rgba(0,0,0,0.18);
        }
        .main-jumbo {
            background: rgba(0,0,0,0.45);
            border-radius: 22px;
            padding: 2.5rem 2rem;
            margin: 2.5rem 0 2rem 0;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            color: #fff;
            text-align: center;
        }
        .main-jumbo h1 {
            color:rgb(250, 252, 252);
            font-size: 2.5em;
            font-weight: 700;
        }
        .main-jumbo p {
            font-size: 1.25em;
            margin-bottom: 1.5em;
        }
        .main-jumbo .btn {
            font-size: 1.15em;
            padding: 0.8em 2.2em;
        }
        @media (max-width: 900px) {
            .main-jumbo { padding: 1.2rem 0.5rem; }
            .it-section-title { font-size: 1.3em; }
        }
        .it-popular-card .card-more {
            opacity: 0;
            pointer-events: none;
            position: absolute;
            left: 0; right: 0; bottom: 0; top: 0;
            background: rgba(3, 3, 3, 0.97);
            color: #fff;
            border-radius: 18px;
            padding: 1.2em 1em;
            transition: opacity 0.3s;
            z-index: 2;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        .it-popular-card:hover .card-more {
            opacity: 1;
            pointer-events: auto;
        }
        .card-more h6 {
            color:rgb(250, 251, 251);
            font-weight: 700;
            margin-bottom: 0.7em;
        }
        .card-more ul {
            padding-left: 1.2em;
            text-align: left;
        }
        .card-more ul li {
            margin-bottom: 0.4em;
            font-size: 1em;
        }
        .cta-block {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2em;
            background: linear-gradient(90deg,rgb(0, 0, 0) 0%,rgb(255, 255, 255) 100%);
            color: #fff;
            border-radius: 2em;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            padding: 2.5em 2em;
            margin: 2em 0 2.5em 0;
            animation: fadeInUp 1s;
        }
        .cta-icon {
            font-size: 3em;
            background: rgba(255,255,255,0.12);
            border-radius: 50%;
            padding: 0.6em;
            margin-right: 1em;
            color:rgb(249, 249, 249);
            box-shadow: 0 2px 12px rgba(33,211,151,0.15);
        }
        .cta-block h2 {
            font-weight: 700;
            font-size: 2em;
            margin-bottom: 0.3em;
        }
        .cta-block p {
            font-size: 1.2em;
            margin-bottom: 1.2em;
        }
        .cta-btn {
            background: #fff;
            color:rgb(0, 0, 0);
            font-weight: 600;
            font-size: 1.15em;
            border-radius: 2em;
            padding: 0.8em 2.5em;
            border: none;
            box-shadow: 0 2px 8px rgba(255, 255, 255, 0.1);
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .cta-btn:hover {
            background:rgb(70, 65, 65);
            color: #fff;
            box-shadow: 0 4px 16px rgba(255, 255, 255, 0.18);
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px);}
            to { opacity: 1; transform: translateY(0);}
        }
        @media (max-width: 700px) {
            .cta-block { flex-direction: column; gap: 1em; padding: 1.2em 0.5em;}
            .cta-icon { margin-right: 0; }
        }
        .profession-card {
            background: rgba(3, 3, 3, 0.97) !important;
            color: #fff !important;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.18);
            margin-bottom: 1.5rem;
            transition: box-shadow 0.2s, background 0.2s, transform 0.2s;
        }
        .profession-card:hover {
            box-shadow: 0 8px 32px rgba(245, 245, 245, 0.18);
            background: rgba(0, 0, 0, 0.98);
            transform: translateY(-4px) scale(1.03);
        }
        .profession-card .card-title {
            color:rgb(243, 243, 243) !important;
            font-weight: 700;
        }
        .profession-card .card-text {
            color: #fff !important;
            text-shadow: 0 2px 8px rgba(0,0,0,0.18);
        }
        .profession-card .btn-outline {
            background: none;
            color:rgb(250, 250, 250);
            border: 2px solidrgb(253, 255, 254);
            border-radius: 2em;
            font-weight: 600;
            transition: background 0.2s, color 0.2s, border 0.2s;
            margin-top: 0.7em;
        }
        .profession-card .btn-outline:hover {
            background:rgb(58, 55, 55);
            color: #fff;
            border-color:rgb(255, 255, 255);
        }
        .latest-prof-title {
            color:rgb(255, 255, 255);
            font-size: 2.1em;
            font-weight: 700;
            text-align: center;
            margin: 2.5rem 0 1.2rem 0;
            text-shadow: 0 2px 12px #000, 0 1px 0rgb(255, 255, 255);
            letter-spacing: 1px;
        }
        body { padding-top: 3.5rem !important; }
        @media (max-width: 700px) {
            .mainpage-nav-container { flex-direction: column; gap: 0.5em; padding: 0 0.5rem; }
            .mainpage-links { margin: 0.5em 0; }
        }
    
    </style>

</head>
<body>
    <?php include 'header.php'; ?>
    <div class="video-background">
        <video autoplay muted loop playsinline>
            <source src="images/fontop.mp4" type="video/mp4">
        </video>
        <div class="overlay"></div>
    </div>
    <main>
        <div class="container">
            <div class="main-jumbo fade-in">
                <h1>ProfHub — твой старт в IT</h1>
                <p>Платформа для выбора, освоения и развития в самых востребованных IT-профессиях. Тесты, экспертные советы, сообщество и реальные карьерные истории.</p>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="register.php" class="btn btn-primary">Начать путь в IT</a>
                <?php endif; ?>
            </div>

            <div class="it-section-title">Популярные IT-профессии</div>
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="it-popular-card">
                        <h5><span class="icon"><i class="fas fa-code"></i></span>Frontend-разработчик</h5>
                        <p>Создаёт интерфейсы сайтов и веб-приложений. Важно знать HTML, CSS, JavaScript, современные фреймворки (React, Vue, Angular).</p>
                        <a href="profession.php?id=1" class="btn btn-outline-info btn-sm">Подробнее</a>
                        <div class="card-more">
                            <h6>Ключевые навыки</h6>
                            <ul>
                                <li>HTML, CSS, JavaScript</li>
                                <li>React, Vue, Angular</li>
                                <li>UI/UX-дизайн</li>
                            </ul>
                            <h6>Перспективы</h6>
                            <ul>
                                <li>Высокий спрос на рынке</li>
                                <li>Удалённая работа</li>
                            </ul>
                            <h6>Средняя зарплата</h6>
                            <div>от 120 000 ₽</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="it-popular-card">
                        <h5><span class="icon"><i class="fas fa-database"></i></span>Backend-разработчик</h5>
                        <p>Отвечает за серверную логику, базы данных, API. Важно знать Python, PHP, Java, Node.js, SQL, архитектуру приложений.</p>
                        <a href="profession.php?id=2" class="btn btn-outline-info btn-sm">Подробнее</a>
                        <div class="card-more">
                            <h6>Ключевые навыки</h6>
                            <ul>
                                <li>Python, PHP, Java, Node.js</li>
                                <li>SQL, базы данных</li>
                                <li>API, архитектура</li>
                            </ul>
                            <h6>Перспективы</h6>
                            <ul>
                                <li>Рост зарплат</li>
                                <li>Карьерный рост до тимлида</li>
                            </ul>
                            <h6>Средняя зарплата</h6>
                            <div>от 150 000 ₽</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="it-popular-card">
                        <h5><span class="icon"><i class="fas fa-robot"></i></span>Data Scientist</h5>
                        <p>Работает с большими данными, машинным обучением и аналитикой. Важно знать Python, математику, ML-библиотеки, SQL.</p>
                        <a href="profession.php?id=3" class="btn btn-outline-info btn-sm">Подробнее</a>
                        <div class="card-more">
                            <h6>Ключевые навыки</h6>
                            <ul>
                                <li>Python, ML, SQL</li>
                                <li>Математика, аналитика</li>
                                <li>Работа с Big Data</li>
                            </ul>
                            <h6>Перспективы</h6>
                            <ul>
                                <li>Востребованность в крупных компаниях</li>
                                <li>Развитие в AI/ML</li>
                            </ul>
                            <h6>Средняя зарплата</h6>
                            <div>от 180 000 ₽</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="it-popular-card">
                        <h5><span class="icon"><i class="fas fa-cloud"></i></span>DevOps-инженер</h5>
                        <p>Автоматизирует процессы разработки и внедрения, отвечает за инфраструктуру, CI/CD, облака, Docker, Kubernetes.</p>
                        <a href="profession.php?id=4" class="btn btn-outline-info btn-sm">Подробнее</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="it-popular-card">
                        <h5><span class="icon"><i class="fas fa-bug"></i></span>QA-инженер (тестировщик)</h5>
                        <p>Обеспечивает качество продуктов, пишет тесты, автоматизирует проверки, ищет баги. Важно знать тестовые методологии, инструменты автоматизации.</p>
                        <a href="profession.php?id=5" class="btn btn-outline-info btn-sm">Подробнее</a>
                                </div>
                            </div>
                <div class="col-md-4">
                    <div class="it-popular-card">
                        <h5><span class="icon"><i class="fas fa-project-diagram"></i></span>Product Manager</h5>
                        <p>Управляет продуктом, анализирует рынок, общается с командой и заказчиками, отвечает за успех продукта.</p>
                        <a href="profession.php?id=6" class="btn btn-outline-info btn-sm">Подробнее</a>
                    </div>
                </div>
            </div>

            <div class="it-section-title">Почему стоит выбрать IT?</div>
            <ul class="it-benefits-list mb-5">
                <li><i class="fas fa-check-circle"></i> Востребованность и высокие зарплаты</li>
                <li><i class="fas fa-check-circle"></i> Возможность удалённой работы и гибкого графика</li>
                <li><i class="fas fa-check-circle"></i> Быстрый карьерный рост и постоянное развитие</li>
                <li><i class="fas fa-check-circle"></i> Работа в международных командах и крупных компаниях</li>
                <li><i class="fas fa-check-circle"></i> Вклад в создание новых технологий и сервисов</li>
            </ul>

            <!-- Яркий CTA-блок -->
            <div class="cta-block">
                <div class="cta-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <div>
                    <h2>Не знаешь, с чего начать?</h2>
                    <p>Пройди бесплатный тест и узнай, какая IT-профессия тебе подходит!</p>
                    <a href="tests.php" class="cta-btn">Пройти тест</a>
                </div>
            </div>

            <div class="it-section-title">Как начать карьеру в IT?</div>
            <div class="it-steps mb-5">
                <ol>
                    <li><i class="fas fa-search"></i> Изучите направления и выберите профессию по душе</li>
                    <li><i class="fas fa-book-open"></i> Пройдите бесплатные тесты и определите свои сильные стороны</li>
                    <li><i class="fas fa-users"></i> Получите советы от экспертов и наставников</li>
                    <li><i class="fas fa-laptop-code"></i> Начните учиться и практиковаться на реальных задачах</li>
                    <li><i class="fas fa-rocket"></i> Постройте карьеру и развивайтесь вместе с ProfHub!</li>
                </ol>
            </div>

            <section class="mb-5">
                <div class="latest-prof-title">Последние добавленные профессии</div>
                <div class="row">
                    <?php while ($profession = $latest_professions_result->fetch_assoc()): ?>
                        <div class="col-md-4">
                            <div class="card profession-card fade-in">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($profession['name']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars(substr($profession['description'], 0, 150)) . '...'; ?></p>
                                    <a href="profession.php?id=<?php echo $profession['id']; ?>" class="btn btn-outline">Подробнее</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>

            <div class="row mb-5">
                <div class="col-12">
                    <div class="card profession-card fade-in">
                        <div class="card-body text-center">
                            <h5 class="card-title">
                                <i class="fas fa-rocket"></i> Начните свой путь в IT
                            </h5>
                            <p class="card-text">Присоединяйтесь к нашему сообществу и развивайтесь вместе с нами!</p>
                            <?php if (!isset($_SESSION['user_id'])): ?>
                                <a href="register.php" class="btn btn-primary">Зарегистрироваться</a>
                            <?php else: ?>
                                <a href="professions.php" class="btn btn-primary">Исследовать профессии</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Управление выпадающим меню профиля
    const profileBtn = document.querySelector('.profile-btn');
    const profileDropdown = document.querySelector('.profile-dropdown .dropdown-menu');
    
    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Закрываем все другие открытые меню
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if (menu !== profileDropdown) {
                    menu.classList.remove('show');
                }
            });
            
            // Переключаем текущее меню
            profileDropdown.classList.toggle('show');
        });
    }
    
    // Закрытие меню при клике вне его
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.profile-dropdown')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });
});
</script>
</body>
</html>
