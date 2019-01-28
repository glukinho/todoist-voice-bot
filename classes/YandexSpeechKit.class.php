<?php



class YandexSpeechKit
{
	const YANDEX_SPEECK_KIT_URL = 'https://stt.api.cloud.yandex.net/speech/v1/stt:recognize/';

	public function __construct($folder_id, $iam_token)
	{
		$this->folder_id = $folder_id;
		$this->iam_token = $iam_token;
	}

	public function recognize($file)
	{
		// Authorization: Bearer $token
	
		_dbg("file: $file");
		
		// $full_url = self::YANDEX_SPEECK_KIT_URL . http_build_query( [ 'topic' => 'general', 'folderId' => $this->folder_id,  ] );
		$full_url = self::YANDEX_SPEECK_KIT_URL . '?topic=general&folderId=b1g9uqbho9p2b52psrbv';
		_dbg("full url: $full_url");
		
		$auth_header =  "Authorization: Bearer {$this->iam_token}" ;
		_dbg("$auth_header");
		
		$ch = curl_init($full_url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth_header, "Transfer-Encoding: chunked" ));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1 );
		curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($file));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_STDERR, fopen("/var/www/html/todoist-voice-bot/yandex.log", 'w+'));
		$result = curl_exec($ch);
		
		_dbg($result);
		curl_close($ch);
		
		return $result;	
	}
}