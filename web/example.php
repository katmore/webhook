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
   
   if ($payload->getEvent()=='push') {
      
      $line = exec('cd '.$config['RepoPath'].' && git pull 2>&1',$out,$ret);
      
      //$line = exec('svn up '.$config['RepoPath'].' 2>&1',$out,$ret);
      
      if ($ret!=0) http_response_code(500);
      
      echo implode("\n",$out)."\n";
      
      return !$ret;
      
   } elseif ($payload->getEvent()=='ping') { 
      
      
      
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

