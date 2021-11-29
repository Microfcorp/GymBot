<?
function GetPeers(){
	global $link;
	$res = mysqli_query($link, "SELECT `ID` FROM `Peers` WHERE 1");
	$tmp = [];
    while($row = $res->fetch_assoc())
		$tmp[] = $row['ID'];
	return $tmp;
}
//890
function MessSend($peer_id, $message){
	global $chunkCounts;
	$rts = [];
	$chanks = str_split($message, $chunkCounts);
	foreach($chanks as $tmp)
		$rts[] = MessSendChank($peer_id, "\t ".$tmp);
	return $rts;
}
function MessSendChank($peer_id, $message){
	global $token;
	$request_params = array(
            'message' => $message,
            'peer_ids' => $peer_id,
            'access_token' => $token,
			'random_id' => random_int(-2147483647, 2147483647),
            'v' => '5.130',            
        );
 
       $get_params = http_build_query($request_params);  

       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/messages.send?' . $get_params);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	   //curl_setopt($ch, CURLOPT_POST, 1);
       $m = @curl_exec($ch);
       curl_close($ch);
       return json_decode($m, true)['response'][0]['conversation_message_id'];
}
function MessSendAttach($peer_id, $message, $atach){
	global $token;
	$request_params = array(
            'message' => $message,
            'peer_id' => $peer_id,
			'attachment' => $atach,
            'access_token' => $token,
			'random_id' => 0,
            'v' => '5.89',            
        );
 
       $get_params = http_build_query($request_params);  

       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/messages.send?' . $get_params);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       $m = @curl_exec($ch);
       curl_close($ch);

       return json_decode($m, true);
}
function MessSendReply($peer_id, $message, $userid){
	return MessSend($peer_id, "[id$userid|".GetName($userid)."], " . $message);
}
function MessDelete($peer_id, $message){
	global $token;
	$request_params = array(
            'conversation_message_ids' => $message,
            'peer_id' => $peer_id,
            'delete_for_all' => '1',
            'access_token' => $token,
            'v' => '5.130',            
        );
 
       $get_params = http_build_query($request_params);  

       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/messages.delete?' . $get_params);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	   //curl_setopt($ch, CURLOPT_POST, 1);
       $m = @curl_exec($ch);
       curl_close($ch);
       file_put_contents("d.txt", $m);
       return json_decode($m, true);
}
function MessEdit($peer_id, $mesid, $message){
	global $token;
	global $chatid;
	$request_params = array(
            'message' => $message,
            'peer_id' => $chatid,
            'conversation_message_id' => $mesid,
            'access_token' => $token,
            'v' => '5.80',            
        );
 
        $get_params = http_build_query($request_params);  

       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/messages.edit?' . $get_params);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       $m = @curl_exec($ch);
       curl_close($ch);

	   //file_put_contents("t.txt", $m);
       return json_decode($m, true)['response'];
}
function MessUpdate($peer_id, $mesid, $message){
	global $token;
	$request_params = array(
            'delete_for_all' => '1',
            'message_ids' => $mesid,
            'access_token' => $token,
            'v' => '5.80',            
        );
 
        $get_params = http_build_query($request_params);  

       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/messages.delete?' . $get_params);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       $m = @curl_exec($ch);
       curl_close($ch);

       $sm = MessSend($peer_id, $message);
       
       SetCurrentMessage($peer_id, $sm);
       return [json_decode($m, true)['response'], $sm];
}
function MessRead($peer_id, $mesid){
	global $token;
	$request_params = array(
            'peer_id' => $peer_id,
            'message_ids' => $mesid,
            'start_message_id' => '1',
            'access_token' => $token,
            'v' => '5.80',            
        );
 
        $get_params = http_build_query($request_params);  

       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/messages.markAsRead?' . $get_params);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       $m = @curl_exec($ch);
       curl_close($ch);

       return json_decode($m, true)['response'];
}
function getConversationMembers(){
	global $token;
	global $chatid;
	$request_params = array(
            'peer_id' => $chatid,
            'access_token' => $token,
            'v' => '5.130',            
        );
    $get_params = http_build_query($request_params);  
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/messages.getConversationMembers?' . $get_params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $m = @curl_exec($ch);
    curl_close($ch);
	//file_put_contents("t.txt", $m);
    return json_decode($m, true)['response'];
}

