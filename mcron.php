<?php require_once("BotSet.php"); ?>
<?php require_once("func.php"); ?>
<?php require_once("gachi.php"); ?>
<?php require_once("KillLib.php"); ?>

<?php
//////////////КАЖДЫЕ 1 МИНУТУ РАБОТАЕТ

    foreach(GetPeers() as $tmp){
        $chatid = $tmp;
        $mutes = GetMute();
        foreach($mutes as $tt){
            $id = $tt[0];
            
            $t2 = explode(':', $tt[4]);
            $m = (intval($t2[0]) * 60) + intval($t2[1]) /*+ (intval($t2[2]) / 60)*/;
            $m -= 1;
            
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
        
        if(GetGayBar()){
            $lasttime = explode(':', GetLastTimeGayBar());
            $lt = $lasttime[0]*60 + $lasttime[1] + $lasttime[2]/60;
            $currenttime = explode(':', date('H:i:s'));
            $ct = $currenttime[0]*60 + $currenttime[1] + $currenttime[2]/60;
            
            if(abs($ct - $lt) >= 60){
                MessSend($chatid, "♂Gay Bar♂ закрыт из за часового отсутствия активности славян в нем");
                SetGayBar(false);
            }
        }
    }   
?>
