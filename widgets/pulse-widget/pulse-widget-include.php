<?php
// Подключаем виджет стресса на все страницы
?>
<div id="pulse-widget-container" style="display: none;"></div>
<script src="/widgets/pulse-widget/pulse-widget.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const widget = new PulseWidget('pulse-widget-container');
        
        // Функция для переключения видимости виджета
        window.togglePulseWidget = function() {
            const container = document.getElementById('pulse-widget-container');
            if (container) {
                container.style.display = container.style.display === 'none' ? 'block' : 'none';
            }
        };
    });
</script> 