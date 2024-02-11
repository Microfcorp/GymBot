<?php require_once("BotSet.php"); ?>
<?php require_once("func.php"); ?>
<?php require_once("pictures.php"); ?>
<?php require_once("gachi.php"); ?>
<?php require_once("KillLib.php"); ?>

<?
if (!isset($_REQUEST)) { //Если нет данных
    exit("This is service script"); //То просто возваращаем error
}

$regex = "((https?|ftp)\:\/\/)?";
$regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?";
$regex .= "([a-z0-9-.]*)\.([a-z]{2,3})";
$regex .= "(\:[0-9]{2,5})?";
$regex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?";
$regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?";
$regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?";
 
//Получаем и декодируем уведомление
$data = json_decode(file_get_contents('php://input'));

// проверяем secretKey
if(strcmp($data->secret, $secretKey) !== 0 && strcmp($data->type, 'confirmation') !== 0)
    return;
 
    //Если это уведомление для подтверждения адреса сервера...
    if($data->type == 'confirmation'){
        //...отправляем строку для подтверждения адреса
        die($confirmationToken);
	}
 
    //Если это уведомление о новом сообщении или о редактировании сообщения...
    elseif($data->type == 'message_new' || $data->type == 'message_edit'){
        //...получаем id его автора
        $userId = $data->object->message->from_id;
        //Получаем текст сообщения
		$bodyRead = $data->object->message->text;
        $body = mb_strtolower($bodyRead); //Переводим всю строку в нижний регистр
        
        $rrr = isset($data->object->message->payload) ? json_decode($data->object->message->payload, true) : null;
        if($rrr != null){
            $body = mb_strtolower($rrr['rep']);
        }

		$peer_ids = $data->object->message->peer_id; //id назначенияы (беседы)
		$messageid = $data->object->message->conversation_message_id; //id сообщения в беседе
		$chataction = isset($data->object->message->action) ? $data->object->message->action : null; //действие в чате
		
		if($userId < 0) //Если пишет сообщество
			exit("OK");
		
		//Получаем имя пользователя
        $sendingUser = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$userId}&fields=sex&v=5.130&access_token={$token}"));
        $userName = $sendingUser->response[0]->first_name;
        $userSex = $sendingUser->response[0]->sex;
		$chatid = $peer_ids; //глобальная переменная номера чата
		
        RegistrationUser($userId, $userName); //Регистрируем пользователя в базе данных
        
		/*if(!isAllowMess($userId) && IsStr($body, ["/"])){
			MessSendReply($peer_ids, "разреши сообщения сообщества, по-братски", $userId);
		}*/
        
        $muteuser = GetMuteUser($userId);
        if($muteuser != false){
            MessDelete($peer_ids, $messageid);
        }
        
        if(preg_match("/^$regex$/i", $body))
            exit("OK");
		
		RegistrationPeer(); //Регистрируем беседу в бд
		
		//$slovar = ;
		foreach(GetLibKill() as $sl){
			$sss = $sl[4];
			if(IsStr($body, [$sss]) && !IsAdmin($userId)){
				if($sl[3] == 3){
					MessSend($peer_ids, GetLinkUser($userId)." нарушил славянско-братский закон, и, если не админ, был изгнан за употребление '$sss'", $userId);
					kickUser($userId);
				}
				elseif($sl[3] == 2){
					//$pp = KillGD($userId);
                    $pp = "photo/poves.jpg";
					$photo = _bot_uploadPhoto($pp);
                    AddMute($userId, '2', '00:10:00');
					MessSendAttach($peer_ids, "Поганый ГТАшник ".GetLinkUser($userId)." нарушил словарное братское правило и был повешен на виселице за употребление '$sss', так же ему выдан мут на 10 минут", 'photo'.$photo['owner_id'].'_'.$photo['id'].','.$GachiMics['Хардбас']);
                    _bot_PublishPhotoStories($pp);
                }
				elseif($sl[3] == 1){
					//$pp = RastrelGD($userId, true);
                    $pp = "rasterl.jpg";
					$photo = _bot_uploadPhoto($pp);
                    AddMute($userId, '1', '00:10:00');
				    MessSendAttach($peer_ids, "Поганый ГТАшник ".GetLinkUser($userId)." нарушил словарное братское правило и был расстрелен НКВД во славу Коммунизма с участием малолетнего чекиста за употребление '$sss', а так же ему выдан мут на 10 минут", 'photo'.$photo['owner_id'].'_'.$photo['id'].','.$GachiMics['Хардбас']);
                    //_bot_PublishPhotoStories($pp);
                }
				elseif($sl[3] == 0){
					MessSend($peer_ids, GetLinkUser($userId)." получает предупреждение, об употреблении запрещенных слов за употребление '$sss'", $userId);
				}
				break;
			}
		}
		
		if(IsStr($body, ["/принять закон"])){
			if(!IsAdmin($userId)){
				MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);
			}
			else{
				$mess = trim(mb_substr($bodyRead, 14));
				//$messid = $data->object->message->conversation_message_id;
				$datee = date("d.m.Y H:i");
				$pinsss = "📜Товарищ [id".$userId."] прянял закон от $datee:📜\n$mess";
				UpdatePin(ReadPin() . "\n\n" . $pinsss);
				$mid = MessSend($peer_ids, "📌ЗАКОН📌" . GetPin(), $userId);
				PinMessage($mid[0]);
			}
		}
        elseif(IsStr($body, ["/удалить закон"])){
            ///удалить закон Статья 14 \n27.11.2021 16:03 \nСтатья 15
			if(!IsAdmin($userId)){
				MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);
			}
			else{
				$mess = trim(mb_substr($bodyRead, 14)); //GG 📔Статья 14📔 GG
				$t = ReadPin();
                $i = 0;
                foreach(preg_split('/\n/', $mess) as $tmpt)
                {
                    $t = preg_replace(
                        "/(.|)+".trim($tmpt)."+(.|)+\n*/",
                        "",
                        $t);
                }                        
				UpdatePin($t);
				$mid = MessSend($peer_ids, "📌ЗАКОН📌" . GetPin(), $userId);
				PinMessage($mid[0]);
			}
		}
        elseif(IsStr($body, ["/изменить закон"])){
            ///удалить закон Статья 14 \n27.11.2021 16:03 \nСтатья 15
			if(!IsAdmin($userId)){
				MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);
			}
			else{
				$mess = trim(mb_substr($bodyRead, 15)); //GG 📔Статья 14📔 GG
				$t = ReadPin();
                $i = 0;
                $tmt = preg_split('/\n/', $mess);             
                $t = preg_replace(
                    "/(.|)+".trim($tmt[0])."+(.|)+\n*/",
                    $tmt[1],
                    $t);
                                       
				UpdatePin($t);
				$mid = MessSend($peer_ids, "📌ЗАКОН📌" . GetPin(), $userId);
				PinMessage($mid[0]);
			}
		}
		elseif(IsStr($body, ["/привет"], true)){ //Если текст сообщения равн "привет"
			MessSend($peer_ids, "Здорова, Славянин ".GetLinkUser($userId)); //Производим вызов метода из файла "func.php", отправляем сообщение пользователь, текст и токен
		}
		elseif(IsStr($body, ["/сменить имя"])){ //Если в тексте сообщения найдено "сменить имя"
			$newname = mb_substr($bodyRead, 13);
			if($newname == ""){
				MessSendReply($peer_ids, "ты ввел пустое имя", $userId);
			}
			else{
				$fname = GetName($userId);
				RenameUser($userId, $newname);
				MessSend($peer_ids, "Чудо ".LinkUser($userId, $fname)." был переименован в ".GetLinkUser($userId)); //Производим вызов метода из файла "func.php", отправляем сообщение пользователь, текст и токен
			}
		}
		elseif(IsStr($body, ["/мой ид", "/мой хд", "/мой id"])){ //Если в тексте сообщения найдено "мой ид" ИЛИ "мой хд"
			MessSendReply($peer_ids, "Твой ID - ".$userId, $userId);
		}
        elseif(IsStr($body, ["/остановить гей бар", "/стоп гей бар", "/остановить gay bar", "/стоп gay bar"])){ //ГЕЙ БАААААААРРРРР
			$isbar = GetGayBar();
            SetGayBar(false);
            if($isbar){
                $photo = _bot_uploadPhoto('photo/gaybart.jpg');
                MessSendAttach($peer_ids, "Благодарим всех за посещение ♂Gay Bar♂. Приходите еще", 'photo'.$photo['owner_id'].'_'.$photo['id'].','.$GachiMics['Валим на Gay Party']);
            }
            else{
                MessSendReply($peer_ids, "Нельзя остановить то, чего, увы, нет", $userId);
            }
		}
        elseif(IsStr($body, ["/гей бар", "/gay bar", "/gey bar"])){ //Гей бар
            if($userSex == 1){
                $photo = _bot_uploadPhoto('photo/nowom.jpg');
                MessSendAttach($peer_ids, "Вход в ♂Gay Bar♂ женщинам воспрещен", 'photo'.$photo['owner_id'].'_'.$photo['id']);
            }
            else{
                $isbar = GetGayBar();
                SetGayBar(true);
                if($isbar){               
                    $photo = _bot_uploadPhoto('photo/gaybar.jpg');
                    MessSendAttach($peer_ids, "♂Oh, sheet♂ приветствуем ".GetLinkUser($userId)." в ♂Gay Bar♂\nПрисоединяйся к ♂Fisting Ass♂", 'photo'.$photo['owner_id'].'_'.$photo['id'].','.$GachiMics['Gay Bar']);
                }
                else{
                    $photo = _bot_uploadPhoto('photo/gaybart.jpg');
                    MessSendAttach($peer_ids, GetLinkUser($userId)." запустил ♂Gay Bar♂\nДа начнется ♂Fisting Ass♂\nДа изыдут ♂Fucking Slave♂\nИ прибудет ♂Dungeon Master♂", 'photo'.$photo['owner_id'].'_'.$photo['id'].','.(rand(0,2) == 0 ? $GachiMics['Федерико Феллини'] : $GachiMics['Два корабля']));
                }
            }
        }
        elseif(IsStr($body, ["гей бар", "gay bar"])){ //ГЕЙ БАААААААРРРРР
            if(GetGayBar() && $userSex != 1){
                SetGayBar(true);
                $photo = _bot_uploadPhoto('photo/gaybar.jpg');
                MessSendAttach($peer_ids, "♂Oh, my cum♂ ".GetLinkUser($userId)." сказал ♂Gay Bar♂. Мы тебя приглашаем 😏", 'photo'.$photo['owner_id'].'_'.$photo['id'].','.$GachiMics['Валим на Gay Party']);
            }
        }
        elseif(IsStr($body, ["/фраза", "/гачи фраза", "/гей фраза"])){ //ГЕЙ БАААААААРРРРР
			$t1 = GenerateRandomAudio();
            MessSendAttach($peer_ids, "♂Oh, ".$t1['files']."♂", 'doc'.$t1['owner_id'].'_'.$t1['id']);
		}
        elseif(IsStr($body, ["/микс"])){ //ГЕЙ БАААААААРРРРР
            MessSendAttach($peer_ids, "Зацени гачи миксы", AllMisc());
		}
		elseif(IsStr($body, ["/кто я", "/мое гачи", "/моя роль"])){
			MessSendReply($peer_ids, "ты - ".RoleToEnglish(GetRole($userId)), $userId);
		}
		elseif(IsStr($body, ["/добавить в словарь", "/новое в словарь", "/добавить словарь", "/добавить слово"])){
            if(!IsAdmin($userId)){
				MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);
			}
            else{
                $typeq = SearchType($body);
                $typetext = TypeString($typeq);
                $frq = trim(explode('слово', $body)[1]);
                AddLibKill($userId, $typeq, $frq);
                MessSendReply($peer_ids, "В словарь добавлено слово 📍".$frq."📍 со статусом ".$typetext, $userId);
            }
		}
		elseif(IsStr($body, ["/получить словарь", "/словарь", "/словарь запрященных слов"])){
			$rt = "";
			foreach(GetLibKill() as $t)
				$rt .= "📍".$t[4]."📍 - ".TypeString($t[3])." (слово ввел ".trim(GetLinkUser($t[2])).")\n";
			MessSend($peer_ids, "В словаре находятся следующие слова:\n".$rt, $userId);
		}
		elseif(IsStr($body, ["/удалить слово"])){
            if(!IsAdmin($userId)){
				MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);
			}
            else{
                $frq = trim(explode('слово', $body)[1]);
                RemoveLibKill($frq);
                MessSend($peer_ids, "Слово $frq было удалено из словаря", $userId);
            }
		}
		elseif(IsStr($body, ["/я буду", "/я стану"])){
			if(GetRole($userId) == 0){
				MessSendReply($peer_ids, "♂Anal♂ не может изменить себя", $userId);
			}
			else{
				for($i=0; $i<count($Gachirole); $i++){
					$rol = mb_strtolower($Gachirole[$i]);
					if(IsStr($body, [$rol])){
						if($i == 0){
							MessSendReply($peer_ids, "Нельзя стать ♂Anal♂", $userId);
						}
						else{
							ChangeRole($userId, $i);
							MessSendReply($peer_ids, "ты стал - ♂".RoleToEnglish($i)."♂", $userId);
						}
					}
				}	
			}			
		}
		elseif(IsStr($body, ["/изменить роль"])){
			if(!IsAdmin($userId)){
				MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);
			}
			else{
				$u_id = explode('|', explode('[id', $bodyRead)[1])[0];
				$role = EnglishToRole(trim(explode(']', $bodyRead)[1]));
				ChangeRole($u_id, $role);
				MessSend($peer_ids, GetLinkUser($u_id)." был изменен на ♂".RoleToEnglish($role)."♂", $userId);
			}
		}
		elseif(IsStr($body, ["/кокнуть имя"])){
			if(!IsAdmin($userId)){
				MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);
			}
			else{
				$u_id = explode('|', explode('[id', $bodyRead)[1])[0];
				$newname = (trim(explode(']', $bodyRead)[1]));
				if($newname == "")
					MessSendReply($peer_ids, "ты ввел пустое имя", $userId);
				else{
					$fname = GetName($u_id);
					RenameUser($u_id, $newname);
					MessSend($peer_ids, "Дорогой человек ".LinkUser($u_id, $fname)." был переименован в ".GetLinkUser($u_id)); //Производим вызов метода из файла "func.php", отправляем сообщение пользователь, текст и токен
				}
			}
		}
		elseif(IsStr($body, ["/все роли", "/посмотреть роли", "гачи роли"])){			
			MessSend($peer_ids, "В боте есть роли:<br>".AllRole(), $userId);
		}
		elseif(IsStr($body, ["/сделать fisting", "/сделать fisting ass", "/сделать ♂fisting ass♂", "/fisting ass", "/fisting", "/фистинг"])){	
			$expl = explode('[id', $bodyRead);
			$fist_id = (count($expl) < 2 ) ? $userId : explode('|', $expl[1])[0];;
			if($fist_id == $userId){
				MessSend($peer_ids, GetLinkUser($userId)." поехал крышей и сделал ♂fisting ass♂ сам себе", $userId);
                MessSendAttach($peer_ids, "", GenerateAudioAttachmnt("audio/gay/The semen.ogg"));
			}
			elseif(GetRole($fist_id) < GetRole($userId)){
				MessSend($peer_ids, GetLinkUser($userId)." сделал ♂fisting ass♂ ".GetLinkUser($fist_id), $userId);
                MessSendAttach($peer_ids, "", GenerateAudioAttachmnt("audio/gay/Sorry for what.ogg"));
			}
			elseif(GetRole($fist_id) > GetRole($userId)){
				MessSend($peer_ids, GetLinkUser($userId)." хотел сделать ♂fisting ass♂ ".GetLinkUser($fist_id)." но у него не хватило сил", $userId);
                MessSendAttach($peer_ids, "", GenerateAudioAttachmnt("audio/gay/FUCK YOU.ogg"));
			}
			elseif(GetRole($fist_id) == GetRole($userId)){
				$ok = rand(0, 2);
				if($ok == 1) MessSend($peer_ids, GetLinkUser($userId)." долго боролся, и, наконец сделал ♂fisting ass♂ ".GetLinkUser($fist_id), $userId);
				else MessSend($peer_ids, GetLinkUser($userId)." долго боролся, пал ♂dick♂ и не смог сделал ♂fisting ass♂ ".GetLinkUser($fist_id), $userId);
                MessSendAttach($peer_ids, "", GenerateAudioAttachmnt("audio/gay/Spit YEEEEEAAAAHHH.ogg"));
			}
		}
		elseif(IsStr($body, ["/сделать cum", "/cum", "/кам", "/кончить"])){	
			$expl = explode('[id', $bodyRead);
			$fist_id = (count($expl) < 2 ) ? "1" : explode('|', $expl[1])[0];
			if(count($expl) < 2){
				MessSend($peer_ids, GetLinkUser($userId)." сделал ♂".GetRandomCum()."♂ в этой ♂Gym♂", $userId);
                MessSendAttach($peer_ids, "", GenerateAudioAttachmnt("audio/gay/Orgasm 1.ogg"));
			}
			elseif($fist_id == $userId){
				MessSend($peer_ids, GetLinkUser($userId)." поехал ♂Anal♂ и сделал ♂".GetRandomCum()."♂ сам себе", $userId);
                MessSendAttach($peer_ids, "", GenerateAudioAttachmnt("audio/gay/Orgasm 2.ogg"));
			}
			elseif(GetRole($fist_id) <= GetRole($userId)){
				MessSend($peer_ids, GetLinkUser($userId)." сделал ♂".GetRandomCum()."♂ ".GetLinkUser($fist_id), $userId);
                MessSendAttach($peer_ids, "", GenerateAudioAttachmnt("audio/gay/Orgasm 3.ogg"));
			}
			elseif(GetRole($fist_id) > GetRole($userId)){
				MessSend($peer_ids, GetLinkUser($userId)." хотел сделать ♂".GetRandomCum()."♂ ".GetLinkUser($fist_id)." но его завалили на лопатки", $userId);
                MessSendAttach($peer_ids, "", GenerateAudioAttachmnt("audio/gay/Spank.ogg"));
			}
		}
		elseif(IsStr($body, ["/онлайн", "/online", "/кто в сети", "/брат кто в сети"])){
			/*if(!IsAdmin($userId)) MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);			
			else MessSend($peer_ids, GetUsersOnline(), $userId);*/
            MessSend($peer_ids, "Функция отключена в версии бота ".BOTVERSION, $userId);
		}
		elseif(IsStr($body, ["/получить пацанское гачимучи", "/пацанское гачимучи", "/получить пацанский гачимучи", "/пацанский гачимучи"])){
			$wall = GetRandomWall('-113661329');
			$wallid = $wall['items'][0]['id'];
			MessSendAttach($peer_ids, "Случайная запись из [club113661329|Пацанское Gachimuchi ⚦]", "wall-113661329_$wallid");
		}
		elseif(IsStr($body, ["/получить славян", "/славянский пост", "/славяне"])){
			$wall = GetRandomWall('-165104294');
			$wallid = $wall['items'][0]['id'];
			MessSendAttach($peer_ids, "Случайная запись из [club165104294|Танцы Рикардо Милоса]", "wall-165104294_$wallid");
		}
		elseif(IsStr($body, ["/изгнать", "/выгнать", "/прогнать"])){			
			$expl = explode('[id', $bodyRead);
			$u_id = (count($expl) < 2 ) ? "1" : explode('|', $expl[1])[0];
			if($u_id == $userId) MessSendReply($peer_ids, "нельзя изгнать себя", $userId);
			else{				
				if(!IsAdmin($userId)) MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);
				else{
					if(IsAdmin($u_id)) MessSendReply($peer_ids, "нельзя изгать администратора", $userId);
					else{
						kickUser($u_id);
						MessSend($peer_ids, GetLinkUser($u_id)." был изгнан из славянского братства", $userId);
					}
				}
			}			
		}
        elseif(IsStr($body, ["/чистка баб", "/отчистка баб", "/уличение баб"])){
            if(!IsAdmin($userId)) MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);
            else{
                $expl = GetNumber($body);
                $expl = $expl < 2 ? 2 : $expl;
                $tchat = getConversationsMembersAll()["profiles"];
                $func = function($value) {
                    $t = empty($value['deactivated']) && $value['sex'] == 1;
                    if($t)
                        return $value['id'];
                };         
                $tchat = array_map($func, $tchat);
                $tchat = array_diff($tchat, array(null));           
                $tchatid = array_rand($tchat, $expl);
                $tmp = "Из беседы были изгнаны следующие бабы:\n";
                foreach($tchatid as $tmp1){
                    $user = $tchat[$tmp1];
                    if(!IsAdmin($user)){
                        $tmp .= "🧹" . GetLinkUser($user) . "🧹\n";
                        kickUser($user);
                    }
                }
                $photo = _bot_uploadPhoto('photo/nowom.jpg');
                MessSendAttach($peer_ids, $tmp, 'photo'.$photo['owner_id'].'_'.$photo['id']);
            }            
		}
        elseif(IsStr($body, ["/чистка трупов", "/чистка анонимусов", "/чистка бебр"])){
            if(!IsAdmin($userId)) MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);
            else{
                $expl = GetNumber($body);
                $expl = $expl < 2 ? 2 : $expl;
                $tchat = getConversationsMembersAll()["profiles"];
                $func = function($value) {
                    $f1 = ["/рикардо", "/риккардо", "милос"];
                    $t = isset($value['deactivated']) || IsStr(mb_strtolower($value['last_name']), $f1) || IsStr(mb_strtolower($value['first_name']), $f1) || (isset($value['last_seen']) && $value['last_seen'] < strtotime('1/1/2022'));
                    if($t)
                        return $value['id'];
                };         
                $tchat = array_map($func, $tchat);
                $tchat = array_diff($tchat, array(null));           
                $tchatid = array_rand($tchat, ($expl >= count($tchat) ? count($tchat)-1 : $expl));
                $tmp = "Из беседы были изгнаны следующие трупы и аннонимнусы:\n";
                foreach($tchatid as $tmp1){
                    $user = $tchat[$tmp1];
                    if(!IsAdmin($user)){
                        $tmp .= "🧹" . GetLinkUser($user) . "🧹\n";
                        kickUser($user);
                    }
                }
                MessSend($peer_ids, $tmp, $userId);
            }            
		}
		elseif(IsStr($body, ["/созвать всех"])){
			if(!IsAdmin($userId)){
				MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);
			}
			else{
				/*$mess = mb_substr($bodyRead, 14);
				$countm = 0;
				foreach (getConversationMembers()['profiles'] as $member) { // Прошли по массиву для регистрации пользователей по их id
					$user_id = $member['id']; // Получили id пользоавтеля
					if($member['online'] == 1)
                    {
                        if(isMessagesFromGroupAllowed($user_id)){
                            $countm++;
                            MessSend($user_id, "Вас созывают в беседу Славяне и Братские народы<br>".$mess, $userId);  
                        }
                    }                        
				}
				MessSend($peer_ids, "Все $countm участников были созваны", $userId);
                */
                MessSend($peer_ids, "Функция отключена в версии бота ".BOTVERSION, $userId);
			}
		}
		elseif(IsStr($body, ["/славянское фото"])){
			$groups = getGroupsById('165104294');
			file_put_contents("photo/slav.jpg", file_get_contents($groups[0]['photo_200']));
			$photo = _bot_uploadPhoto('photo/slav.jpg');
			MessSendAttach($peer_ids, "Великое Славянской фото", 'photo'.$photo['owner_id'].'_'.$photo['id']);
		}
		elseif(IsStr($body, ["/призвать"])){
			if(!IsAdmin($userId)){
				MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);
			}
			else{
				$u_id = explode('|', explode('[id', $bodyRead)[1])[0];
				$mess = trim(explode(']', $bodyRead)[1]);
                if(isMessagesFromGroupAllowed($u_id)){
                    MessSend($u_id, "Вас призывает ".GetLinkUser($userId)." в беседу Славяне и Братские народы с сообщением: ".$mess, $userId);
                    MessSendReply($peer_ids, "Вы призвали ".GetLinkUser($userId), $userId);
                    //MessDelete($peer_ids, $messageid);
                }
                else{
                    MessSendReply($peer_ids, "Вы не можете призвать ".GetLinkUser($u_id).", так как он не разрешил сообщения от сообщества", $userId);		
                }    
			}
		}
        elseif(IsStr($body, ["/итоги опроса"])){
            if(!IsAdmin($userId)){
				MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);
			}
			else{
                if(isset($data->object->message->reply_message->attachments[0]) && $data->object->message->reply_message->attachments[0]->type == "poll"){
                    $pool = $data->object->message->reply_message->attachments[0]->poll;
                    $txtwq = "В ".($pool->anonymous == true ? "аннонимном " : "")."опросе от инициатора ".GetLinkUser($pool->author_id).": ".$pool->question."\nИмеется всего: ".$pool->votes." голосов:\n";
                    foreach($pool->answers as $ty){
                        $txtwq .= $ty->votes . " голосов за вариант: ". $ty->text. ". Соотношение ". $ty->rate. " процентов\n";
                    }
                    $txtwq .= "\n";
                    $txtwq .= ($pool->end_date == 0 ? "" : "Опрос окончится: ".date('d.m.Y H:i', $pool->end_date)." \n");
                    MessSend($peer_ids, $txtwq, $userId);
                }                              
            }
		}
        elseif(IsStr($body, ["/удалить"])){
            if(!IsAdmin($userId)){
				MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);
			}
			else{
                $delcount = 0;
                if(isset($data->object->message->reply_message->conversation_message_id)){
                    $replymesid = $data->object->message->reply_message->conversation_message_id;
                    MessDelete($peer_ids, $replymesid);
                    $delcount++;
                }
                
                if(isset($data->object->message->fwd_messages)){
                    $fwdmes = $data->object->message->fwd_messages;
                    $idsmes = "";
                    foreach($fwdmes as $ty){
                        $idsmes .= $ty->conversation_message_id . ',';
                        $delcount++;
                    }
                    MessDelete($peer_ids, $idsmes);
                }
                //MessDelete($peer_ids, $data->object->message->conversation_message_id);
                MessSendReply($peer_ids, "{$delcount} сообщений было удалено", $userId);
            }
		}
		elseif(IsStr($body, ["/календарь"])){
			$month = date('m');
			$day = date('d');
			$isdrochka = ($month == 1 || $month == 2 || $month == 6 || $month == 7 || $month == 9 || $month == 10 || $month == 12);
			$monthrus = $Month_r[$month][0];
			MessSendReply($peer_ids, "Сегодня $day число.<br>В этот ".($isdrochka ? "чудный" : "славный")." месяц $monthrus дрочить Славянам ".($isdrochka ? "разрешается)" : "ЗАПРЕЩАЕТСЯ"), $userId);
		}
        elseif(IsStr($body, ["/сводка", "/славянская сводка", "/славянская водка"])){
            $photo = _bot_uploadPhoto(IsRabDay() ? 'photo/zavod.jpg' : 'photo/portveinone.jpg');
			MessSendAttach($peer_ids, GenerateSlavyanVodka($peer_ids), 'photo'.$photo['owner_id'].'_'.$photo['id']);
		}
		elseif(IsStr($body, ["/какая роль у"])){	
			$u_id = explode('|', explode('[id', $bodyRead)[1])[0];
			MessSend($peer_ids, "Товарищ ".GetLinkUser($u_id)." - ♂".RoleToEnglish(GetRole($u_id))."♂", $userId);
		}
        elseif(IsStr($body, ["/замолчать", "/заткнуть", "/мут", "/mute", "/молчать"])){	
			$u_id = explode('|', explode('[id', $bodyRead)[1])[0];
            if(IsAdmin($u_id) && !IsAdmin($userId)){
                MessSendReply($peer_ids, "Нельзя заставить молчать администратора или нужно быть администратором", $userId);
            }
            else{
                $yt = GetMuteUser($u_id);
                if($yt == false){
                    AddMute($u_id, '0', '838:0:00');
                    MessSend($peer_ids, "Товарищ ".GetLinkUser($u_id)." заставлен молчать месяц или до особого распоряжения", $userId);
                    MessSendAttach($peer_ids, "", GenerateAudioAttachmnt("audio/gay/Its so fucking deep.ogg"));
                }
                else{
                    MessSendReply($peer_ids, "Пользователь уже молчит. Осталось молчать " . $yt[4], $userId);
                }
            }
		}
        elseif(IsStr($body, ["/говорить", "/разбан", "/унмут", "/снять мут"])){	
			$u_id = explode('|', explode('[id', $bodyRead)[1])[0];
            if(!IsAdmin($userId)){
				MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);
			}
            else{
                if(GetMuteUser($u_id) == false){
                    MessSendReply($peer_ids, "Пользователю не запрещено говорить", $userId);              
                }
                else{
                    RemoveMute($u_id);
                    MessSend($peer_ids, "Товарищу ".GetLinkUser($u_id)." возвращено право слова", $userId);
                }
            }
		}
        elseif(IsStr($body, ["/молчащие", "/список мута", "кто молчит"])){	
			$mutes = GetMute();
            $tmp = "Сейчас молчат пользователи:\n";
            foreach($mutes as $tt){
                $tmp .= GetLinkUser($tt[1]) . " за " . TypeStringMute($tt[3]) . " на " . $tt[4] . " минут\n";
            }
            MessSend($peer_ids, $tmp, $userId);
		}
		elseif(IsStr($body, ["/похвалить"])){	
			$photo = _bot_uploadPhoto('photo/pohval.jpg');
            
            $expl = explode('[id', $bodyRead);
            $fist_id = (count($expl) < 2 ) ? $userId : explode('|', $expl[1])[0];;
            if(count($expl) < 2 || $fist_id == $userId){
                MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." похвалил сам себя. Странный", 'photo'.$photo['owner_id'].'_'.$photo['id'].','.$GachiMics['Хардбас']);
            }
            else{
                MessSendAttach($peer_ids, "Товарищу ".GetLinkUser($fist_id)." выдвинута благодарность от администрации, а так же кусочек похвалы", 'photo'.$photo['owner_id'].'_'.$photo['id']);
            } 
            
            MessSendAttach($peer_ids, "", GenerateAudioAttachmnt("audio/gay/Fisting is 300 $.ogg"));
        }
        elseif(IsStr($body, ["/boy", "/бой"])){
            $expl = explode('[id', $bodyRead);
            $fist_id = (count($expl) < 2 ) ? $userId : explode('|', $expl[1])[0];;
            if(count($expl) < 2 || $fist_id == $userId){
                MessSendAttach($peer_ids, "i`m", GenerateAudioAttachmnt("audio/gay/Boy next door.ogg"));
            }
            else{
                MessSendAttach($peer_ids, GetLinkUser($fist_id), GenerateAudioAttachmnt("audio/gay/Boy next door.ogg"));
            } 
			
        }
        elseif(IsStr($body, ["/отвалить сиськи", "/отломать сиськи", "/украсть сиськи", "/опустить сиськи", "/отрезать сиськи", "/сиськ"])){
            $expl = explode('[id', $bodyRead);
            $fist_id = (count($expl) < 2 ) ? $userId : explode('|', $expl[1])[0];
            if($fist_id == $userId){
                if($userSex == 2)
                    MessSendReply($peer_ids, "прости, брат, но сисек у тебя нет", $userId);
                else
                    MessSendAttach($peer_ids, "Балбесина ".GetLinkUser($userId)." потеряла свои сиськи", GenerateAudioAttachmnt("audio/gay/Deep dark fantasies.ogg"));
            }
            else{
                $fistsex = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$fist_id}&fields=sex&v=5.130&access_token={$token}"))->response[0]->sex;
                if($fistsex == 2)
                    MessSendReply($peer_ids, "У ".GetLinkUser($fist_id)." отсутствует натуральные большие сиськи", $userId);
                else
                    MessSendAttach($peer_ids, "У ".GetLinkUser($fist_id)." отвалились сиськи от шаманства ".GetLinkUser($userId)." ...Только если они были)...", GenerateAudioAttachmnt("audio/gay/Deep dark fantasies.ogg"));
            } 			
        }
        elseif(IsStr($body, ["/отвалить письк", "/сломать письк", "/сломать dick", "/сломать писк", "/отломать член", "/сломать член", "/письк"])){
            $expl = explode('[id', $bodyRead);
            $fist_id = (count($expl) < 2 ) ? $userId : explode('|', $expl[1])[0];
            if($fist_id == $userId){
                if($userSex == 1)
                    MessSendReply($peer_ids, "прости, но dick у тебя нет", $userId);
                else
                    MessSendAttach($peer_ids, "Балбесина ".GetLinkUser($userId)." потерял свой dick", GenerateAudioAttachmnt("audio/gay/Deep dark fantasies.ogg"));
            }
            else{
                $fistsex = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$fist_id}&fields=sex&v=5.130&access_token={$token}"))->response[0]->sex;
                if($fistsex == 1)
                    MessSendReply($peer_ids, "У ".GetLinkUser($fist_id)." отсутствует природный dick", $userId);
                else
                    MessSendAttach($peer_ids, "У ".GetLinkUser($fist_id)." отвалился dick от колдовства ".GetLinkUser($userId)." ...Только он у него был)...", GenerateAudioAttachmnt("audio/gay/Deep dark fantasies.ogg"));
            } 			
        }
        elseif(IsStr($body, ["/обновить фото"])){	
			_bot_uploadChatAva('photo/gaybar.jpg');
        }
        /*elseif(IsStr($body, ["/сторис"])){	
			_bot_PublishPhotoStories("photo/dzed.jpg");
        }*/
        elseif(IsStr($body, ["/пиво", "/налить пиво", "/бухнуть", "/выпить"])){	
            if(GetGayBar()){                        
                $expl = explode('[id', $bodyRead);
                $fist_id = (count($expl) < 2 ) ? "1" : explode('|', $expl[1])[0];;
                if(count($expl) < 2){
                    if(IsStr($body, ["@online"])) //@online
                    {
                        $photo = _bot_uploadPhoto('photo/pivoallonline.jpg');   
                        MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." налил всем присутствующим самое лучшее пиво в ♂Gay Bar♂", 'photo'.$photo['owner_id'].'_'.$photo['id']);
                    }
                    else{
                        $photo = _bot_uploadPhoto('photo/pivoall.jpg');   
                        MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." налил всем пиво в ♂Gay Bar♂", 'photo'.$photo['owner_id'].'_'.$photo['id']);
                    }
                }
                elseif($fist_id == $userId){
                    $photo = _bot_uploadPhoto('photo/pivoone.jpg');   
                    MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." один в ♂Gay Bar♂ напился пива", 'photo'.$photo['owner_id'].'_'.$photo['id']);
                }
                else{
                    $photo = _bot_uploadPhoto('photo/pivo.jpg');   
                    MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." налил в ♂Gay Bar♂ пиво товарищу ".GetLinkUser($fist_id)." и они выпили его", 'photo'.$photo['owner_id'].'_'.$photo['id']);
                }
                SetGayBar(true);
            }
            else{
                MessSendReply($peer_ids, "прости, брат, но пиво есть только в ♂Gay Bar♂", $userId);
            }
        }
        elseif(IsStr($body, ["/порт", "/портвейн", "/портвэйн", "/налить порт", "/налить портвейн", "/налить портвэйн"])){	
            if(GetGayBar()){                        
                $expl = explode('[id', $bodyRead);
                $fist_id = (count($expl) < 2 ) ? "1" : explode('|', $expl[1])[0];;
                if(count($expl) < 2){
                    if(IsStr($body, ["@online"])) //@online
                    {
                        $photo = _bot_uploadPhoto('photo/portveinall.jpg');   
                        MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." налил всем присутствующим самый лучший портвейн в ♂Gay Bar♂", 'photo'.$photo['owner_id'].'_'.$photo['id']);
                    }
                    else{
                        $photo = _bot_uploadPhoto('photo/portvein.jpg');   
                        MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." налил всем портвейн в ♂Gay Bar♂", 'photo'.$photo['owner_id'].'_'.$photo['id']);
                    }
                }
                elseif($fist_id == $userId){
                    $photo = _bot_uploadPhoto('photo/portveinone.jpg');   
                    MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." один в ♂Gay Bar♂ напился портвейн", 'photo'.$photo['owner_id'].'_'.$photo['id']);
                }
                else{
                    $photo = _bot_uploadPhoto('photo/portveinall.jpg');   
                    MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." налил в ♂Gay Bar♂ портвейн товарищу ".GetLinkUser($fist_id)." и они выпили его", 'photo'.$photo['owner_id'].'_'.$photo['id']);
                }
                SetGayBar(true);
            }
            else{
                MessSendReply($peer_ids, "прости, брат, но портвейн есть только в ♂Gay Bar♂", $userId);
            }
        }
        elseif(IsStr($body, ["/водка", "/водяра", "/водица", "/налить водк"])){	
            if(GetGayBar()){                        
                $expl = explode('[id', $bodyRead);
                $fist_id = (count($expl) < 2 ) ? "1" : explode('|', $expl[1])[0];;
                if(count($expl) < 2){
                    if(IsStr($body, ["@online"])) //@online
                    {
                        $photo = _bot_uploadPhoto('photo/vodkaall.jpg');   
                        MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." налил всем присутствующим самый лучшей водки в ♂Gay Bar♂", 'photo'.$photo['owner_id'].'_'.$photo['id']);
                    }
                    else{
                        $photo = _bot_uploadPhoto('photo/vodkas.jpg');   
                        MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." налил всем водку в ♂Gay Bar♂", 'photo'.$photo['owner_id'].'_'.$photo['id']);
                    }
                }
                elseif($fist_id == $userId){
                    $photo = _bot_uploadPhoto('photo/vodkaone.jpg');   
                    MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." один в ♂Gay Bar♂ напился водки", 'photo'.$photo['owner_id'].'_'.$photo['id']);
                }
                else{
                    $photo = _bot_uploadPhoto('photo/vodka.jpg');   
                    MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." налил в ♂Gay Bar♂ водку товарищу ".GetLinkUser($fist_id)." и они выпили его", 'photo'.$photo['owner_id'].'_'.$photo['id']);
                }
                SetGayBar(true);
            }
            else{
                MessSendReply($peer_ids, "прости, брат, но водка есть только в ♂Gay Bar♂", $userId);
            }
        }
        elseif(IsStr($body, ["/танец", "/диско", "/данс", "/пати"])){	
            if(GetGayBar()){                        
                $expl = explode('[id', $bodyRead);
                $fist_id = (count($expl) < 2 ) ? "1" : explode('|', $expl[1])[0];;
                if(count($expl) < 2){
                    $photo = _bot_uploadPhoto('photo/gyms.jpg');   
                    MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." устроил танец для всех в ♂Gay Bar♂", 'photo'.$photo['owner_id'].'_'.$photo['id']);
                }
                elseif($fist_id == $userId){
                    $photo = _bot_uploadPhoto('photo/gymone.jpg');   
                    MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." начал кривляться перед зеркалом в ♂Gay Bar♂", 'photo'.$photo['owner_id'].'_'.$photo['id']);
                }
                else{
                    $photo = _bot_uploadPhoto('photo/gym.jpg');   
                    MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." устроил диско пати в ♂Gay Bar♂ с товарищем ".GetLinkUser($fist_id), 'photo'.$photo['owner_id'].'_'.$photo['id']);
                }
                SetGayBar(true);
            }
            else{
                MessSendReply($peer_ids, "прости, брат, но танцы только в ♂Gay Bar♂", $userId);
            }
        }
        elseif(IsStr($body, ["/fuck", "/фак"])){	
            if(GetGayBar()){                        
                $expl = explode('[id', $bodyRead);
                $fist_id = (count($expl) < 2 ) ? "1" : explode('|', $expl[1])[0];
                if(count($expl) < 2){
                    MessSendAttach($peer_ids, GetLinkUser($userId)." сделал ♂fuck♂ всему братству. Фу, ♂latherman♂", GenerateAudioAttachmnt("audio/gay/Oh shit iam sorry.ogg"));                
                }
                elseif($fist_id == $userId){
                    MessSendAttach($peer_ids, GetLinkUser($userId)." сделал ♂fuck♂ сам себе", GenerateAudioAttachmnt("audio/gay/FUCK YOU.ogg"));
                }
                else{
                    MessSendAttach($peer_ids, GetLinkUser($userId)." сделал ♂fuck♂ ".GetLinkUser($fist_id), GenerateAudioAttachmnt("audio/gay/FUCK YOU.ogg"));
                }
                SetGayBar(true);
            }
            else{
                MessSendReply($peer_ids, "прости, брат, но ♂fuck♂ только в ♂Gay Bar♂", $userId);
            }
        }
        elseif(IsStr($body, ["/suck", "/сак"])){	
            if(GetGayBar()){                        
                $expl = explode('[id', $bodyRead);
                $fist_id = (count($expl) < 2 ) ? "1" : explode('|', $expl[1])[0];
                if(count($expl) < 2){
                    MessSendAttach($peer_ids, GetLinkUser($userId)." сделал ♂suck♂ всему братству. Фу, ♂latherman♂", GenerateAudioAttachmnt("audio/gay/Oh shit iam sorry.ogg"));                
                }
                elseif($fist_id == $userId){
                    MessSendAttach($peer_ids, GetLinkUser($userId)." сделал ♂suck♂ сам себе", GenerateAudioAttachmnt("audio/gay/Spit YEEEEEAAAAHHH.ogg"));
                }
                else{
                    MessSendAttach($peer_ids, GetLinkUser($userId)." сделал ♂suck♂ ".GetLinkUser($fist_id), GenerateAudioAttachmnt("audio/gay/Spit YEEEEEAAAAHHH.ogg"));
                }
                SetGayBar(true);
            }
            else{
                MessSendReply($peer_ids, "прости, брат, но ♂suck♂ только в ♂Gay Bar♂", $userId);
            }
        }
		elseif(IsStr($body, ["/самовыпил", "/выпилиться"])){	          
            $pp = SamovipilGD($userId);
			//$pp = "photo/poves.jpg";
			$photo = _bot_uploadPhoto($pp);
            
			if(!IsAdmin($userId)){				
				MessSendAttach($peer_ids, "Брат ".GetLinkUser($userId)." устал от этой жизни и решил совершить самовыпил. Помянем его, да прибудет с ним все самое лучшие в мире ином. Братия, можете вернуть его назад", 'photo'.$photo['owner_id'].'_'.$photo['id'].','.$GachiMics['Два корабля']);
				kickUser($userId);
			}
			else{
				$yt = GetMuteUser($userId);
                if($yt == false){
                    AddMute($userId, '0', '24:0:00');
				}
				MessSendAttach($peer_ids, "Брат ".GetLinkUser($userId)." устал от этой жизни и решил совершить самовыпил. Помянем его, да прибудет с ним все самое лучшие в мире ином. Мут на день, так как самовыпилился админ", 'photo'.$photo['owner_id'].'_'.$photo['id'].','.$GachiMics['Два корабля']);
			}
	
            _bot_PublishPhotoStories($pp);
            MessSendAttach($peer_ids, "", GenerateAudioAttachmnt("audio/gay/Thats power son.ogg"));
		}
		elseif(IsStr($body, ["/виселица", "/повесить", "/казнить"])){	          
            $expl = explode('[id', $bodyRead);
            $fist_id = (count($expl) < 2 ) ? $userId : explode('|', $expl[1])[0];
            $pp = KillGD($fist_id);
			//$pp = "photo/poves.jpg";
			$photo = _bot_uploadPhoto($pp);
            
            if(count($expl) < 2 || $fist_id == $userId){
                MessSendAttach($peer_ids, "Товарищу ".GetLinkUser($userId)." надоело жить и он повесился на виселице", 'photo'.$photo['owner_id'].'_'.$photo['id'].','.$GachiMics['Хардбас']);
            }
            else{
                //AddMute($u_id, '2', '00:10:00');
                MessSendAttach($peer_ids, "Поганый ГТАшник ".GetLinkUser($fist_id)." был повешен на виселице"/*." а так же не может говорить 10 минут"*/, 'photo'.$photo['owner_id'].'_'.$photo['id'].','.$GachiMics['Хардбас']);
            }    

            _bot_PublishPhotoStories($pp);
            MessSendAttach($peer_ids, "", GenerateAudioAttachmnt("audio/gay/WOO.ogg"));
		}
		elseif(IsStr($body, ["/расстрелять", "/расстрел", "/убить", "/растрелять"])){	            
            $expl = explode('[id', $bodyRead);
            $fist_id = (count($expl) < 2 ) ? $userId : explode('|', $expl[1])[0];
            $pp = RastrelGD($fist_id, IsStr($body, ["чекист"]));
			//$pp = IsStr($body, ["чекист"]) ? "rasterl.jpg" : "rasstrel.jpg";
			$photo = _bot_uploadPhoto($pp);
            
            if(count($expl) < 2 || $fist_id == $userId){
                MessSendAttach($peer_ids, "Товарищу ".GetLinkUser($userId)." надоело жить и он напал на НКВД, и они его расстреляли", 'photo'.$photo['owner_id'].'_'.$photo['id'].','.$GachiMics['Хардбас']);
            }
            else{
                MessSendAttach($peer_ids, "Поганый ГТАшник ".GetLinkUser($fist_id)." был расстрелен во славу Коммунизма"/*.", а так же не может говорить 10 минут"*/, 'photo'.$photo['owner_id'].'_'.$photo['id'].','.$GachiMics['Хардбас']);
                 //AddMute($u_id, '1', '00:10:00');
            }          
            //_bot_PublishPhotoStories($pp);
            MessSendAttach($peer_ids, "", GenerateAudioAttachmnt("audio/gay/WOO.ogg"));
		}
		elseif(IsStr($body, ["/шлепнуть по попке", "/по попке", "/попк", "/по жопе"])){
            $photo = _bot_uploadPhoto('photo/popka.jpg');
            $expl = explode('[id', $bodyRead);
            $fist_id = (count($expl) < 2 ) ? "1" : explode('|', $expl[1])[0];;
            if(count($expl) < 2){
                MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." отшлепал всех по попке", 'photo'.$photo['owner_id'].'_'.$photo['id']);
            }
            elseif($fist_id == $userId){
                MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." отшлепал себя по попке. Вот балб♂Ass♂", 'photo'.$photo['owner_id'].'_'.$photo['id']);
            }
            else{
                MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." отшлепал по попке ".GetLinkUser($fist_id), 'photo'.$photo['owner_id'].'_'.$photo['id']);
            }		
            MessSendAttach($peer_ids, "", GenerateAudioAttachmnt("audio/gay/Spank 3.ogg"));
		}
		elseif(IsStr($body, ["/наше сообщество", "/сообщество", "/братство"])){			
			MessSend($peer_ids, "Сообщество авторов этой беседы: [club165104294|Танцы Рикардо Милоса]", $userId);
		}
        elseif(IsStr($body, ["/информация" , "/сведения"])){
            $groups = getGroupsById('203187765');
			file_put_contents("photo/botava.jpg", file_get_contents($groups[0]['photo_200']));
			$photo = _bot_uploadPhoto('photo/botava.jpg');
			MessSendAttach($peer_ids, "ГАЧИ бот. Версия ".BOTVERSION."\nOpen Source: https://github.com/Microfcorp/GymBot", 'photo'.$photo['owner_id'].'_'.$photo['id']);
		}	
        elseif(IsStr($body, ["/есть" , "/жрать", "/кушать"])){
            $expl = explode('[id', $bodyRead);
                $fist_id = (count($expl) < 2 ) ? "1" : explode('|', $expl[1])[0];;
                if(count($expl) < 2){
                    $photo = _bot_uploadPhoto('photo/foodall.jpg');   
                    MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." очень сильно хочет кушать и просит еды", 'photo'.$photo['owner_id'].'_'.$photo['id']);
                    MessSendAttach($peer_ids, "", GenerateAudioAttachmnt("audio/gay/Orgasm 6.ogg"));
                }
                elseif($fist_id == $userId){
                    $photo = _bot_uploadPhoto('photo/food.jpg');   
                    MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." отправил кушать сам себя. Кароч поел", 'photo'.$photo['owner_id'].'_'.$photo['id']);
                    MessSendAttach($peer_ids, "", GenerateAudioAttachmnt("audio/gay/She gave me quite a show.ogg"));
                }
                else{
                    $photo = _bot_uploadPhoto('photo/foodone.jpg');   
                    MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." отправил кушать товарища ".GetLinkUser($fist_id)." придав ему ускорение", 'photo'.$photo['owner_id'].'_'.$photo['id']);
                    MessSendAttach($peer_ids, "", GenerateAudioAttachmnt("audio/gay/Oh yes sir.ogg"));
                }
		}			
		elseif(IsStr($body, ["/обновить закон"])){
			if(!IsAdmin($userId)){
				MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);
			}
			else{
				$mid = MessSend($peer_ids, "📌ЗАКОН📌" . GetPin(), $userId);
				//PinMessage($mid[0]);
			}
		}
		elseif(IsStr($body, ["/отчистить закон"])){
			if(!IsAdmin($userId)){
				MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);
			}
			else{
				UpdatePin("");
				$mid = MessSend($peer_ids, "Закона больше нет! АНАРХИЯ!", $userId);
				PinMessage($mid[0]);
			}
		}
        elseif(IsStr($body, ["коммуни", "социали", "ленин", "сталин"])){ //КОММУНИЗМ
			MessSendAttach($peer_ids, "Во славу Коммунизма", $SAudio[0]);
		}
		elseif(IsStr($body, ["/help", "/справка", "/комманды"])){			
			MessSend($peer_ids, "Привет. Я Гачи бот, разработан я был [id125883149|Лехой] специально для беседы Славян и Братских народов. Мне нужны права администратора для работы в беседе
			Список аргументов:
                <> - обязательные
                {} - необязательные
                ** - прикриленные или пересланные сообщения
            Я умею:");
			MessSend($peer_ids, "📍- /Привет - КОШКАТУН
			📍- /информация - версия и настройки бота
			📍- /изменить имя <новое имя> - изменяет ваше имя
			📍- /мой id - возвращает ваш id
			📍- /кто я - показывает вашу Gachi роль
			📍- /посмотреть роли - показывает все доступные роли
			📍- /я буду <название роли> - изменяет вашу роль
			📍- /сделать fisting ass {пользователь} - сделать ♂fisting ass♂ пользователю
			📍- /сделать cum {пользователь} - сделать ♂cum♂ пользователю
			📍- /пацанский гачимучи - возвращает запись из сообщества [club113661329|Пацанское Gachimuchi]
			📍- /славянский пост - возвращает запись из [club165104294|этого сообщества]
			📍- /словарь - словарь запрященных слов
			📍- /gay bar - запускает гей бар
            📍- /налить пиво {пользователь} - наливает пользователю пиво в ♂Gay Bar♂
			📍- /налить портвейн {пользователь} - наливает пользователю портвейн в ♂Gay Bar♂
			📍- /налить водку {пользователь} - наливает пользователю водку в ♂Gay Bar♂
            📍- /танец {пользователь} - устраивает танец с пользователем в ♂Gay Bar♂
			📍- /кушать {пользователь} - отправляет пользователя кушать
			📍- /boy {пользователь} - ♂BOY NEXT DOOR♂
            📍- /fuck {пользователь} - делает пользователю ♂fuck♂
            📍- /suck {пользователь} - делает пользователю ♂suck♂
            📍- /отвалить письку {пользователь} - делает массовый отвал письки у пользователя, ну...
            📍- /отвалить сиськи {пользователь} - делает отвал сисек у пользователя, ну или нет)
			📍- /фраза - рандомная фраза из ♂Gay Bar♂			
			📍- /сводка - сводка за текущий славянский день
            📍- /молчащие - список всех молчащих пользователей и причины мута
			📍- /календарь - настольный календарь славянина с путеводителем по дрочке
			📍- /славянское фото - обложка сообщества славян
			📍- /наше сообщество - возваращает ссылку на сообщество славян
            📍- /какая роль у <пользователь> - Показывает роль пользователя
			📍- /самовыпил - Производит самовыпил пользователя из беседы
			📍- /повесить <пользователь> - Вешает пользователя на виселице
			📍- /расстрелять {чекист} <пользователь> - НКВД расстреливает пользователя (возможно участие малолетнего чекста)
			📍- /шлепнуть по попке {пользователь} - Шлепает пользователя по попке
                📍Действия только для администроторовв:📍
            📍- /чистка баб {количетсво} - автоматически отчистить {количество} баб (только для админов)
            📍- /чистка трупов {количетсво} - автоматически отчистить {количество} трупов и аннонимнусов (только для админов)
            📍- /итоги опроса *опрос* - подводит итоги опроса (только для админов)
            📍- /удалить *сообщения* - удаляет данные сообщения из диалога (только для админов)
            📍- /призвать <пользователь> <сообщение> - призывает пользователя сообщением ему (только для админов)
			📍- /добавить слово <вид наказания> слово <слово> - добовляет в словарь запрещенное слово (только для админов)
			📍- /удалить слово <слово> - удаляет слово из словаря (только для админов)
			📍- /изменить роль <пользователь> <роль> - изменяет роль пользователя (только для админов)
			📍- /кокнуть имя <пользователь> <новое имя> - изменяет имя пользователю (только для админов)
			📍- /созвать всех <сообщение созыва> - созывает участников беседы, которые разрешили сообщения сообщества (только для админов)
			📍- /замолчать <пользователь> - заставляет пользователя молчать месяц (только для админов)
			📍- /говорить <пользователь> - разрешает пользователю говорить (только для админов)			
			📍- /изгнать <пользователь> - исключает пользователя из беседы (только для админов)
			📍- /принять закон <закон> - Принимает закон и закрепляет его в шапке (только для админов)
			📍- /изменить закон <статья для поиска> \n <закон> - Изменяет строку с законом по регулярке (только для админов)
			📍- /обновить закон - Перепечатывает и перезакрепляет закон (только для админов)
			📍- /удалить закон <закон> - Удаляет строку с законом по регулярке (только для админов)
			📍- /отчистить закон - Удаляет все законы (только для админов)
			📍- /похвалить {пользователь} - Выдает похвалу этому пользователю (только для админов)
			📍- /онлайн - показывает, кто из братьев сейчас в сети (только для админо)");
		}
		elseif($chataction != null){ //События чата
			$typea = $chataction->type;
			$member_id = $chataction->member_id;
			if($typea == "chat_invite_user"){
				if($member_id < 0){
					MessSend($peer_ids, "Иностранному боту [club$member_id|@$member_id] нечего делать в Славянском братстве");
                    kickUser($member_id);
				}
				else{
					//$memberName = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$member_id}&v=5.130&access_token={$token}"))->response[0]->first_name;
					//MessSend($peer_ids, "Приветствуем брата-славянина ".LinkUser($member_id, $memberName) . "в славянском братстве");
					MessSendReply($peer_ids, "здравствуй супчик голубчик", $member_id);
				}
			}
			elseif($typea == "chat_kick_user"){
				$memberName = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$member_id}&v=5.130&access_token={$token}"))->response[0]->first_name;
				//MessSend($peer_ids, LinkUser($member_id, $memberName)." Оказался предателем и покинул нас. Пользователь исключен принудительно");
				kickUser($member_id);
			}
		}		
		elseif(IsStr($body, ["/"])){ //Во всех остальных случаях			
			//MessUpdate($userId, CurrentMessage($userId), "Каво?",$token);
			setActivity();
		}
	}
 
    // Если это уведомление о вступлении в группу
    elseif($data->type == 'group_join'){
        //...получаем id нового участника
        $userId = $data->object->from_id;
 
        //затем с помощью users.get получаем данные об авторе
        $userInfo = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$userId}&v=5.8&access_token={$token}"));
 
        //и извлекаем из ответа его имя
        $user_name = $userInfo->response[0]->first_name;
 
        MessSend($userId, "Хай, гей. Я бот для спец-беседы как бы".$user_name,$token);
	}
    
    // Если это разрешение на получение сообщений
    elseif($data->type == 'message_allow'){
        //...получаем id нового участника
        $userId = $data->object->from_id;
 
        MessSend($userId, "Спасибо за активацию функций сообщений. Теперь вас при необходимости смогут призвать ваши братья", $token);
	}

echo('ok');
?>
