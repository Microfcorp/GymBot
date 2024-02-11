<?php require_once("BotSet.php"); ?>
<?php require_once("func.php"); ?>
<?php require_once("gachi.php"); ?>

<?php
$TH = date("H"); 
$TM = date("i"); 

function CheckPost($ownerid){
	$stc = (file_exists('countwall/'.$ownerid) ? intval(file_get_contents('countwall/'.$ownerid)) : 1);
	$wal = GetLastWall($ownerid, true);
	$newc = $wal[1];
    $groupname = getGroupsById(mb_substr($ownerid, 1))[0]['name'];
	echo "$newc vs $stc<br>";
	if($newc > $stc) foreach(GetPeers() as $tmp) MessSendAttach($tmp, "Новая запись в сообщетсве [club".mb_substr($ownerid, 1)."|".$groupname."], которая уже была просмотрена ".$wal[0]['views']['count']." раз и собрала ".$wal[0]['likes']['count']." лайков", "wall".$wal[0]['owner_id']."_".$wal[0]['id']);
	//if($newc < $stc) foreach(GetPeers() as $tmp) MessSendAttach($tmp, "В сообщетсве [club".mb_substr($ownerid, 1)."|@".mb_substr($ownerid, 1)."] была удалена запись. Последняя запись на текущий момент:", "wall".$ownerid."_".$wal['items'][0]['id']);
}
//паблик бота
//CheckPost("-203187765");
//паблик славян
//CheckPost("-165104294");
//гачи на каждый день
//CheckPost("-113661329");

if($TH == 10 && $TM < 30){ //Просьба доната
    $photo = _bot_uploadPhoto('photo/pohval.jpg');
    foreach(GetPeers() as $tmp)
        MessSendAttach($tmp, "Товарищи, поддержите бота и Лёху на https://donationalerts.com/r/razbiyan", 'photo'.$photo['owner_id'].'_'.$photo['id']);
}

if(($TH == 7 && $TM < 30) || ($TH == 15 && $TM < 30)){ //Моя огромная любовь к Виктории
    $photo = _bot_uploadPhoto($LoveFiles[mt_rand(0, count($LoveFiles) - 1)]);
    foreach(GetPeers() as $tmp)
        MessSendAttach($tmp, "@vtretiak5, просто так", 'photo'.$photo['owner_id'].'_'.$photo['id']);
}

if($TH == 12 && $TM < 30 && false){ //Сводка информации      
    foreach(GetPeers() as $tmp){
		$chatid = $tmp;
		$photo = _bot_uploadPhoto(IsRabDay() ? 'photo/spanzavod.jpg' : 'photo/pivoall.jpg');
        MessSendAttach($tmp, "Полдень, братья\n".GenerateSlavyanVodka($tmp), 'photo'.$photo['owner_id'].'_'.$photo['id']);
        //MessSendAttach($tmp, "Пусть этот день с вами будет:", GenerateRandomAudioAttachmnt());
    }
}
?>
