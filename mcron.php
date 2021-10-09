<?php require_once("BotSet.php"); ?>
<?php require_once("func.php"); ?>
<?php require_once("gachi.php"); ?>
<?php require_once("KillLib.php"); ?>

<?php
    foreach(GetPeers() as $tmp){
        $chatid = $tmp;
        $mutes = GetMute();
        foreach($mutes as $tt){
            $id = $tt[0];
            
            $t2 = explode(':', $tt[4]);
            $m = (intval($t2[0]) * 60) + intval($t2[1]) /*+ (intval($t2[2]) / 60)*/;
            $m -= 10;
            
            if($m <= 0){
                MessSend($tt[2], "Время молчания товарища ".GetLinkUser($tt[1])." истекло. Мут снят");
                RemoveMute($tt[1]);
            }
            else{      
                $hours = floor($m / 60);
                $minutes = $m % 60;            
                $t1 = $hours . ":" . $minutes . ":00";       
                $sql = "UPDATE `Mute` SET `Time`='$t1' WHERE `ID`='$id'";            
                mysqli_query($link, $sql);
            }
        }
    }
?>
