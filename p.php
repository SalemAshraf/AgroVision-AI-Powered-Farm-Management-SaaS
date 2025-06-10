<?php
// توليد كلمة مرور مشفرة باستخدام Bcrypt
$password = '@Brand2002';  // ضع كلمة المرور التي تريد تشفيرها
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// عرض الكلمة المشفرة
echo "Encrypted password: " . $hashedPassword;
?>
