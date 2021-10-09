<?
function GetLibKill($filter = -1){
	global $link;
	global $chatid;
	$res = mysqli_query($link, "SELECT * FROM `LibraryKill` WHERE `PeerID` = '$chatid' AND `KillType` >= '$filter'");
	$returns = [];
    while($row = $res->fetch_assoc())
        $returns[] = Array($row['ID'], $row['PeerID'], $row['UserID'], $row['KillType'], $row['Text']);
	return $returns;
}
function SearchLibKill($text, $filter = -1){
	global $link;
	global $chatid;
	$res = mysqli_query($link, "SELECT * FROM `LibraryKill` WHERE `PeerID` = '$chatid' AND `KillType` >= '$filter' AND Text LIKE '%$text%'");
	$returns = [];
    while($row = $res->fetch_assoc())
        $returns[] = Array($row['ID'], $row['PeerID'], $row['UserID'], $row['KillType'], $row['Text']);
	return $returns;
}

function AddLibKill($userid, $type, $text){
	global $link;
	global $chatid;
	if($text == "" || ($type < -1 && $type > 3)) return false;
	mysqli_query($link, "INSERT INTO `LibraryKill`(`PeerID`, `UserID`, `KillType`, `Text`) VALUES ('$chatid','$userid','$type','$text')");
}
function RemoveLibKill($text){
	global $link;
	global $chatid; //text LIKE 'статьи%'
	
	if($text == "") return false;
	//$res = mysqli_query($link, "SELECT `Text` FROM `LibraryKill` WHERE `PeerID` = $chatid AND Text LIKE '%$text%'");
    //if(mysqli_num_rows($res) > 1) return false;
	
	mysqli_query($link, "DELETE FROM `LibraryKill` WHERE `PeerID` = '$chatid' AND Text = '$text'");
}
function SearchType($text){
	if(IsStr($text, ['бан', 'изгна', 'исклю'])) return 3;
	elseif(IsStr($text, ['повеш', "казнь"])) return 2;
	elseif(IsStr($text, ['расстред'])) return 1;
	elseif(IsStr($text, ['пред'])) return 0;
	else return -1;
}
function TypeString($type){
	if($type == 3) return "изгание из братства";
	elseif($type == 2) return "повешение";
	elseif($type == 1) return "расстрел";
	elseif($type == 0) return "предупреждение";
	else return "такого в словаре нет";
}

function AddMute($user, $type, $time){
    global $link;
	global $chatid;
    if(GetMuteUser($user) == false)
        mysqli_query($link, "INSERT INTO `Mute` (`UID`, `PID`, `MType`, `Time`) VALUES ('$user', '$chatid', '$type', '$time');");
}
function GetMute($filter = -1){
	global $link;
	global $chatid;
	$res = mysqli_query($link, "SELECT * FROM `Mute` WHERE `PID` = '$chatid' AND `MType` >= '$filter'");
	$returns = [];
    while($row = $res->fetch_assoc())
        $returns[] = Array($row['ID'], $row['UID'], $row['PID'], $row['MType'], $row['Time'], $row['Comment']);
	return $returns;
}
function GetMuteUser($user){
	global $link;
	global $chatid;
	$res = mysqli_query($link, "SELECT * FROM `Mute` WHERE `PID` = '$chatid' AND `UID` = '$user'");
	$returns = [];
    if(mysqli_num_rows($res) < 1) return false;
    while($row = $res->fetch_assoc())
        $returns[] = Array($row['ID'], $row['UID'], $row['PID'], $row['MType'], $row['Time'], $row['Comment']);
	return $returns;
}
function RemoveMute($user){
	global $link;
	global $chatid; //text LIKE 'статьи%'
	mysqli_query($link, "DELETE FROM `Mute` WHERE `PID` = '$chatid' AND `UID` = '$user'");
}
function TypeStringMute($type){
	if($type == 2) return "повешение";
	elseif($type == 1) return "расстрел";
	elseif($type == 0) return "воля администратора";
	else return "такого повода нет";
}
?>