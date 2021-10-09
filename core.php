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
		//$idmes = $data->object->message->id; //id сообщения
		$peer_ids = $data->object->message->peer_id; //id назначенияы (беседы)
		$messageid = $data->object->message->conversation_message_id; //id сообщения в беседе
		$chataction = isset($data->object->message->action) ? $data->object->message->action : null; //id назначенияы
		
		if($userId < 0) //Если пишет сообщество
			exit("OK");
		
		//Получаем имя пользователя
        $userName = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$userId}&v=5.130&access_token={$token}"))->response[0]->first_name;
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
			if(IsStr($body, [$sss])){
				if($sl[3] == 3 && !IsAdmin($userId)){
					MessSend($peer_ids, GetLinkUser($userId)."Нарушил славянско-братский закон и был изгнан за употребление '$sss'", $userId);
					kickUser($userId);
				}
				elseif($sl[3] == 2){
					$pp = KillGD($userId);
					$photo = _bot_uploadPhoto($pp);
                    AddMute($userId, '2', '00:10:00');
					MessSendAttach($peer_ids, "Поганый ГТАшник ".GetLinkUser($userId)." нарушил словарное братское правило и был повешен на виселице за употребление '$sss', так же ему выдан мут на 10 минут", 'photo'.$photo['owner_id'].'_'.$photo['id']);
				}
				elseif($sl[3] == 1){
					$pp = RastrelGD($userId, true);
					$photo = _bot_uploadPhoto($pp);
                    AddMute($userId, '1', '00:10:00');
				    MessSendAttach($peer_ids, "Поганый ГТАшник ".GetLinkUser($userId)." нарушил словарное братское правило и был расстрелен НКВД во славу Коммунизма с участием малолетнего чекиста за употребление '$sss', а так же ему выдан мут на 10 минут", 'photo'.$photo['owner_id'].'_'.$photo['id']);
				}
				elseif($sl[3] == 0){
					MessSend($peer_ids, GetLinkUser($userId)."Получает предупреждение, об употреблении запрещенных слов за употребление '$sss'", $userId);
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
				$datee = date("d.m.Y H:M");
				$pinsss = "📜Товарищ ".GetLinkUser($userId)."прянял закон от $datee:📜\n  $mess";
				UpdatePin(GetPin() . "<br><br>" . $pinsss);
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
		elseif(IsStr($body, ["/кто я", "/мое гачи", "/моя роль"])){
			MessSendReply($peer_ids, "ты - ".RoleToEnglish(GetRole($userId)), $userId);
		}
		elseif(IsStr($body, ["/добавить в словарь", "/новое в словарь", "/добавить словарь"])){
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
				MessSend($peer_ids, GetLinkUser($u_id)."был изменен на ♂".RoleToEnglish($role)."♂", $userId);
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
		elseif(IsStr($body, ["/сделать fisting", "/сделать fisting ass", "/сделать ♂fisting ass♂", "/fisting ass"])){	
			$fist_id = explode('|', explode('[id', $bodyRead)[1])[0];
			if($fist_id ==$userId){
				MessSend($peer_ids, GetLinkUser($userId)."поехал крышей и сделал ♂fisting ass♂ сам себе", $userId);
			}
			elseif(GetRole($fist_id) < GetRole($userId)){
				MessSend($peer_ids, GetLinkUser($userId)."сделал ♂fisting ass♂ ".GetLinkUser($fist_id), $userId);
			}
			elseif(GetRole($fist_id) > GetRole($userId)){
				MessSend($peer_ids, GetLinkUser($userId)."хотел сделать ♂fisting ass♂ ".GetLinkUser($fist_id)."но у него не хватило сил", $userId);
			}
			elseif(GetRole($fist_id) == GetRole($userId)){
				$ok = rand(0, 2);
				if($ok == 1) MessSend($peer_ids, GetLinkUser($userId)."долго боролся, и, наконец сделал ♂fisting ass♂ ".GetLinkUser($fist_id), $userId);
				else MessSend($peer_ids, GetLinkUser($userId)."долго боролся и не смог сделал ♂fisting ass♂ ".GetLinkUser($fist_id), $userId);
			}
		}
		elseif(IsStr($body, ["/сделать cum", "/cum"])){	
			$expl = explode('[id', $bodyRead);
			$fist_id = (count($expl) < 2 ) ? "1" : explode('|', $expl[1])[0];;
			if(count($expl) < 2){
				MessSend($peer_ids, GetLinkUser($userId)."сделал ♂".GetRandomCum()."♂ в этой ♂Gym♂", $userId);
			}
			elseif($fist_id ==$userId){
				MessSend($peer_ids, GetLinkUser($userId)."поехал ♂Anal♂ и сделал ♂".GetRandomCum()."♂ сам себе", $userId);
			}
			elseif(GetRole($fist_id) <= GetRole($userId)){
				MessSend($peer_ids, GetLinkUser($userId)."сделал ♂".GetRandomCum()."♂ ".GetLinkUser($fist_id), $userId);
			}
			elseif(GetRole($fist_id) > GetRole($userId)){
				MessSend($peer_ids, GetLinkUser($userId)."хотел сделать ♂".GetRandomCum()."♂ ".GetLinkUser($fist_id)."но его завалили", $userId);
			}
		}
		elseif(IsStr($body, ["/онлайн", "/online", "кто в сети", "брат кто в сети"])){
			if(!IsAdmin($userId)) MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);			
			else MessSend($peer_ids, GetUsersOnline(), $userId);
		}
		elseif(IsStr($body, ["/получить пацанское гачимучи", "/пацанское гачимучи", "/получить пацанский гачимучи", "/пацанский гачимучи"])){
			$wall = GetWall('-113661329');
			$wallid = $wall['items'][0]['id'];
			MessSendAttach($peer_ids, "Случайная запись из [club113661329|Пацанское Gachimuchi ⚦]", "wall-113661329_$wallid");
		}
		elseif(IsStr($body, ["/получить славян", "/славянский пост", "/славяне"])){
			$wall = GetWall('-165104294');
			$wallid = $wall['items'][0]['id'];
			MessSendAttach($peer_ids, "Случайная запись из [club165104294|Танцы Рикардо Милоса]", "wall-165104294_$wallid");
		}
		elseif(IsStr($body, ["/изгнать", "/выгнать", "/прогнать"])){			
			$u_id = explode('|', explode('[id', $bodyRead)[1])[0];
			if($u_id == $userId) MessSendReply($peer_ids, "нельзя изгнать себя", $userId);
			else{				
				if(!IsAdmin($userId)) MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);
				else{
					if(IsAdmin($u_id)) MessSendReply($peer_ids, "нельзя изгать администратора", $userId);
					else{
						kickUser($u_id);
						MessSend($peer_ids, GetLinkUser($u_id)."был изгнан из беседы", $userId);
					}
				}
			}			
		}
		elseif(IsStr($body, ["/созвать всех"])){
			if(!IsAdmin($userId)){
				MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);
			}
			else{
				$mess = mb_substr($bodyRead, 14);
				$countm = 0;
				foreach (getConversationMembers()['profiles'] as $member) { // Прошли по массиву для регистрации пользователей по их id
					$countm .= 1;
					$user_id = $member['id']; // Получили id пользоавтеля
					if($member['online'] == 1) MessSendReply($peer_ids, "Вас созывают в беседу Славяне и Братские народы<br>".$mess, $userId);        
				}
				MessSend($peer_ids, "Все $countm участников были созваны", $userId);
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
				MessSend($u_id, "Вас призывает ".GetLinkUser($userId)." в беседу Славяне и Братские народы с сообщением: ".$mess, $userId);
				MessSendReply($peer_ids, "Вы призвали ".GetLinkUser($userId), $userId);				
			}
		}
		elseif(IsStr($body, ["/календарь"])){
			$month = date('m');
			$day = date('d');
			$isdrochka = ($month == 1 || $month == 2 || $month == 6 || $month == 7 || $month == 9 || $month == 10 || $month == 12);
			$monthrus = $Month_r[$month];
			MessSendReply($peer_ids, "Сегодня $day число.<br>В этот ".($isdrochka ? "чудный" : "славный")." месяц $monthrus дрочить Славянам ".($isdrochka ? "разрешается)" : "ЗАПРЕЩАЕТСЯ"), $userId);
		}
		elseif(IsStr($body, ["/какая роль у"])){	
			$u_id = explode('|', explode('[id', $bodyRead)[1])[0];
			MessSend($peer_ids, "Товарищ ".GetLinkUser($u_id)." - ♂".RoleToEnglish(GetRole($u_id))."♂", $userId);
		}
        elseif(IsStr($body, ["/замолчать", "/заткнуть", "/мут", "/mute"])){	
			$u_id = explode('|', explode('[id', $bodyRead)[1])[0];
            if(IsAdmin($u_id) && !IsAdmin($userId)){
                MessSendReply($peer_ids, "Нельзя заставить молчать администратора или нужно быть администратором", $userId);
            }
            else{
                $yt = GetMuteUser($u_id);
                if($yt == false){
                    AddMute($u_id, '0', '838:0:00');
                    MessSend($peer_ids, "Товарищ ".GetLinkUser($u_id)." заставлен молчать месяц", $userId);
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
			$u_id = explode('|', explode('[id', $bodyRead)[1])[0];
			$photo = _bot_uploadPhoto('photo/pohval.jpg');
			MessSendAttach($peer_ids, "Товарищу ".GetLinkUser($u_id)." выдвинута благодарность от администрации, а так же кусочек похвалы", 'photo'.$photo['owner_id'].'_'.$photo['id']);
		}
		elseif(IsStr($body, ["/виселица", "/повесить", "/казнить"])){	
			$u_id = explode('|', explode('[id', $bodyRead)[1])[0];
			$pp = KillGD($u_id);
			$photo = _bot_uploadPhoto($pp);
			if($u_id == $userId) MessSendAttach($peer_ids, "Товарищу ".GetLinkUser($u_id)."надоело жить и он повесился на виселице", 'photo'.$photo['owner_id'].'_'.$photo['id']);
			else{
                AddMute($u_id, '2', '00:10:00');
                MessSendAttach($peer_ids, "Поганый ГТАшник ".GetLinkUser($u_id)." был повешен на виселице, а так же не может говорить 10 минут", 'photo'.$photo['owner_id'].'_'.$photo['id']);
            }
		}
		elseif(IsStr($body, ["/расстрелять", "/расстрел", "убить"])){	
			$u_id = explode('|', explode('[id', $bodyRead)[1])[0];
			$pp = RastrelGD($u_id, IsStr($body, ["чекист"]));
			$photo = _bot_uploadPhoto($pp);
			if($u_id == $userId) MessSendAttach($peer_ids, "Товарищу ".GetLinkUser($u_id)."надоело жить и он напал на НКВД, и они его расстреляли", 'photo'.$photo['owner_id'].'_'.$photo['id']);
			else{
                MessSendAttach($peer_ids, "Поганый ГТАшник ".GetLinkUser($u_id)." был расстрелен во славу Коммунизма, а так же не может говорить 10 минут", 'photo'.$photo['owner_id'].'_'.$photo['id']);
                AddMute($u_id, '1', '00:10:00');
            }
		}
		elseif(IsStr($body, ["/шлепнуть по попке", "/по попке"])){	
			$u_id = explode('|', explode('[id', $bodyRead)[1])[0];
			$photo = _bot_uploadPhoto('photo/popka.jpg');
			if($u_id == $userId) MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($u_id)."отшлепал себя по попке", 'photo'.$photo['owner_id'].'_'.$photo['id']);
			else MessSendAttach($peer_ids, "Товарищ ".GetLinkUser($userId)." отшлепал по попке ".GetLinkUser($u_id), 'photo'.$photo['owner_id'].'_'.$photo['id']);
		}
		elseif(IsStr($body, ["/наше сообщество"])){			
			MessSend($peer_ids, "Сообщество авторов этой беседы: [club165104294|Танцы Рикардо Милоса]", $userId);
		}		
		elseif(IsStr($body, ["/обновить закон"])){
			if(!IsAdmin($userId)){
				MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);
			}
			else{
				$mid = MessSend($peer_ids, "📌ЗАКОН📌" . GetPin(), $userId);
				PinMessage($mid[0]);
			}
		}
		elseif(IsStr($body, ["/удалить закон"])){
			if(!IsAdmin($userId)){
				MessSendReply($peer_ids, "это действие доступно только администраторам", $userId);
			}
			else{
				UpdatePin("");
				$mid = MessSend($peer_ids, "Закона нет", $userId);
				PinMessage($mid[0]);
			}
		}
		elseif(IsStr($body, ["/help", "/справка", "/комманды"])){			
			MessSend($peer_ids, "Привет. Я гачи бот, разработан я был [id125883149|Лехой] специально для беседы Славян и Братских народов. Мне нужны права администратора для работы в беседе
			Я умею:");
			MessSend($peer_ids, "- /Привет
			📍- /Сменить имя <новое имя> - изменяет ваше имя
			📍- /мой id - возвращает ваш id
			📍- /кто я - показывает вашу Gachi роль
			📍- /посмотреть роли - показывает все доступные роли
			📍- /я буду <название роли> - изменяет вашу роль
			📍- /сделать fisting ass <пользователь> - вы делаете ♂fisting ass♂ пользователю
			📍- /сделать cum <пользователь> - вы делаете ♂cum♂ пользователю
			📍- /пацанский гачимучи - возвращает запись из сообщества [club113661329|Пацанское Gachimuchi]
			📍- /славянский пост - возвращает запись из [club165104294|этого сообщества]
			📍- /словарь - словарь запрященных слов
			📍- /добавить слово <вид наказания> слово <слово> - добовляет в словарь запрещенное слово (только для админов)
			📍- /удалить слово <слово> - удаляет слово из словаря (только для админов)
			📍- /изменить роль <пользователь> <роль> - изменяет роль пользователя (только для админов)
			📍- /кокнуть имя <пользователь> <новое имя> - изменяет имя пользователю (только для админов)
			📍- /созвать всех <сообщение созыва> - созывает участников беседы, которые разрешили сообщения сообщества (только для админов)
			📍- /замолчать <пользователь> - заставляет пользователя молчать месяц (только для админов)
			📍- /говорить <пользователь> - разрешает пользователю говорить (только для админов)
			📍- /молчащие - список всех молчащих пользователей и причины мута
			📍- /изгнать <пользователь> - исключает пользователя из беседы (только для админов)
			📍- /призвать <пользователь> <сообщение> - призывает пользователя сообщением ему
			📍- /календарь - настольный календарь славянина с путеводителем по дрочке
			📍- /славянское фото - обложка сообщества славян
			📍- /наше сообщество - возваращает ссылку на сообщество славян
			📍- /принять закон <закон> - Принимает закон и закрепляет его в шапке (только для админов)
			📍- /обновить закон - Перепечатывает и перезакрепляет закон (только для админов)
			📍- /удалить закон - Удаляет все законы (только для админов)
			📍- /какая роль у <пользователь> - Показывает роль пользователя
			📍- /повесить <пользователь> - Вешает пользователя на виселице
			📍- /расстрелять <чекист> <пользователь> - НКВД расстреливает пользователя (возможно участие малолетнего чекста)
			📍- /шлепнуть по попке <пользователь> - Шлепает пользователя по попке
			📍- /похвалить <пользователь> - Выдает похвалу этому пользователю (только для админов)
			📍- /онлайн - показывает, кто из братьев сейчас в сети (только для админо)");
		}
		elseif($chataction != null){ //События чата
			$typea = $chataction->type;
			$member_id = $chataction->member_id;
			if($typea == "chat_invite_user"){
				if($member_id < 0){
					MessSend($peer_ids, "Приветствуем сообщество [club$member_id|@$member_id] в славянском братстве");
				}
				else{
					$memberName = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$member_id}&v=5.130&access_token={$token}"))->response[0]->first_name;
					//MessSend($peer_ids, "Приветствуем брата-славянина ".LinkUser($member_id, $memberName) . "в славянском братстве");
					MessSendReply($peer_ids, "здравствуй супчик голубчик", $member_id);
				}
			}
			elseif($typea == "chat_kick_user"){
				$memberName = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$member_id}&v=5.130&access_token={$token}"))->response[0]->first_name;
				MessSend($peer_ids, LinkUser($member_id, $memberName)."Оказался предателем и покинул нас. Пользователь не исключен принудительно");
				//kickUser($member_id);
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
 
        MessSend($userId, "Хай, гей ".$user_name,$token);
	}

echo('ok');
?>
