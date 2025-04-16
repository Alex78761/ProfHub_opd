<?php
$admin_password = 'admin123';
$consultant_password = 'consultant123';

echo "Admin password hash: " . password_hash($admin_password, PASSWORD_BCRYPT) . "\n";
echo "Consultant password hash: " . password_hash($consultant_password, PASSWORD_BCRYPT) . "\n";
?> 