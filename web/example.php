<?php
use Webhook\Callback;
use Webhook\Request;
use Webhook\InvalidRequest;

//ini_set('display_errors',1);

require __DIR__."/../vendor/autoload.php";

$config['Secret'] = 'My Secret';
$config['RepoPath'] = '/path/to/my/repo';

header('Content-Type:text/plain');

$callback = new Callback($config['Secret'],function( ) use (&$config) {
   
   $line = exec('cd '.$config['RepoPath'].' && git pull 2>&1',$out,$ret);
   
   if ($ret!=0) http_response_code(500);
   
   echo implode("\n",$out)."\n";
   
   return !$ret;
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

