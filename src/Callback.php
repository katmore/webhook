<?php
namespace Webhook;

class Callback {
   
   /**
    * Invokes the previously configured callback if the Hub-Signature corresponds to the payload and Hub-Secret. 
    * 
    * @param string $hubSignature Hub-Signature value specified by the request.
    * @param string $rawPayload raw request payload.
    * @param \Webhook\Payload $payload Payload object.
    * 
    * @return boolean
    */
   public function validateRequest(string $hubSignature,string $rawPayload, Payload $payload) {
      list($algo, $hash) = explode('=', $hubSignature, 2) + ['', ''];
      if ($hash !== hash_hmac($algo, $rawPayload, $this->_hubSecret)) {
         return new InvalidRequest("secret validation failed");
      }
      
      
      if (count($this->_urlRule)) {
         $found_urlRule_match=false;
         foreach($this->_urlRule as $url) {
            if ($payload->repository->url===$url) {
               $found_urlRule_match = true;
               break 1;
            }
            if ($payload->repository->html_url===$url) {
               $found_urlRule_match = true;
               break 1;
            }
            if ($payload->repository->git_url===$url) {
               $found_urlRule_match = true;
               break 1;
            }
            if ($payload->repository->ssh_url===$url) {
               $found_urlRule_match = true;
               break 1;
            }
            if ($payload->repository->clone_url===$url) {
               $found_urlRule_match = true;
               break 1;
            }
            if ($payload->repository->svn_url===$url) {
               $found_urlRule_match = true;
               break 1;
            }
         }
         if (!$found_urlRule_match) throw new InvalidRequest("failed to find a match for the payload's repository URL");
      }
      
      if (count($this->_eventRule)) {
         $found_eventRule_match=false;
         foreach($this->_eventRule as $event) {
            if ($payload->getEvent()===$event) {
               $found_eventRule_match = true;
               break 1;
            }
         }
         if (!$found_eventRule_match) throw new InvalidRequest("failed to find a match for the payload's GitHub-Event type");
      }
      
      
      call_user_func_array($this->_callback,[$payload]);
   }
   
   /**
    * @var string
    *    Secret string known by the webhoook provider.
    */
   private $_hubSecret;
   
   /**
    * @var callable
    *    callback invoked when Hub-Signature has is validated to secret
    */
   private $_callback;
   
   /**
    * @var string[]
    *    Optional GitHub-Event type criterion for validation.
    */
   private $_eventRule=[];
   
   /**
    * @var string[]
    *    Optional repo URL criterion for validation.
    */
   private $_urlRule=[];
   
   /**
    * 
    * @param string $hubSecret Secret string known by the webhoook provider.
    * @param callable $callback function to invoke when a Hub-Signature is validated.
    *    Callback signature: function( \Webhook\Payload $payload) {}
    * @param CallbackRule ...$CallbackRule Optional. If specified, at least one of these rules must be satisfied
    *    in order for a request to be validated.
    */
   public function __construct(string $hubSecret,callable $callback,CallbackRule ...$CallbackRule) {
      $this->_hubSecret = $hubSecret;
      $this->_callback = $callback;
      foreach($CallbackRule as $v) {
         if ($v instanceof UrlCallbackRule) {
            $this->_urlRule []= (string) $v;
         }
         if ($v instanceof EventCallbackRule) {
            $this->_eventRule []= (string) $v;
         }
      }
   }
   
}










