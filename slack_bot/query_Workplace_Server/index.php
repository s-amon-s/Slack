<?php
	
 	$sharecode_webhook 	= 'https://hooks.slack.com/services/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';

	date_default_timezone_set('Asia/Bangkok');


        //Options
        $channel  = '#teamsite-bot';
        $bot_name = 'Webhook';
        $icon     = ':alien:';
        $message  = '
```
<?php
	echo "Hello, World!";
?>
```';
        $attachments = array([
            'color'    => '#ff6600',
            'fields'   => array(
                [
                    'title' => 'Reference:',
                    'value' => '<http://foo.com/>',
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
        $data_string = json_encode($data);
        $ch = curl_init($sharecode_webhook);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
            );
        //Execute CURL
        $result = curl_exec($ch);
        return $result;        
 

?>