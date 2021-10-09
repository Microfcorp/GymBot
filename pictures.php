<?php

/**
* Скругление углов картинки
*
* @param $image - картинка
* @param $radius - радиус скругления
* @param $background - цвет фона для скруглений
*
* @return изображение
*/
function makeCornersForImage($image, $radius, $background){
    // загружаем картинку
    $img = imagecreatefromjpeg($image);
    // включаем режим сопряжения цветов
    imagealphablending($img, true);
    // размер исходной картинки
    $width = imagesx($img);
    $height = imagesy($img);
    // создаем изображение для углов
    $corner = imagecreatetruecolor($radius, $radius);
    imagealphablending($corner, false);
    // прозрачный цвет
    $trans = imagecolorallocatealpha($corner, 255, 255, 255, 127);
    // заливаем картинку для углов
    imagefill($corner, 0, 0, $background);
    // рисуем прозрачный эллипс
    imagefilledellipse($corner, $radius, $radius, $radius * 2, $radius * 2, $trans);
    // массив положений. Для расположения по углам
    $positions = array(
        array(0, 0),
        array($width - $radius, 0),
        array($width - $radius, $height - $radius),
        array(0, $height - $radius),
    );
    // накладываем на углы картинки изображение с прозрачными эллипсами
    foreach ($positions as $pos) {
        imagecopyresampled($img, $corner, $pos[0], $pos[1], 0, 0, $radius, $radius, $radius, $radius);
        // поворачиваем картинку с эллипсов каждый раз на 90 градусов
        $corner = imagerotate($corner, -90, $background, false);
    }
    // вернем картинку
    return $img;
}
function KillGD($userid, $savepath = "photo/result.jpg"){
	global $token;
	
	$typevisel = random_int(0,1) == 1;
	
	$img    = ImageCreateFromJpeg($typevisel ? 'photo/vis.jpg' : 'photo/poves.jpg');
	$reqvk = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$userid}&v=5.130&fields=photo_200,photo_100&access_token={$token}"))->response[0];
	file_put_contents("photo/users/$userid.jpg", file_get_contents($typevisel ? $reqvk->photo_200 : $reqvk->photo_100));
	$logo   = makeCornersForImage("photo/users/$userid.jpg", ($typevisel ? 80 : 40), 0x00000f);
	 
	imagecopy($img, $logo, ($typevisel ? 200 : 175), ($typevisel ? 310 : 250), 0, 0, ($typevisel ? 195 : 95), ($typevisel ? 195 : 95));
	 
	imagejpeg($img, $savepath);
	imagedestroy($img);
	return $savepath;
}
function RastrelGD($userid, $chekist, $savepath = "photo/result.jpg"){
	global $token;
	
	$img    = ImageCreateFromJpeg($chekist ? 'photo/rasterl.jpg' : 'photo/rasstrel.jpg');
	file_put_contents("photo/users/$userid.jpg", file_get_contents(json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$userid}&v=5.130&fields=photo_100&access_token={$token}"))->response[0]->photo_100));
	$logo   = makeCornersForImage("photo/users/$userid.jpg", ($chekist ? 80 : 40), 0x00000f);
	 
	imagecopy($img, $logo, ($chekist ? 850 : 1270), ($chekist ? 240 : 520), 0, 0, 95, 95);
	 
	imagejpeg($img, $savepath);
	imagedestroy($img);
	return $savepath;
}



?>
