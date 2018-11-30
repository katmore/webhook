<?php
/**
 * webservice endpoint that updates a git or svn repo on the local system in response to a 'push' event Github webhook.
 * 
 */
use Webhook\Callback;
use Webhook\Request;
use Webhook\InvalidRequestException;
use Webhook\Payload;
use Webhook\UrlCallbackRule;

if (is_file(__DIR__."/../vendor/autoload.php")) {
   require __DIR__."/../vendor/autoload.php";
}

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


function onPushEvent(Payload\PushEvent $payload) {
   /*
    *
    * --- place code in this function ---
    * --- that should execute when a Github 'push' event occurs ---
    *
    */
}

function onPingEvent(Payload\PingEvent $payload) {
   /*
    *
    * --- place code in this function --
    * --- that should execute when a Github 'ping' event occurs ---
    *
    */
}

function onOtherEvent(Payload\Event $payload) {
   /*
    *
    * --- place code in this function ---
    * --- that should execute when a Github event occurs other than 'ping' or 'push' ---
    *
    */
}

/*
 * string $config['RepoPath']
 *    The local system path to the repository.
 *    A null or empty value will skip the repo update.
 */
$config['RepoPath'] = '';
//$config['RepoPath'] = '/path/to/my/git/repo';

header('Content-Type: text/plain');

$callback = new Callback($config['Secret'],function(Payload $payload ) use (&$config) {
   
   /*
    * "push" event
    */
   if ($payload instanceof Payload\PushEvent) {
      
      $ret = onPushEvent($payload);
      if ($ret===false) {
         http_response_code(500);
      }
      
      /*
       * do a "git pull" on the local system copy of the repo
       */
      if (!empty($config['RepoPath'])) {
      
         if (is_dir($config['RepoPath'])) {
            exec('cd '.$config['RepoPath'].' && git pull 2>&1',$output,$exit_status);
         
            if ($exit_status!=0) http_response_code(500);
         
            echo implode("\n",$output)."\n\n";
         
            if ($exit_status!=0) echo "exit status: $exit_status\n";
         } else {
            echo "skipped 'git pull' because the path was invalid, see \$config['RepoPath']\n";
         }
         
      }
      
      echo "event: ".$payload->getEvent()."\n";
      
      echo "sender login: ".$payload->sender->login."\n";
      echo "sender avatar_url: ".$payload->sender->avatar_url."\n";
      
      echo "pusher name: ".$payload->pusher->name."\n";
      echo "pusher email: ".$payload->pusher->email."\n";
      
      return;
      
   }
   
   /*
    * "ping" event
    */
   if ($payload instanceof Payload\PingEvent) {
      
      $ret = onPingEvent($payload);
      if ($ret===false) {
         http_response_code(500);
      }
      
      /*
       * do a "git status" on the local system copy of the repo
       */
      if (!empty($config['RepoPath'])) {
      
         if (is_dir($config['RepoPath'])) {
            exec('cd '.$config['RepoPath'].' && git status 2>&1',$output,$exit_status);
            
            if ($exit_status!=0) http_response_code(500);
            
            echo implode("\n",$output)."\n\n";
            
            if ($exit_status!=0) echo "exit status: $exit_status\n";
         } else {
            echo "skipped 'git status' because the path was invalid, see \$config['RepoPath']\n";
         }
      
      }
      
      echo "event: ".$payload->getEvent()."\n";
      echo "sender login: ".$payload->sender->login."\n";
      echo "sender avatar_url: ".$payload->sender->avatar_url."\n";
      
      echo "zen: ".$payload->zen;
      
      return;
      
   }
   
   $ret = onOtherEvent($payload);
   if ($ret===false) {
      http_response_code(500);
   }
   
   echo "event: ".$payload->getEvent()."\n";
   echo "sender login: ".$payload->sender->login."\n";
   echo "sender avatar_url: ".$payload->sender->avatar_url."\n";
   
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
   $request = Request::service(file_get_contents('php://input'),isset($_SERVER)?$_SERVER:[]);
   if ($request->getRequestMethod()!=='POST') {
      throw new InvalidRequestException("requestMethod must be POST");
   }
   $callback->validateRequest($request->getHubSignature(), $request->getMessageBody(), $request->getPayload());
} catch(InvalidRequestException $e) {
   http_response_code(500);
   echo $e->getMessage();
}

