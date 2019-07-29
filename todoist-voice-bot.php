<?php
include "/var/www/html/todoist-voice-bot/classes/todoist.class.php";
include "/var/www/html/todoist-voice-bot/classes/YandexSpeechKit.class.php";


$yandex_folder_id = 'b1g9uqbho9p2b52psrbv';
$yandex_iam_token = json_decode(file_get_contents('yandex-iam-token.json'))->iamToken;


$debug =		 		true;
$logfile = 				"/var/www/html/todoist-voice-bot/todoist-voice-bot.log";
$users_file = 			"/var/www/html/todoist-voice-bot/users.json";
$file_tmp_path = 		"/var/www/html/todoist-voice-bot/tmp/";
			
$token = 				'771879835:AAFAd86vndX4QiHR9_dFYBlssLst9ooY68o';
$bot_url = 				"https://api.telegram.org/bot" . $token;
$file_url = 			"https://api.telegram.org/file/bot" . $token;

function answerHttp($response) {
	http_response_code($response);
	die();
}

function _log($text) {
	global $logfile;
	global $update_id;
	
	$date = DateTime::createFromFormat('U.u', microtime(TRUE));
	
	// file_put_contents($logfile, date("Y-m-d H:i:s") . " - [$update_id] - " . $text . "\n", FILE_APPEND);
	file_put_contents($logfile, $date->format('Y-m-d H:i:s.u') . " - [$update_id] - " . $text . "\n", FILE_APPEND);
}

function _dbg($text) {
	global $debug;
	global $update_id;
	
	$date = DateTime::createFromFormat('U.u', microtime(TRUE));
	
	if ($debug) {
		global $logfile;
		file_put_contents($logfile, $date->format('Y-m-d H:i:s.u') . " - [$update_id] - [DEBUG] - " . $text . "\n", FILE_APPEND);
	}
}

function sendRequest($method, $params) {
	global $bot_url;
	
	_dbg("method: $method");
	_dbg("params: " . print_r($params, true));
	
	$ch = curl_init($bot_url . '/' . $method);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$result = curl_exec($ch);
	
	_dbg($result);
	curl_close($ch);
	
	return $result;
}

function sendMessage($chat_id, $text, $parse_mode = false, $keyboard = false, $disable_notification = false) {
	_log("sending message: '{$text}' to chat id: '{$chat_id}'");
	
	$message = array('chat_id' => $chat_id, 'text' => $text);
	
	if ($parse_mode) $message['parse_mode'] = $parse_mode;
	if ($disable_notification) $message['disable_notification'] = $disable_notification;
	if ($keyboard) $message['reply_markup'] = json_encode($keyboard);
	
	sendRequest('sendMessage', $message);
}

function reply($text, $parse_mode = false, $keyboard = false, $disable_notification = false) {
	global $input;
	_log("replying: '{$text}'");
	sendMessage($input->message->chat->id, $text, $parse_mode, $keyboard, $disable_notification);
}

function getFile($file_id) {
	_log("fetching file id: $file_id");
	
	
}




// reply buttons markup syntax: r1b1|r1b2|r1b3^r2b1^r3b1|r3b2
// test|ejdowej^nldffn|lkrjf|jljflsf^
function generateReplyKeyboard($markup, $resize_keyboard = false, $one_time_keyboard = false) {
	
	$rows = explode("^", $markup);
	foreach ($rows as &$row) {
		$row = explode("|", $row);
	}
	
	_log($markup);
	_dbg(print_r($rows, true));
	
	$keyb = 
	[ 'keyboard' => 
		$rows
	];
	
	if ($resize_keyboard) $keyb['resize_keyboard'] = true;
	
	if ($one_time_keyboard) $keyb['one_time_keyboard'] = true;
	
	return $keyb;
}