function getInviteLink(){
	global $token;
	global $chatid;
	$request_params = array(
            'peer_id' => $chatid,
            'reset' => 0,
            'access_token' => $token,
            'v' => '5.130',            
        );
    $get_params = http_build_query($request_params);  
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/messages.getInviteLink?' . $get_params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $m = @curl_exec($ch);
    curl_close($ch);
	//file_put_contents("t.txt", $m);
    return json_decode($m, true)['response'];
}

function _vkApi_call($method, $params = array()) {
	global $token;
  $params['access_token'] = $token;
  $params['v'] = "5.130";

  $query = http_build_query($params);
  $url = 'https://api.vk.com/method/'.$method.'?'.$query;

  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $json = curl_exec($curl);

  curl_close($curl);

  $response = json_decode($json, true);
  return $response['response'];
}

function vkApi_photosGetMessagesUploadServer() {
	global $chatid;
  return _vkApi_call('photos.getMessagesUploadServer', array(
    'peer_id' => $chatid,
  ));
}

function vkApi_photosSaveMessagesPhoto($photo, $server, $hash) {
  return _vkApi_call('photos.saveMessagesPhoto', array(
    'photo'  => $photo,
    'server' => $server,
    'hash'   => $hash,
  ));
}

function vkApi_setChatPhoto($upload) {
  return _vkApi_call('messages.setChatPhoto', array(
    'file'  => $upload,
  ));
}

function vkApi_docsSave($file, $title) {
  return _vkApi_call('docs.save', array(
    'file'  => $file,
    'title'  => $title,
    'tag'  => $title,
  ));
}

function vkApi_storiesSave($respa) {
  return _vkApi_call('stories.save', array(
    'upload_results'  => $respa,
  ));
}

function vkApi_docsGetMessagesUploadServer($type) {
	global $chatid;
  return _vkApi_call('docs.getMessagesUploadServer', array(
    'peer_id' => $chatid,
    'type'    => $type,
  ));
}

function vkApi_GetChatUploadServer($x, $y, $size) {
	global $chatid;
  return _vkApi_call('photos.getChatUploadServer', array(
    'chat_id' => $chatid,
    'crop_x'    => $x,
    'crop_y'    => $y,
    'crop_width'    => $size,
  ));
}

function vkApi_GetPhotoStoriesUploadServer($addtonews, $users = '') {
	global $chatid;
  return _vkApi_call('stories.getPhotoUploadServer', array(
    'add_to_news' => $addtonews,
    'user_ids'    => $users,
  ));
}

function uploadPhoto($url, $file_name) {
  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_POSTFIELDS, array('file' => new CURLfile($file_name)));
  $json = curl_exec($curl);
  curl_close($curl);

  $response = json_decode($json, true);

  return $response;
}

function _bot_uploadPhoto($file_name) {
  $upload_server_response = vkApi_photosGetMessagesUploadServer();
  $upload_response = uploadPhoto($upload_server_response['upload_url'], $file_name);

  $photo = $upload_response['photo'];
  $server = $upload_response['server'];
  $hash = $upload_response['hash'];

  $save_response = vkApi_photosSaveMessagesPhoto($photo, $server, $hash);
  $photo = array_pop($save_response);

  return $photo;
}

function _bot_uploadAM($file_name) {
  $upload_server_response = vkApi_docsGetMessagesUploadServer("audio_message");
  $upload_response = uploadPhoto($upload_server_response['upload_url'], $file_name);

  $file = $upload_response['file'];

  $save_response = vkApi_docsSave($file, basename($file, ".ogg"));
  $photo = array_pop($save_response);

  return $photo;
}

function _bot_uploadChatAva($file_name) {///////////////
  $upload_server_response = vkApi_GetChatUploadServer('300','300','200');
  //var_dump($upload_server_response);
  $upload_response = uploadPhoto($upload_server_response['upload_url'], $file_name);

  $file = $upload_response['response'];

  $save_response = vkApi_setChatPhoto($file);
  $photo = array_pop($save_response);

  return $photo;
}

function _bot_PublishPhotoStories($file_name, $addtonews = 1, $users = '') {///////////////
  $upload_server_response = vkApi_GetPhotoStoriesUploadServer($addtonews, $users);
  //var_dump($upload_server_response);
  $upload_response = uploadPhoto($upload_server_response['upload_url'], $file_name);

  $file = $upload_response['response'];

  $save_response = vkApi_storiesSave($file);
  $photo = array_pop($save_response);

  return $photo;
}

