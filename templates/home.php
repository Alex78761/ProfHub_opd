<?php
$stmt = $db->query("SELECT * FROM professions ORDER BY created_at DESC LIMIT 3");
$latest_professions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="jumbotron bg-light p-5 rounded-3 mb-4">
    <h1 class="display-4">Добро пожаловать в мир ИТ-профессий!</h1>
    <p class="lead">Наш портал поможет вам узнать больше о различных профессиях в сфере информационных технологий, получить экспертную оценку и консультацию специалистов.</p>
    <hr class="my-4">
    <p>Зарегистрируйтесь, чтобы получить доступ ко всем возможностям портала.</p>
    <a class="btn btn-primary btn-lg" href="/?page=register" role="button">Начать</a>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Каталог профессий</h5>
                <p class="card-text">Изучите подробные описания различных ИТ-профессий, требования и уровень зарплат.</p>
                <a href="/?page=professions" class="btn btn-outline-primary">Перейти к каталогу</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Экспертная оценка</h5>
                <p class="card-text">Получите профессиональную оценку и рекомендации от экспертов в области ИТ.</p>
                <a href="/?page=experts" class="btn btn-outline-primary">Найти эксперта</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Консультации</h5>
                <p class="card-text">Запишитесь на персональную консультацию с опытным специалистом.</p>
                <a href="/?page=consultations" class="btn btn-outline-primary">Записаться</a>
            </div>
        </div>
    </div>
</div>

<h2 class="mb-4">Последние добавленные профессии</h2>
<div class="row">
    <?php foreach ($latest_professions as $profession): ?>
        <div class="col-md-4 mb-4">
            <div class="card profession-card h-100">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($profession['title']); ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars(substr($profession['description'], 0, 150)) . '...'; ?></p>
                    <p class="card-text"><small class="text-muted">Зарплата: <?php echo htmlspecialchars($profession['salary_range']); ?></small></p>
                    <a href="/?page=profession&id=<?php echo $profession['id']; ?>" class="btn btn-primary">Подробнее</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?> 