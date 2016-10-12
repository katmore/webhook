<?php
use Webhook\Callback;
use Webhook\Delivery;
use Webhook\EventCallbackRule;
use Webhook\UrlCallbackRule;
use Webhook\Response;

return new class() {
   
   public static function callbacksConfig() {
      return [
         new Callback('My Secret',function( ) {
            $line = exec('svn up /path/to/my/repo 2>&1',$out,$ret);
            if ($ret==0) {
               $statusCode = 200;
            } else {
               $statusCode = 500;
            }
            return new Response(implode("\n",$out)."\n",$statusCode);
         },new EventCallbackRule('push'),new UrlCallbackRule('https://example.com/my-org/my-repo')),
      ];
   }
   
   public function __construct() {
      //ini_set('display_errors',1);
      //ini_set('html_errors', false);
      header('Content-Type:text/plain');
   
      require(__DIR__."/../vendor/autoload.php");
      
      set_exception_handler(function(\Throwable  $e) {
         http_response_code(500);
         $trace=[];
         foreach($e->getTrace() as $t) {
            $item=[];
            foreach($t as $k=>$v) {
               if ($k!="args") $item[$k]=$v;
            }
            if (!empty($item['class']) && (false !== ( strpos($item['class'], get_called_class()) ))) {
               continue;
            }
            $trace[]=$item;
         }
         
         $edata = [
            'Response'=>['status_code'=>500,'description'=>'Internal Server Error'],
            'Type'=>'Unhandled Exception',
            'Class'=>get_class($e),
            'Message'=>$e->getMessage(),
            'Code'=>$e->getCode(),
            'File'=>$e->getFile(),
            'Line'=>$e->getLine(),
            'Stack Trace'=>$trace,
         ];
         echo json_encode($edata,\JSON_PRETTY_PRINT);
      });
      
      set_error_handler(function($errno,$errstr,$errfile,$errline) {
         http_response_code(500);
         $edata = [
            'Response'=>['status_code'=>500,'description'=>'Internal Server Error'],
            'Type'=>'php error',
            'Errno'=>$errno,
            'Errstr'=>$errstr,
            'File'=>$errfile,
            'Line'=>$errline,
            'Stack Trace'=>debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
         ];
         echo json_encode($edata,\JSON_PRETTY_PRINT);
      },error_reporting());
   
      $server = [];
      if (!empty($_SERVER)) $server = $_SERVER;
   
      $delivery = new Delivery(
            file_get_contents('php://input'), //messageBody
            (!empty($_SERVER["CONTENT_TYPE"]))?$_SERVER["CONTENT_TYPE"]:"", //contentType
            (!empty($_SERVER["HTTP_X_GITHUB_EVENT"]))?$_SERVER["HTTP_X_GITHUB_EVENT"]:"", //gitHubEvent
            (!empty($_SERVER["HTTP_X_HUB_SIGNATURE"]))?$_SERVER["HTTP_X_HUB_SIGNATURE"]:"", //hubSignature
            (!empty($_SERVER["HTTP_X_GITHUB_DELIVERY"]))?$_SERVER["HTTP_X_GITHUB_DELIVERY"]:"", //gitHubDelivery
            (!empty($_SERVER["REQUEST_METHOD"]))?$_SERVER["REQUEST_METHOD"]:"", //requestMethod
            (!empty($_SERVER["HTTP_USER_AGENT"]))?$_SERVER["HTTP_USER_AGENT"]:""//userAgent
            );
   
      foreach(self::callbacksConfig() as $callback) {
         if ($callback instanceof Callback) {
   
            $callback->validateRequest($delivery->getHubSignature(), $delivery->getMessageBody(), $delivery->getPayload());
   
         }
      }
   }
};

