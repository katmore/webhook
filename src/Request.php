<?php
namespace Webhook;

class Request {
   
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
   
   const CONTENT_TYPES = [
      'application/x-www-form-urlencoded',
      'application/json',
   ];
   
   /**
    * @var string
    *    The raw payload of this delivery.
    */
   private $_messageBody;
   
   /**
    * @var string
    *    The content-Type of the payload.
    */
   private $_contentType;
   
   /**
    * @var string GitHub Event Type of this delivery.
    */
   private $_gitHubEvent;
   
   /**
    * @var string
    *    The Hub-Signature provided along with this delivery.
    */
   private $_hubSignature;
   
   /**
    * @var string
    *    The UUID of this delivery.
    */
   private $_gitHubDelivery;
   
   /**
    * @var string
    *    The request method of this delivery.
    */
   private $_requestMethod;
   
   /**
    * @var string
    *    The user-agent reported for this delivery.
    */
   private $_userAgent;
   
   /**
    * @var \Webhook\Payload
    */
   private $_payload;
   
   /**
    * Provides a webhook request object from specified paramaters.
    *    Suitable for use in conjunction with the $_SERVER array.
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
    * @return \Webhook\Request
    */
   public static function service(string $messageBody,array $param=null) {
      $request = array_fill_keys(static::SERVICE_PARAMS, '');
      if (!empty($param)) {
         foreach($request as $k=>&$v) {
            if (isset($param[$k])) $v=$param[$k];
         }
         unset($k);
         unset($v);
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
    * @throws \Webhook\InvalidRequest
    */
   public function validateSignature(string $hub_secret) {
      if (!self::isValidSignature($hub_secret, $this->_hubSignature, $this->_messageBody)) {
         throw new SignatureInvalidException();
      }
   }
   
   /**
    * @param string $messageBody
    * @param string $hubSignature
    * @param string $gitHubEvent
    * @param string $contentType Optional.
    * @param string $gitHubDelivery Optional.
    * @param string $requestMethod Optional.
    * @param string $userAgent Optional.    
    * 
    * @throws \Webhook\InvalidRequest if invalid value specified for hubSignature, or gitHubEvent;  
    */
   public function __construct(
         string $messageBody,string $hubSignature,string $gitHubEvent,
         string $contentType='',string $gitHubDelivery='',
         string $requestMethod='',string $userAgent=''
         ) 
   {
      
      if (empty($hubSignature)) {
         throw new SignatureMissingException();
      }
      
      if (empty($gitHubEvent)) {
         throw new EventMissingException();
      }
      
      $this->_messageBody = $messageBody;
      $this->_contentType = $contentType;
      $this->_gitHubEvent = $gitHubEvent;
      $this->_hubSignature = $hubSignature;
      $this->_gitHubDelivery = $gitHubDelivery;
      $this->_requestMethod = $requestMethod;
      $this->_userAgent = $userAgent;
      
      if (empty($this->_contentType)) {
         
         $this->_contentType = (new \finfo(\FILEINFO_MIME_TYPE))->buffer($this->_messageBody);
         
         if (!in_array($this->_contentType,static::CONTENT_TYPES,true)) {
            if (null!==json_decode($this->_messageBody)) {
               $this->_contentType = "application/json";
            } else {
               throw new MessageBodyInvalidException();
            }
         }
      } else {
         if (!in_array($this->_contentType,static::CONTENT_TYPES,true)) {
            throw new MessageBodyInvalidException();
         }
      }
      
      $bodyObject = null;
      if ($this->_contentType == 'application/json') {
         $bodyObject = json_decode($this->_messageBody);
         if (!is_object($bodyObject)) {
            throw new MessageBodyInvalidException();
         }
      } elseif ($this->_contentType == 'application/x-www-form-urlencoded') {
         parse_str($this->_messageBody,$bodyObject);
      }
      
      if (empty($bodyObject)) {
         throw new MessageBodyInvalidException();
      }
      
      $eventSubns = str_replace(" ","",ucwords(str_replace("_"," ",$this->_gitHubEvent)))."Event";
      $payload = __NAMESPACE__ . '\Payload\\'.$eventSubns;
      if (class_exists($payload)) {
         $this->_payload = new $payload($bodyObject);
      } else {
         $this->_payload = new Payload\Event($bodyObject, $this->_gitHubEvent);
      }
   }
   
   /**
    * @return \Webhook\Payload
    */
   public function getPayload(): Payload {  
      
      return $this->_payload;
   }
   
   /**
    * @return string The raw payload of this delivery.
    */
   public function getMessageBody(): string {
      
      return $this->_messageBody;
      
   }
   
   /**
    * @return string
    *    The content-Type of the payload.
    */
   public function getContentType(): string {
      
      return $this->_contentType;
      
   }
   
   /**
    * @return string GitHub Event Type of this delivery.
    */
   public function getGitHubEvent(): string {
      
      return $this->_gitHubEvent;
      
   }
   
   /**
    * @return string
    *    The Hub-Signature provided along with this delivery.
    */
   public function getHubSignature(): string {
   
      return $this->_hubSignature;
      
   }
   
   /**
    * @return string
    *    The UUID of this delivery.
    */
   public function getGitHubDelivery(): string {
      
      return $this->_gitHubDelivery;
      
   }
   
   /**
    * @return string
    *    The request method of this delivery.
    */
   public function getRequestMethod(): string {
      
      return $this->_requestMethod;
      
   }
   
   /**
    * @return string
    *    The user-agent reported for this delivery.
    */
   public function getUserAgent(): string {
      
      return $this->_userAgent;
      
   }
   
}