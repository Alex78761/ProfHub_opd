<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "db-connect.php";

// Проверка роли пользователя
if (isset($_SESSION['user_id'])) {
    $check_role_stmt = $conn->prepare("SELECT u.id, u.username, u.role FROM users u WHERE u.id = ?");
    $check_role_stmt->bind_param("i", $_SESSION['user_id']);
    $check_role_stmt->execute();
    $check_role_result = $check_role_stmt->get_result();
    if ($check_role_row = $check_role_result->fetch_assoc()) {
        $_SESSION['role'] = $check_role_row['role'];
    }
    $check_role_stmt->close();
}
?>

<header class="header">
    <div class="header-container">
        <!-- Логотип -->
        <a class="logo" href="index.php">ProfHub</a>
        
        <!-- Основное меню -->
        <nav class="main-nav">
            <ul class="nav-list">
                <li class="nav-item">
                    <a class="nav-link<?php if(basename($_SERVER['PHP_SELF'])=='professions.php') echo ' active'; ?>" href="professions.php">Профессии</a>
                </li>
                
                <?php if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'expert', 'consultant'])): ?>
                <li class="nav-item dropdown">
                    <div class="dropdown-wrapper">
                        <button class="nav-link dropdown-btn<?php if(in_array(basename($_SERVER['PHP_SELF']), ['tests.php', 'test_results.php'])) echo ' active'; ?>">
                            Тесты <i class="fas fa-chevron-down dropdown-arrow"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item<?php if(basename($_SERVER['PHP_SELF'])=='tests.php') echo ' active'; ?>" href="tests.php">Пройти тест</a>
                            <a class="dropdown-item<?php if(basename($_SERVER['PHP_SELF'])=='test_results.php') echo ' active'; ?>" href="view_test_results.php">Результаты тестов</a>
                        </div>
                    </div>
                </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a class="nav-link<?php if(basename($_SERVER['PHP_SELF'])=='chat.php') echo ' active'; ?>" href="chat.php">Чат</a>
                </li>
            </ul>
        </nav>

        <!-- Профиль/авторизация -->
        <div class="auth-section">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a class="auth-btn login-btn" href="login.php">Войти</a>
                <a class="auth-btn register-btn" href="register.php">Регистрация</a>
            <?php else: ?>
                <div class="profile-dropdown">
                    <button class="profile-btn" id="profileDropdownBtn">
                        <i class="fas fa-user-circle"></i>
                        <span>Мой профиль</span>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </button>
                    <div class="dropdown-menu profile-menu" id="profileDropdown">
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a class="dropdown-item" href="admin_professions.php">
                                <i class="fas fa-edit"></i> Редактировать профессии
                            </a>
                            <div class="dropdown-divider"></div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'expert'): ?>
                            <a class="dropdown-item" href="expert_panel.php">
                                <i class="fas fa-edit"></i> Оценка профессий
                            </a>
                            <div class="dropdown-divider"></div>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Выйти
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Кнопка мобильного меню -->
        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <span class="menu-line"></span>
            <span class="menu-line"></span>
            <span class="menu-line"></span>
        </button>
    </div>
</header>

<style>
/* Добавляем стили для выпадающего меню тестов */
.dropdown-wrapper {
    position: relative;
    display: inline-block;
}

.dropdown-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    font: inherit;
    color: inherit;
}

.nav-item.dropdown .dropdown-menu {
    display: none;
    position: absolute;
    background-color: white;
    min-width: 200px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    z-index: 1000;
    border-radius: 4px;
    padding: 8px 0;
}

.nav-item.dropdown.active .dropdown-menu {
    display: block;
}

.dropdown-item {
    display: block;
    padding: 8px 16px;
    color: #333;
    text-decoration: none;
    transition: background-color 0.2s;
}

.dropdown-item:hover {
    background-color: #f5f5f5;
}

.dropdown-item.active {
    background-color: #e9ecef;
    color: #495057;
}

.dropdown-divider {
    height: 1px;
    background-color: #e9ecef;
    margin: 4px 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Управление выпадающим меню тестов
    const testDropdowns = document.querySelectorAll('.nav-item.dropdown .dropdown-btn');
    
    testDropdowns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const dropdown = this.closest('.nav-item.dropdown');
            
            // Закрываем все другие открытые меню
            document.querySelectorAll('.nav-item.dropdown').forEach(item => {
                if (item !== dropdown) {
                    item.classList.remove('active');
                }
            });
            
            // Переключаем текущее меню
            dropdown.classList.toggle('active');
        });
    });
    
    // Управление выпадающим меню профиля
    const profileBtn = document.getElementById('profileDropdownBtn');
    const profileMenu = document.getElementById('profileDropdown');
    
    if (profileBtn && profileMenu) {
        profileBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Закрываем все другие открытые меню
            document.querySelectorAll('.profile-dropdown').forEach(dropdown => {
                if (dropdown !== this.closest('.profile-dropdown')) {
                    dropdown.classList.remove('active');
                }
            });
            
            // Переключаем текущее меню
            this.closest('.profile-dropdown').classList.toggle('active');
        });
    }
    
    // Закрытие меню при клике вне его
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.nav-item.dropdown')) {
            document.querySelectorAll('.nav-item.dropdown').forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
        
        if (!e.target.closest('.profile-dropdown')) {
            document.querySelectorAll('.profile-dropdown').forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });
    
    // Мобильное меню
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const header = document.querySelector('.header');
    
    if (mobileMenuBtn && header) {
        mobileMenuBtn.addEventListener('click', function() {
            header.classList.toggle('mobile-menu-open');
            
            const menuLines = document.querySelectorAll('.menu-line');
            if (header.classList.contains('mobile-menu-open')) {
                menuLines[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                menuLines[1].style.opacity = '0';
                menuLines[2].style.transform = 'rotate(-45deg) translate(5px, -5px)';
            } else {
                menuLines.forEach(line => {
                    line.style.transform = '';
                    line.style.opacity = '';
                });
            }
        });
    }
    
    // Обновление непрочитанных сообщений
    <?php if (isset($_SESSION['user_id'])): ?>
    function updateUnreadMessages() {
        fetch('get_unread_messages.php')
            .then(response => response.json())
            .then(data => {
                const chatLink = document.querySelector('a[href="chat.php"]');
                if (chatLink) {
                    const existingBadge = chatLink.querySelector('.message-badge');
                    
                    if (data.unread > 0) {
                        if (existingBadge) {
                            existingBadge.textContent = data.unread;
                        } else {
                            const badge = document.createElement('span');
                            badge.className = 'message-badge';
                            badge.textContent = data.unread;
                            chatLink.appendChild(badge);
                        }
                    } else if (existingBadge) {
                        existingBadge.remove();
                    }
                }
            })
            .catch(error => console.error('Ошибка при получении непрочитанных сообщений:', error));
    }
    
    setInterval(updateUnreadMessages, 30000);
    updateUnreadMessages();
    <?php endif; ?>
});
</script>