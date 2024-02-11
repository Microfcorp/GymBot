<?php require_once("BotSet.php"); ?>
<?php require_once("func.php"); ?>
<?php require_once("gachi.php"); ?>
<?php
// Этот текст был использован в 2002 году
// мы хотим обновить даты к 2003 году
$text = "День смеха был 01/04/2002\n";
$text.= "Последнее Рождество было 24/12/2001\n";
$text.= "Последнее [id192585654] bom";
$text.= "Последнее [id192585679] bom";
// callback-функция

echo preg_replace_callback(
            "/[id+.[0-9]+]/",
            function ($matches)
            {
              $tpu = doubleval(preg_replace("/[^-0-9\.]/","",$matches[0]));
              return "GET(".$tpu.");";
            },
            $text);
			
			$photo = _bot_uploadPhoto($LoveFiles[mt_rand(0, count($LoveFiles) - 1)]);
    foreach(GetPeers() as $tmp)
        MessSendAttach($tmp, "@vtretiak5, просто так", 'photo'.$photo['owner_id'].'_'.$photo['id']);
?>