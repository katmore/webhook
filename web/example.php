<?php
use Webhook\Callback;
use Webhook\Request;
use Webhook\InvalidRequest;
use Webhook\Payload;
use Webhook\UrlCallbackRule;

//ini_set('display_errors',1);

require __DIR__."/../vendor/autoload.php";

$config['Secret'] = 'My Secret';
$config['RepoPath'] = '/path/to/my/repo';
$config['RepoUrl'] = 'https://example.com/my-org/my-repo';
$config['RepoType'] = 'git'; //'git' or 'svn' (works in the callback below)

$callback = new Callback($config['Secret'],function(Payload $payload ) use (&$config) {
   
   if ($payload instanceof Payload\PushEvent) {
      
      if ($config['RepoType']=='git') {
         
         $line = exec('cd '.$config['RepoPath'].' && git pull 2>&1',$out,$ret);
      
      } else if ($config['RepoType']=='svn') {
         
         $line = exec('svn up '.$config['RepoPath'].' 2>&1',$out,$ret);
      
      }
      
      header('Content-Type:text/plain');
      if ($ret!=0) http_response_code(500);
      
      echo implode("\n",$out)."\n";
      
   } elseif ($payload instanceof Payload\PingEvent) {
      
      if ($config['RepoType']=='git') {
          
         $line = exec('cd '.$config['RepoPath'].' && git status 2>&1',$out,$ret);
      
      } else if ($config['RepoType']=='svn') {
          
         $line = exec('svn info '.$config['RepoPath'].' 2>&1',$out,$ret);
      
      }
      
      header('Content-Type:text/plain');
      if ($ret!=0) http_response_code(500);
      
      echo implode("\n",$out)."\n";
      
   }
},new UrlCallbackRule($config['RepoUrl']));

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
   if ($request->getRequestMethod()!=='POST') {
      throw new InvalidRequest("requestMethod must be POST");
   }
   $callback->validateRequest($request->getHubSignature(), $request->getMessageBody(), $request->getPayload());
} catch(InvalidRequest $e) {
   http_response_code(500);
   echo "Invalid Request: ".$e->getMessage();
}

