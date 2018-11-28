<?php
/**
 * webservice endpoint that updates a git or svn repo on the local system in response to a 'push' event Github webhook.
 * 
 */

/*
 * string $config['RepoUrl']
 *    GitHub repository URL: i.e: https://api.github.example.com/repos/my-org/my-repo
 */
$config['RepoUrl'] = 'https://api.github.example.com/repos/my-org/my-repo';

/*
 * string $config['Secret']
 *    The "Secret" configured in Github for the webhook.
 */
$config['Secret'] = 'My Secret';

/*
 * string $config['RepoPath']
 *    The local system path to the repository
 */
$config['RepoPath'] = '/path/to/my/repo';

/*
 * string $config['RepoType']
 *    The type of local system repository, this end-point can handle either 'git' or 'svn'.
 */
$config['RepoType'] = 'git';

use Webhook\Callback;
use Webhook\Request;
use Webhook\InvalidRequest;
use Webhook\Payload;
use Webhook\UrlCallbackRule;

require __DIR__."/../vendor/autoload.php";

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
      unset($out);
      
   } elseif ($payload instanceof Payload\PingEvent) {
      
      if ($config['RepoType']=='git') {
          
         $line = exec('cd '.$config['RepoPath'].' && git status 2>&1',$out,$ret);
      
      } else if ($config['RepoType']=='svn') {
          
         $line = exec('svn info '.$config['RepoPath'].' 2>&1',$out,$ret);
      
      }
      
      header('Content-Type:text/plain');
      if ($ret!=0) http_response_code(500);
      
      echo implode("\n",$out)."\n";
      unset($out);
      
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
   $request = Request::service(
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