// creates simple inline keyboard with one button
function generateInlineKeyboardSimple($keyboard_type, $label, $data) {
	switch($keyboard_type) {
		case "inline":
			$keyb =  
			[ 'inline_keyboard' => 
				[
					[ 
						[
							'text' => $label,
							'callback_data' => $data
						]
					]
				]
			];
			
			_dbg(print_r($keyb, true));
			break;
			
		default:
			$keyb = false;
	}
	
	return $keyb;
}

// just returns keyboard object
function generateInlineKeyboard($keyboard_object) {
	return $keyboard_object;
}


function auth($user_id) {
	global $users_file;
	
	$users = json_decode(file_get_contents($users_file));
	// _dbg(print_r($users, true));
	_log("auth | user id: $user_id");
	
	$result = false;
	foreach($users as $user) {
		if ($user->telegram_id == $user_id) {
			$username = $user->username;
			$result = $user;
		}
	}
	
	_log( ( $result ? "auth | known as {$username}" : "auth | unknown user") );
	
	return $result;
}

function answerCallbackQuery($callback_query_id) {
	sendRequest("answerCallbackQuery", [ 'callback_query_id' =>	$callback_query_id ] );
}



$input_json = file_get_contents('php://input');

$request_id = $_SERVER['UNIQUE_ID'];

// _dbg(print_r($_SERVER, TRUE));
_dbg($input_json);

$input = json_decode($input_json);

_dbg(print_r($input, true));

$update_id = $input->update_id;



// reply('test ok');


if (isset($input->message)) {
	$debug_str = "User {$input->message->from->username} (user id {$input->message->from->id}) wrote: \"{$input->message->text}\" (message id {$input->message->message_id})";
	_log($debug_str);
	
	$auth = auth($input->message->from->id);
	_dbg(print_r($auth, true));
	
	if ( !$auth ) { 
		_log("{$input->message->from->id} is unknown user");
		reply("Я тебя не знаю!");
		die();
	}
	
	if (isset($input->message->voice)) {
		_log("Voice message received");
		$file_id = $input->message->voice->file_id;
		$file_path = json_decode( sendRequest( 'getFile', [ 'file_id' => $file_id ] ) )->result->file_path;
		$file_full_url = $file_url . "/$file_path";
		_log($file_path);
		
		$tmpfile = uniqid() . ".oga";
		_log($tmpfile);
		$file = file_get_contents($file_full_url);
		$file_tmp_full_path = $file_tmp_path . $tmpfile;
		file_put_contents($file_tmp_full_path, $file);
		
		$pathinfo = pathinfo($file_tmp_full_path);
		$opus_file = "{$pathinfo["dirname"]}/{$pathinfo["filename"]}.opus";
		$cmd = "/usr/bin/opusdec --force-wav $file_tmp_full_path - | /usr/bin/opusenc - $opus_file";
		_dbg("cmd: $cmd");
		exec($cmd);
		
		
		$yandex = new YandexSpeechKit($yandex_folder_id, $yandex_iam_token);
		$recognized_json = $yandex->recognize($opus_file);
		$recognized = json_decode($recognized_json);
		_dbg("recognized text: $recognized_json");
		
		reply("Вы сказали: '{$recognized->result}'");
		
		$todoist = new Todoist($auth->todoist_token);
		$add_task_json = $todoist->addTaskToInbox($recognized->result);
		$add_task = json_decode($add_task_json);
		
		if (isset($add_task->id)) {
			reply("Задача с id {$add_task->id} и текстом '{$recognized->result}' создана");
		} else {
			reply("Ошибка при создании задачи!");
		}
	
	} else {
		// reply ($input->message->text);
		
		$todoist = new Todoist($auth->todoist_token);
		$add_task_json = $todoist->addTaskToInbox($input->message->text);
		$add_task = json_decode($add_task_json);
		
		if (isset($add_task->id)) {
			reply("Задача с id {$add_task->id} и текстом '{$input->message->text}' создана");
		} else {
			reply("Ошибка при создании задачи!");
		}
	}
}