<?php
use Webhook\Callback;
use Webhook\Delivery;

require(__DIR__."/../vendor/autoload.php");

$config = new Callback('Webhook Shared Secret',function( ) {
   $line = exec('cd /path/to/my/repo && git pull 2>&1',$out,$ret);
   if ($ret!=0) http_response_code(500);
   echo implode("\n",$out)."\n";
});

$delivery = new Delivery(
      file_get_contents('php://input'), //messageBody
      $_SERVER["CONTENT_TYPE"], //contentType
      $_SERVER["HTTP_X_GITHUB_EVENT"], //gitHubEvent
      $_SERVER["HTTP_X_HUB_SIGNATURE"], //hubSignature
      $_SERVER["HTTP_X_GITHUB_DELIVERY"], //gitHubDelivery
      $_SERVER["REQUEST_METHOD"], //requestMethod
      $_SERVER["HTTP_USER_AGENT"] //userAgent
);

$config->validateRequest($delivery->getHubSignature(), $delivery->getMessageBody(), $delivery->getPayload());

