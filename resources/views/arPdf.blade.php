<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>صفحة عربية (RTL)</title>
    <style>
        body {
            direction: rtl;
            text-align: right;
            font-family: DejaVu Sans, serif;
            /*font-family: 'Arial', sans-serif; !* اختيار خط يدعم العربية *!*/
        }
        h1 {
            text-align: center; /* مثال على عنصر يغير الاتجاه/المحاذاة */
        }
    </style>
</head>
<body>
<h1>عنوان الصفحة باللغة العربية</h1>
<p>هذه فقرة نصية مكتوبة باللغة العربية، وتظهر من اليمين إلى اليسار (RTL) بفضل سمة `dir="rtl"` و CSS، كما هو موضح في [1, 2, 3].</p>
<div>
    <span>نص جانبي</span>
    <span>نص آخر</span>
</div>
<?php
$avatarUrl = 'https://static.vecteezy.com/system/resources/thumbnails/057/068/323/small/single-fresh-red-strawberry-on-table-green-background-food-fruit-sweet-macro-juicy-plant-image-photo.jpg';
$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);
$type = pathinfo($avatarUrl, PATHINFO_EXTENSION);
$avatarData = file_get_contents($avatarUrl, false, stream_context_create($arrContextOptions));
$avatarBase64Data = base64_encode($avatarData);
$imageData = 'data:image/' . $type . ';base64,' . $avatarBase64Data;

?>

<img style='display:block; width:100px;height:100px;' id='base64image' src='{{ $imageData }}' />

</body>
</html>
