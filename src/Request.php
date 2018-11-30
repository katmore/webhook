<?php
namespace Webhook;

class Request {
   
   /**
    * @var string The raw payload of this delivery.
    * @private
    */
   private $_messageBody;
   
   /**
    * @var string The content-Type of the payload.
    * @private
    */
   private $_contentType;
   
   /**
    * @var string GitHub Event Type of this delivery.
    * @private
    */
   private $_gitHubEvent;
   
   /**
    * @var string The Hub-Signature provided along with this delivery.
    * @private
    */
   private $_hubSignature;
   
   /**
    * @var string The UUID of this delivery.
    * @private
    */
   private $_gitHubDelivery;
   
   /**
    * @var string The request method of this delivery.
    * @private
    */
   private $_requestMethod;
   
   /**
    * @var string The user-agent reported for this delivery.
    * @private
    */
   private $_userAgent;
   
   /**
    * @var \Webhook\Payload
    * @private
    */
   private $_payload;
   
   /**
    * Service parameter key names.
    * @see \Webhook\Request::service()
    */
   const SERVICE_PARAMS = [
      'HTTP_X_HUB_SIGNATURE',
      'HTTP_X_GITHUB_EVENT',
      'CONTENT_TYPE',
      'HTTP_X_GITHUB_DELIVERY',
      'REQUEST_METHOD',
      'HTTP_USER_AGENT',
   ];
   
   /**
    * Provides a webhook request object from specified paramaters.
    *    Suitable for use in conjunction with the $_SERVER superglobal.
    *
    * @param string $messageBody request message body
    * @param array $param assoc array of paramaters:<ul>
    *    <li><b>string $param['HTTP_X_HUB_SIGNATURE']</b> HTTP_X_HUB_SIGNATURE.</li>
    *    <li><b>string $param['HTTP_X_GITHUB_EVENT']</b> HTTP_X_GITHUB_EVENT.</li>
    *    <li><b>string $param['CONTENT_TYPE']</b> CONTENT_TYPE. Optional.</li>
    *    <li><b>string $param['HTTP_X_GITHUB_DELIVERY']</b> HTTP_X_GITHUB_DELIVERY.</li>
    *    <li><b>string $param['REQUEST_METHOD']</b> REQUEST_METHOD.</li>
    *    <li><b>string $param['HTTP_USER_AGENT']</b> HTTP_USER_AGENT.</li>
    * </ul>
    *
    * @return \Webhook\Request request object
    * 
    * @see $_SERVER
    */
   public static function service(string $messageBody,array $param=null) {
      $request = array_fill_keys(static::SERVICE_PARAMS, '');
      if (!empty($param)) {
         foreach($request as $k=>&$v) {
            if (isset($param[$k])) $v=$param[$k];
         }
      }
      return new static(
            $messageBody,$request['HTTP_X_HUB_SIGNATURE'],$request['HTTP_X_GITHUB_EVENT'],
            $request['CONTENT_TYPE'],$request['HTTP_X_GITHUB_DELIVERY'],
            $request['REQUEST_METHOD'],$request['HTTP_USER_AGENT']
            );
   }
   
   /**
    * Determines if a secret matches a signature and message body.
    *
    * @param string $hub_secret Secret string known by the webhoook provider.
    * @param string $hub_signature Hub-Signature value specified by the request.
    * @param string $message_body Raw request payload.
    *
    * @return bool true if signature is valid, <b>bool</b> false otherwise
    */
   final public static function isValidSignature(string $hub_secret, string $hub_signature, string $message_body) : bool {
      list($algo, $hash) = explode('=', $hub_signature, 2) + ['', ''];
      if ($hash !== hash_hmac($algo, $message_body, $hub_secret)) {
         return false;
      }
      return true;
   }
   
   /**
    * Enforces that a secret matches this request's signature and message body.
    *
    * @param string $hub_secret Secret string known by the webhoook provider.
    *
    * @return void
    * @throws \Webhook\SignatureInvalidException
    */
   public function validateSignature(string $hub_secret) {
      if (!self::isValidSignature($hub_secret, $this->_hubSignature, $this->_messageBody)) {
         throw new SignatureInvalidException;
      }
   }
   
