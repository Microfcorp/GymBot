<?php require_once("BotSet.php"); ?>
<?php require_once("func.php"); ?>
<?php require_once("gachi.php"); ?>

<?php
$TH = date("H"); 
$TM = date("i"); 

function CheckPost($ownerid){
	$stc = (file_exists('countwall/'.$ownerid) ? intval(file_get_contents('countwall/'.$ownerid)) : 1);
	$wal = GetWall($ownerid);
	$newc = $wal['count'];
	echo "$newc vs $stc<br>";
	if($newc > $stc) foreach(GetPeers() as $tmp) MessSendAttach($tmp, "Новая запись в сообщетсве [club".mb_substr($ownerid, 1)."|@".mb_substr($ownerid, 1)."]", "wall".$ownerid."_".$wal['items'][0]['id']);
	//if($newc < $stc) foreach(GetPeers() as $tmp) MessSendAttach($tmp, "В сообщетсве [club".mb_substr($ownerid, 1)."|@".mb_substr($ownerid, 1)."] была удалена запись. Последняя запись на текущий момент:", "wall".$ownerid."_".$wal['items'][0]['id']);
}
//мойпаблик
#CheckPost("-203187765");
//славяне
#CheckPost("-165104294");
//гачи
#CheckPost("-113661329");

if($TH == 10 && $TM < 30){
    $photo = _bot_uploadPhoto('photo/pohval.jpg');
    foreach(GetPeers() as $tmp)
        MessSendAttach($tmp, "Товарищи, поддержите бота на https://donationalerts.com/r/microf", 'photo'.$photo['owner_id'].'_'.$photo['id']);
}
?>
