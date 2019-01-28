<?php


// curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    // 'X-Apple-Tz: 0',
    // 'X-Apple-Store-Front: 143444,12'
// ));

class Todoist
{
	const TODOIST_API_URL = 'https://beta.todoist.com/API/v8/';
	
	private $token;
	
	public function __construct($todoist_token)
	{
		$this->token = $todoist_token;
	}
	
	private function request($url, $http_post = false, $post_params)
	{
		// Authorization: Bearer $token
	
		_dbg("url: $url");
		_dbg("post_params: " . print_r($post_params, true));
		
		$full_url = self::TODOIST_API_URL . $url;
		_dbg("full url: $full_url");
		
		$auth_header =  "Authorization: Bearer {$this->token}" ;
		_dbg("$auth_header");
		
		$ch = curl_init($full_url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth_header, "Content-Type: application/json", "X-Request-Id: " . uniqid() ));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, ( $http_post ? 1 : 0 ) );
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_params));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_STDERR, fopen("/var/www/html/todoist-voice-bot/tmp.log", 'w+'));
		$result = curl_exec($ch);
		
		_dbg($result);
		curl_close($ch);
		
		return $result;	
	}
	
	public function addTaskToInbox($task_text)
	{
		$task = array( 'content' => $task_text );
		return $this->request('tasks', true, $task);
	}
}