   /**
    * @param string $messageBody request body; the string value of the payload
    * @param string $hubSignature value of the 'X-Hub-Signature' header
    * @param string $gitHubEvent github event that triggered this request (i.e. "push" or "ping")
    * @param string $contentType Optionally specify mime type of the request body (i.e. "application/json")
    * @param string $gitHubDelivery Optionally specify value of the 'X-GitHub-Delivery' header
    * @param string $requestMethod Optionally specify request method (i.e. "POST")
    * @param string $userAgent Optionally specify value of the 'User-Agent' header 
    * 
    * @throws \Webhook\SignatureMissingException
    * @throws \Webhook\EventMissingException
    */
   public function __construct(
         string $messageBody,string $hubSignature,string $gitHubEvent,
         string $contentType='',string $gitHubDelivery='',
         string $requestMethod='',string $userAgent=''
         ) 
   {
      $this->_messageBody = $messageBody;
      $this->_contentType = $contentType;
      $this->_gitHubEvent = $gitHubEvent;
      $this->_hubSignature = $hubSignature;
      $this->_gitHubDelivery = $gitHubDelivery;
      $this->_requestMethod = $requestMethod;
      $this->_userAgent = $userAgent;
      
      $bodyObject = null;
      
      if (empty($this->_contentType)) {
         if ((null!==($bodyObject = json_decode($this->_messageBody))) && is_object($bodyObject)) {
            $this->_contentType = 'application/json';
         }
      }
      
      if ($bodyObject===null) {
         if ($this->_contentType === 'application/json') {
            $bodyObject = json_decode($this->_messageBody);
         }
      }
      
      if (empty($bodyObject) || !is_object($bodyObject)) {
         throw new MessageBodyInvalidException;
      }
      
      if (empty($this->_hubSignature)) {
         throw new SignatureMissingException;
      }
      
      $payload = null;
      $eventSubns = "";
      if (!empty($this->_gitHubEvent)) {
         $eventSubns = str_replace(" ","",ucwords(str_replace("_"," ",$this->_gitHubEvent)))."Event";
         $payload = __NAMESPACE__ . '\Payload\\'.$eventSubns;
         if (class_exists($payload)) {
            $this->_payload = new $payload($bodyObject);
         } else {
            $this->_payload = new Payload\Event($bodyObject, $this->_gitHubEvent);
         }
      } else {
         throw new EventMissingException;
      }
   }
   
   /**
    * Provides the payload object of this delivery.
    * 
    * @return \Webhook\Payload payload object
    */
   public function getPayload(): Payload {  
      
      return $this->_payload;
   }
   
   /**
    * Provides the raw message body of this delivery.
    * @return string message body
    */
   public function getMessageBody(): string {
      
      return $this->_messageBody;
      
   }
   
   /**
    * Provides the content-Type of the payload.
    * @return string content-Type
    */
   public function getContentType(): string {
      
      return $this->_contentType;
      
   }
   
   /**
    * Provides the GitHub Event Type of this delivery.
    * @return string GitHub Event Type
    */
   public function getGitHubEvent(): string {
      
      return $this->_gitHubEvent;
      
   }
   
   /**
    * Provides the Hub-Signature provided along with this delivery.
    * @return string Hub-Signature
    */
   public function getHubSignature(): string {
   
      return $this->_hubSignature;
      
   }
   
   /**
    * Provides the UUID of this delivery.
    * @return string UUID
    */
   public function getGitHubDelivery(): string {
      
      return $this->_gitHubDelivery;
      
   }
   
   /**
    * Provides the request method of this delivery.
    * @return string request method
    */
   public function getRequestMethod(): string {
      
      return $this->_requestMethod;
      
   }
   
   /**
    * Provides the user-agent reported for this delivery.
    * @return string user-agent
    */
   public function getUserAgent(): string {
      
      return $this->_userAgent;
      
   }
   
}