function getGroupsById($groupid){
	global $token;
	$request_params = array(
            'group_id' => $groupid,
            'access_token' => '204626222046262220462622fe201be021220462046262279cbce62c5301f21e554f471',
            'v' => '5.130',            
        );
    $get_params = http_build_query($request_params);  
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/groups.getById?' . $get_params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $m = @curl_exec($ch);
    curl_close($ch);
	//file_put_contents("t.txt", $m);
    return json_decode($m, true)['response'];
}

function searchMessage($text){ //только для лс, не для беседы
	global $token;
	global $chatid;
	$request_params = array(
            'q' => $text,
            'peer_id' => $chatid,
            'access_token' => $token,
            'v' => '5.131',            
        );
    $get_params = http_build_query($request_params);  
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/messages.search?' . $get_params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $m = @curl_exec($ch);
    curl_close($ch);
	//file_put_contents("t.txt", $m);
    return json_decode($m, true)['response'];
}

function PinMessage($messid){
	global $token;
	global $chatid;
	$request_params = array(	
            'peer_id' => $chatid,
            'conversation_message_id' => $messid,
            'access_token' => $token,
            'v' => '5.130',            
        );
    $get_params = http_build_query($request_params);  
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/messages.pin?' . $get_params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $m = @curl_exec($ch);
    curl_close($ch);
	file_put_contents("t.txt", $m);
    return json_decode($m, true);
}

function getConversationsById($chatid){
	global $token;
	$request_params = array(
            'peer_ids' => $chatid,
            'extended' => 0,
            'access_token' => $token,
            'v' => '5.130',            
        );
    $get_params = http_build_query($request_params);  
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/messages.getConversationsById?' . $get_params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $m = @curl_exec($ch);
    curl_close($ch);
	//file_put_contents("t.txt", $m);
    return json_decode($m, true)['response'];
}

function setActivity(){
	global $token;
	global $chatid;
	$request_params = array(
            'peer_id' => $chatid,
            'type' => 'typing',
            'access_token' => $token,
            'v' => '5.130',            
        );
    $get_params = http_build_query($request_params);  
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/messages.setActivity?' . $get_params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $m = @curl_exec($ch);
    curl_close($ch);
    return json_decode($m, true);
}

function kickUser($userid){
	if(IsAdmin($userid)) return FALSE;
	global $token;
	global $chatid;
	$request_params = array(
            'chat_id' => $chatid - 2000000000,
            'member_id' => $userid,
            'access_token' => $token,
            'v' => '5.130',            
        );
    $get_params = http_build_query($request_params);  
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/messages.removeChatUser?' . $get_params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $m = @curl_exec($ch);
    curl_close($ch);
    file_put_contents("r.txt", $m);
    return json_decode($m, true);
}

function GetLastWall($ownerid, $savecount = false){ //последдний пост и общее колличество
	$wall = GetWall($ownerid, 0, 2, $savecount);
    if(isset($wall['items'][0]['is_pinned']) && $wall['items'][0]['is_pinned'] == '1')
        return [$wall['items'][1], $wall['count']];
    else
        return [$wall['items'][0], $wall['count']];
}

function GetWall($ownerid, $offset = 0, $count = 1, $issavecount = true){
	$request_params = array(
            'owner_id' => $ownerid,
            'count' => $count,
			'offset' => $offset,
            'access_token' => '204626222046262220462622fe201be021220462046262279cbce62c5301f21e554f471',
            'v' => '5.131',            
		);
    $get_params = http_build_query($request_params);  
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/wall.get?' . $get_params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $m = @curl_exec($ch);
    curl_close($ch);
	$rt = json_decode($m, true)['response'];
	
	if($issavecount) file_put_contents('countwall/'.$ownerid, $rt['count']);
    return $rt;
}

function GetRandomWall($ownerid){
	$offse = (file_exists('countwall/'.$ownerid) ? rand(0, intval(file_get_contents('countwall/'.$ownerid))) : 1);
	$request_params = array(
            'owner_id' => $ownerid,
            'count' => 2,
			'offset' => $offse,
            'access_token' => '204626222046262220462622fe201be021220462046262279cbce62c5301f21e554f471',
            'v' => '5.131',            
		);
    $get_params = http_build_query($request_params);  
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/wall.get?' . $get_params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $m = @curl_exec($ch);
    curl_close($ch);
	$rt = json_decode($m, true)['response'];
	
	file_put_contents('countwall/'.$ownerid, $rt['count']);
    return $rt;
}

