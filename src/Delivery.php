<?php
namespace Webhook;

use Webhook\Payload;

class Delivery {
   
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
   
   public function __construct(
         string $messageBody,string $contentType,string $gitHubEvent,
         string $hubSignature,string $gitHubDelivery,string $requestMethod,
         string $userAgent) 
   {
      $this->_messageBody = $messageBody;
      $this->_contentType = $contentType;
      $this->_gitHubEvent = $gitHubEvent;
      $this->_hubSignature = $hubSignature;
      $this->_gitHubDelivery = $gitHubDelivery;
      $this->_requestMethod = $requestMethod;
      
      $bodyObject = null;
      if ($this->_contentType == 'application/json') {
         $bodyObject = json_decode($this->_messageBody);
      } elseif ($this->_contentType == 'application/x-www-form-urlencoded') {
         parse_str($this->_messageBody,$bodyObject);
      }
      
      $payload = null;
      $eventSubns = "";
      if (!empty($this->_gitHubEvent) && !empty($bodyObject)) {
         $eventSubns = ucwords($this->_gitHubEvent)."Event";
         $payload = __NAMESPACE__ . '\Payload\\'.$eventSubns;
         if (class_exists($payload)) {
            $this->_payload = new $payload($bodyObject);
         }
      } else {
         throw new InvalidDelivery("missing gitHubEvent");
      }
      
      if (empty($this->_payload)) {
         
         throw new InvalidDelivery("could not create payload object for $eventSubns");
         
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