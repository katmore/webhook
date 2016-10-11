<?php
namespace Webhook;

return new class() {
   CONST X_HUB_SECRET = "";
    
    
    
    
    
   CONST REPO_PATH = __DIR__."/../";
    




   public function __construct() {



      ini_set('display_errors','1');





      ini_set('html_errors', false);
      header('Content-Type:text/plain');


      register_shutdown_function(function() {
         $last_error = error_get_last();
         if ($last_error && isset($last_error['type'])) {
            if (!in_array($last_error['type'],[E_DEPRECATED,E_WARNING,E_NOTICE,E_RECOVERABLE_ERROR,E_USER_DEPRECATED,E_USER_NOTICE,E_USER_WARNING])) {
               http_response_code (500);
            }
         }
      });

         if (empty($_SERVER) || empty($_SERVER['REQUEST_METHOD']) || ($_SERVER['REQUEST_METHOD'] != 'POST')) {
            trigger_error("bad request",E_USER_ERROR);
         }

         if (empty($_SERVER['HTTP_X_HUB_SIGNATURE'])) {
            trigger_error("missing X-Hub-Signature",E_USER_ERROR);
         }
         list($algo, $hash) = explode('=', $_SERVER['HTTP_X_HUB_SIGNATURE'], 2) + array('', '');
         $rawPost = file_get_contents('php://input');
         if ($hash !== hash_hmac($algo, $rawPost, self::X_HUB_SECRET)) {
            trigger_error("bad secret",E_USER_ERROR);
         }
         $payload = json_decode($rawPost);





         /*
          *
          * webhook handling
          */
         $line = exec("svn up " .self::REPO_PATH . ' 2>&1',$out,$ret);




















         if ($ret!=0) {
            http_response_code (500);
         }
         echo implode("\n",$out)."\n";
         $out=[];
         if ($ret!=0) {
            return;
         }








         if (!empty($payload->head_commit) && is_object($payload->head_commit)) {
            if (!empty($payload->head_commit->modified) && is_array($payload->head_commit->modified)) {
               if (in_array('composer.json',$payload->head_commit->modified)) {
                  $line = exec("composer update 2>&1",$out,$ret);
                  if ($ret!=0) {
                     echo implode("\n",$out)."\n";
                     trigger_error("composer update command failed: returned status $ret",E_USER_ERROR);
                  }
               }
               if (in_array('bower.json',$payload->head_commit->modified)) {
                  $line = exec("cd ".self::REPO_PATH ."; bash -c 'bower install 2>&1'",$out,$ret);
                  if ($ret!=0) {
                     echo implode("\n",$out)."\n";
                     trigger_error("bower install command failed: returned status $ret",E_USER_ERROR);
                  }
               }
            }
         }












   }
};