function isAllowMess($userid){
	return getConversationsById($userid)['items'][0]['can_write']['allowed'];
}

function isAdmin($id)
{
	global $chatid;
	$chatInfo = getConversationsById($chatid);
	try{
		return ($id == $chatInfo['items'][0]['chat_settings']['owner_id'] || in_array($id, $chatInfo['items'][0]['chat_settings']['admin_ids']));
	}
	catch (Exception $e){
		return false;
	}
}

function IsStr($str, $search, $all = false){ //Функция поиска подстроки в строке
	if($all) { return in_array($str, $search);}	
	foreach($search as $tmp) { if(strpos($str, $tmp) !== FALSE) return true;}
    return false;
}
function GetNumber($str){ //Возвращает только цифровую часть строки
    return doubleval(preg_replace("/[^-0-9\.]/","",$str));
}
function ParseASCII($str, $newline = ''){ //Парсит ascii строку из массивов для отправки в вк
    $ret = "";
    foreach($str as $tmp)
        $ret .= $tmp . $newline;
    return $ret;
}
function LoadASCII($file, $isnbsp = false, $newline = ''){ //Парсит ascii строку из файла
    $str = "";
    $fd = fopen('ascii/'.$file, 'r') or die("не удалось открыть файл"); //&nbsp;
    while(!feof($fd))
    {
        $str .= str_replace(" ", $isnbsp ? "&nbsp;" : " ", fgets($fd)) . $newline;
    }
    fclose($fd);
    return $str;
}
function RegistrationUser($id, $name = "NullBot666"){ //Регистрирует пользователя в бд
    global $link;
	global $chatid;
	global $token;
	$name = ($name == "NullBot666") ? GetVKName($id) : $name;
    $res = mysqli_query($link, "SELECT `ID` FROM `Users` WHERE `ID_Chat`='$chatid' AND `ID` = ".$id);
    $count = mysqli_num_rows($res);
    if($count <= 0)
        return mysqli_query($link, "INSERT INTO `Users`(`ID_Chat`, `ID`, `Name`) VALUES ('$chatid', '$id','$name')");
}
function RegistrationPeer(){ //Регистрирует беседу в бд
    global $link;
	global $chatid;
	global $token;
    $res = mysqli_query($link, "SELECT `ID` FROM `Peers` WHERE `ID` = ".$chatid);
    $count = mysqli_num_rows($res);
    if($count <= 0)
        return mysqli_query($link, "INSERT INTO `Peers`(`ID`) VALUES ($chatid)");
}
function UpdatePin($text){ //изменяет закон беседы
    global $link;
	global $chatid;
	global $token;
    $res = mysqli_query($link, "UPDATE `Peers` SET `Pin`='$text' WHERE `ID` = ".$chatid);
}
function GetPin(){ //возвращает закон беседы
    global $link;
	global $chatid;
	global $token;
	$res = mysqli_query($link, "SELECT `Pin` FROM `Peers` WHERE `ID` = ".$chatid);
	while($row = $res->fetch_assoc())
		return preg_replace_callback(
            "/[id+.[0-9]+.+]/",
            function ($matches)
            {
              $tpu = doubleval(preg_replace("/[^-0-9\.]/","",$matches[0]));
              return GetLinkUser($tpu);
            },
            trim($row['Pin']));
}
function ReadPin(){ //возвращает закон беседы
    global $link;
	global $chatid;
	global $token;
	$res = mysqli_query($link, "SELECT `Pin` FROM `Peers` WHERE `ID` = ".$chatid);
	while($row = $res->fetch_assoc())
		return trim($row['Pin']);
}
function GetVKName($id){ //имя пользователя из вк
	global $token;
	if($id > 0)
		return json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$id}&v=5.130&access_token={$token}"))->response[0]->first_name;
	return "";
}
function RenameUser($id, $name = "Billies"){ //Перемиеновывает пользователя в бд
    global $link;
	global $chatid;
    return mysqli_query($link, "UPDATE `Users` SET `Name`='$name' WHERE `ID_Chat`='$chatid' AND `ID`='$id'");
}
function GetName($id){
	global $link;
	global $chatid;
    $res = mysqli_query($link, "SELECT `Name` FROM `Users` WHERE `ID_Chat`='$chatid' AND `ID` = ".$id);
	$count = mysqli_num_rows($res);
	if($count <= 0){
		RegistrationUser($id);
		return GetVKName($id);
	}
    while($row = $res->fetch_assoc())
		return trim($row['Name']);
}
function GetLinkUser($userid){
	return LinkUser($userid, GetName($userid));
}
function LinkUser($userid, $name){
	return "[id$userid|$name]";
}
function GetUsersOnline(){
	global $token;
	  $userOnline = 0;
	  $Onlinelist = "";
      $members = getConversationMembers(); //$vk->request('messages.getConversationMembers', ['peer_id' => $peer_id]); // Запрос на получение данных о пользователях беседы
      foreach ($members['profiles'] as $useronline) { // При помощи foreach производим работу над данными из пришедшего нам массива
        if ($useronline['online'] == 1) { // Если проверяемый пользователь в сети
          $userOnline++; // Добавляем 1 к общему числу онлайна

		  $userInfoOnline = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$useronline['id']}&fields=last_seen,sex&v=5.130&access_token={$token}"),true)['response'];
          $first_nameOnline = $userInfoOnline[0]['first_name']; // Имя
          RegistrationUser($useronline['id'], $first_nameOnline);
          $last_nameOnline = $userInfoOnline[0]['last_name']; // Фамилия
          $platformOnline = $userInfoOnline[0]['last_seen']['platform']; // Платформа
		  $sex = $userInfoOnline[0]['sex']; // Платформа
          if ($platformOnline >= 1 && $platformOnline <= 5) { // 1 - 5 отнесем к телефонам
            $platformOnline = '📱';
          }else{ // остальные ПК
            $platformOnline = '💻';
          }
          $Onlinelist .= "- ".($sex == 0 ? "Хрен " : ($sex==1 ? "Славянка " : "Славянин ")).GetLinkUser($useronline['id'])."   - ".$platformOnline."\n"; //@id{$useronline['id']} ({$first_nameOnline} {$last_nameOnline}) // Составили текст с онлайн людьми
        }
      }
      return ("
      Сейчас в сети: {$userOnline}:
      {$Onlinelist}
      ");
}
function GenerateSlavyanVodka($peerid){
    global $token;
    global $link;
    global $Month_r;
    
    $chatInfo = getConversationsById($peerid);
    $chatsettings = $chatInfo['items'][0]['chat_settings'];
    
    $month = date('m');
	$day = date('d');
	$year = date('Y');
	$isdrochka = ($month != 11);
	$monthrus = $Month_r[$month][1];
    $zavod = date('N') > 5;
    
    $retur = "Славянкая 🍷водка🍷 за $day $monthrus $year года\n";
    $retur .= "В беседе: ⚖".$chatsettings['title']."⚖\n";
    $retur .= "С количеством: 🗡".$chatsettings['members_count']."🗡\n";
    $retur .= "\n";
    $retur .= "Дрочить славянам: ".($isdrochka ? "разрешено" : "ЗАПРЕЩЕННО")."\n";
    $retur .= ((!$isdrochka && ($day > 29) && ($month+1 != 11)) ? "Осталось совсем немного, воздержись Брат"."\n" : "");
    $retur .= "На заводе: ".($zavod ? "выходной" : "рабочий день")."\n";
    $retur .= ($zavod ? "" : "Все на завод!"."\n");
    $retur .= "Приятного ".(!$zavod ? "трудового рабочего" : "")." дня, Славяне"."\n";
    return $retur;
}

/**
 * Получит случайный файл из некоторой директории
 */
function random_file($dir = 'audio/gay')
{
    $files = glob($dir . '/*.*'); //путь + маска содержимого
    $file = array_rand($files);
    return $files[$file];
}

function GenerateRandomAudio($dir = 'audio/gay')
{
    $filea = random_file();
    $yar = _bot_uploadAM($filea);
    $yar['files'] = basename($filea, ".ogg");
    return $yar;
}
function GenerateAudioAttachmnt($filea)
{
    $t = _bot_uploadAM($filea);
    return 'doc'.$t['owner_id'].'_'.$t['id'];
}
function GenerateRandomAudioAttachmnt($dir = 'audio/gay')
{
    $t = GenerateRandomAudio($dir);
    return 'doc'.$t['owner_id'].'_'.$t['id'];
}
?>
