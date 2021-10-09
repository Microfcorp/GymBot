<?php
$Gachirole = Array('4' => "Dungeon Masters", '1' => "Fucking Slave", '3' => "Billy", '0' => "Anal", '2' => "Boy next door");
$CumType = Array('0' => "Cum on face", '1' => "Cum on Anal", '2' => "Cum on head", '3' => "Cum on dick");
function AllRole(){
	global $Gachirole;
	$str = "";
	foreach ($Gachirole as $tmp)
		$str .= "♂" . $tmp . "♂<br>";
	return $str;
}
function RoleToEnglish($roleid){
	global $Gachirole;
	return $Gachirole[$roleid];
}
function EnglishToRole($roleid){
	global $Gachirole;
	$G1 = $Gachirole;
	array_walk_recursive($G1, function(&$item) {$item = strtolower($item);});
	return array_search(strtolower($roleid), $G1);
}
function GetRole($userid){
	global $link;
	global $chatid;
    $res = mysqli_query($link, "SELECT `GachiRole` FROM `Users` WHERE `ID_Chat`='$chatid' AND `ID` = ".$userid);
    while($row = $res->fetch_assoc())
        return $row['GachiRole'];
}
function ChangeRole($userid, $roleid){
	//UPDATE `Users` SET `ID_Chat`=[value-1],`ID`=[value-2],`Name`=[value-3],`GachiRole`=[value-4] WHERE 1
	global $link;
	global $chatid;
    $res = mysqli_query($link, "UPDATE `Users` SET `GachiRole`='$roleid' WHERE `ID_Chat`='$chatid' AND `ID` = ".$userid);
}
function GetRandomCum(){
	global $CumType;
	return $CumType[rand(0, count($CumType))];
}
?>