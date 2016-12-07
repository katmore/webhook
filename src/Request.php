<?php
namespace Webhook;

use Webhook\Payload;

class Request {
   
   /**
    * Loads a webhook request object from specified paramaters.
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
   public static function load(string $messageBody,array $param=null) {
      $request = [
         'HTTP_X_HUB_SIGNATURE'=>'',
         'HTTP_X_GITHUB_EVENT'=>'',
         'CONTENT_TYPE'=>'',
         'HTTP_X_GITHUB_DELIVERY'=>'',
         'REQUEST_METHOD'=>'',
         'HTTP_USER_AGENT'=>'',
      ];
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
      $this->_messageBody = $messageBody;
      $this->_contentType = $contentType;
      $this->_gitHubEvent = $gitHubEvent;
      $this->_hubSignature = $hubSignature;
      $this->_gitHubDelivery = $gitHubDelivery;
      $this->_requestMethod = $requestMethod;
      
      if (empty($this->_contentType)) {
         $this->_contentType = (new \finfo(\FILEINFO_MIME_TYPE))->buffer($this->_messageBody);
      }
      
      $bodyObject = null;
      if ($this->_contentType == 'application/json') {
         $bodyObject = json_decode($this->_messageBody);
      } elseif ($this->_contentType == 'application/x-www-form-urlencoded') {
         parse_str($this->_messageBody,$bodyObject);
      }
      
      if (empty($this->_hubSignature)) {
         throw new InvalidRequest("missing hubSignature");
      }
      
      $payload = null;
      $eventSubns = "";
      if (!empty($this->_gitHubEvent) && !empty($bodyObject)) {
         $eventSubns = str_replace(" ","",ucwords(str_replace("_"," ",$this->_gitHubEvent)))."Event";
         $payload = __NAMESPACE__ . '\Payload\\'.$eventSubns;
         if (class_exists($payload)) {
            $this->_payload = new $payload($bodyObject);
         } else {
            $this->_payload = new Payload\Event($bodyObject, $this->_gitHubEvent);
         }
      } else {
         throw new InvalidRequest("missing gitHubEvent");
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