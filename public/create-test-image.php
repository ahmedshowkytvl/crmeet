<?php
// إنشاء صورة اختبار بسيطة
$width = 400;
$height = 400;

// إنشاء canvas
$image = imagecreatetruecolor($width, $height);

// ألوان
$background = imagecolorallocate($image, 240, 240, 240);
$skin = imagecolorallocate($image, 255, 219, 172);
$hair = imagecolorallocate($image, 139, 69, 19);
$eyes = imagecolorallocate($image, 0, 0, 0);
$shirt = imagecolorallocate($image, 74, 144, 226);

// رسم الخلفية
imagefill($image, 0, 0, $background);

// رسم الوجه (دائرة)
imagefilledellipse($image, 200, 150, 160, 160, $skin);

// رسم الشعر
imagefilledellipse($image, 200, 120, 180, 100, $hair);

// رسم العينين
imagefilledellipse($image, 180, 130, 16, 16, $eyes);
imagefilledellipse($image, 220, 130, 16, 16, $eyes);

// رسم الأنف
imagefilledellipse($image, 200, 150, 10, 10, $skin);

// رسم الفم (قوس)
imagearc($image, 200, 170, 40, 20, 0, 180, $eyes);

// رسم الجسم
imagefilledrectangle($image, 150, 230, 250, 350, $shirt);

// حفظ الصورة
imagejpeg($image, 'test-portrait.jpg', 90);

// تنظيف الذاكرة
imagedestroy($image);

echo "تم إنشاء صورة الاختبار: test-portrait.jpg";
?>

