<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// SEND OUT THE JSON!! Enjoy your brew
header('Content-Type: application/json');

// $request_get   = $_GET['text'];
$request_words = preg_replace('/!teamsite\s+/', '', $_POST['text']);

//Options
$channel  = '#teamsite-bot';
$bot_name = 'Teamsite-Helper';
$icon     = ':alien:';

$json = file_get_contents('http://mrtawan.com/slack/xml.php');
$obj = json_decode($json);

foreach ($obj as $item) {
	if (preg_match('/'.$request_words.'/', $item->keyword)) {
		
		$message  = '```'.htmlspecialchars_decode($item->code).'```';
		$ref  = htmlspecialchars_decode($item->ref);
		$attachments = array([
		    'color'    => '#ff6600',
		    'fields'   => array(
		        [
		            'title' => 'Reference:',
		            'value' => $ref,
		            'short' => true
		        ],
		    )
		]);


		$data = array(
		    'channel'     => $channel,
		    'username'    => $bot_name,
		    'text'        => $message,
		    'icon_emoji'  => $icon,
		    'attachments' => $attachments,
		    'mrkdwn'	  => true
		);
		
		echo json_encode($data);
	}
}

?>