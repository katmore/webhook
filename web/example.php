<?php
use Webhook\Callback;
use Webhook\Request;
use Webhook\InvalidRequest;
use Webhook\Payload;

//ini_set('display_errors',1);

require __DIR__."/../vendor/autoload.php";

$config['Secret'] = 'My Secret';
$config['RepoPath'] = '/path/to/my/repo';

$callback = new Callback($config['Secret'],function(Payload $payload ) use (&$config) {
   
   header('Content-Type:text/plain');
   
   if ($payload instanceof Payload\PushEvent) {
      
      $line = exec('cd '.$config['RepoPath'].' && git pull 2>&1',$out,$ret);
      
      //$line = exec('svn up '.$config['RepoPath'].' 2>&1',$out,$ret);
      
      if ($ret!=0) http_response_code(500);
      
      echo implode("\n",$out)."\n";
      
   } elseif ($payload instanceof Payload\PingEvent) {
      
      echo json_encode($payload,\JSON_PRETTY_PRINT);
      
   }
});

register_shutdown_function(function() {
   $last_error = error_get_last();
   if ($last_error && isset($last_error['type'])) {
      if (!in_array($last_error['type'],[E_DEPRECATED,E_WARNING,E_NOTICE,E_RECOVERABLE_ERROR,E_USER_DEPRECATED,E_USER_NOTICE,E_USER_WARNING])) {
         http_response_code (500);
      }
   }
});

try {
   $request = Request::load(
         file_get_contents('php://input'),
         isset($_SERVER)?$_SERVER:[]
         );

   $callback->validateRequest($request->getHubSignature(), $request->getMessageBody(), $request->getPayload());
} catch(InvalidRequest $e) {
   http_response_code(500);
   echo "Invalid Request: ".$e->getMessage();
}

