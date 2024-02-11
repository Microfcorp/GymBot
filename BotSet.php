<?php
$token = "97b4943bafa6853b79346b525e3c21f18910b911865d984eb57014c799b5d27e09f77a82ec33bed28b8c9";
//$token = "f446cc22ba178a447ab722dbab2711ec231e5634c2a1e8812af56b837c0973f3368450121ceecb29f3296";
//$token = "c8645621953db9b3e984642744f3af2e305421b151b457b9bf6e7c6d915f6a04c25139932f838fb0220b3"; //Сюда пишешь свой токен
$confirmationToken = "1b54be91"; //Сюда пишешь, что возвращать на подтверждение
$secretKey = "gymboteee"; //Секретный ключ, который короткий
///Настройки бота
$chunkCounts = 4000; //Количество символов в одном чанке сообщения
define("BOTVERSION", "1.8.1"); //Версия бота
define("STARTDAYRAB", 8); //Начало рабочего дня
define("ENDDAYRAB", 18); //Конец рабочего дня

$SAudio = ["audio5720245_456239100"];

$LoveFiles = ["photo/love/love1.jpg","photo/love/love2.jpg","photo/love/love3.jpg","photo/love/love4.jpg","photo/love/love5.jpg","photo/love/love6.jpg","photo/love/love7.jpg","photo/love/love8.jpg","photo/love/love9.jpg","photo/love/love10.jpg"];

///////////////////////////////////////////////////////////
$db_host = 'localhost';
	$db_user = 'root';
	$db_password = 'MichomeServerBD2019'; //SuperSecretPasswordPHPMyaDMInGgGg
	$db_name = 'GymBot';
	
	$link = mysqli_connect($db_host, $db_user, $db_password, $db_name);
	if (!$link) {
    	echo('<p style="color:red"> Error conected to MySql</p>');
	}
	
$Month_r = array(
"01" => ["январь", "январь"],
"02" => ["февраль", "февраля"],
"03" => ["март", "марта"],
"04" => ["апрель", "апреля"],
"05" => ["май", "мая"],
"06" => ["июнь", "июня"],
"07" => ["лесбиюль", "лесбиюля"],
"08" => ["август", "августа"],
"09" => ["сентябрь", "сентября"],
"10" => ["октябрь", "октября"],
"11" => ["недрочабрь", "недрочабря"],
"12" => ["небриябрь", "небриября"]);
?>