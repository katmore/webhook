<?php
namespace Webhook;

class Response {
   
   /**
    * @var int statusCode
    */
   private $_statusCode;
   
   /**
    * @var string messageBody
    */
   private $_messageBody;
   
   public function getStatusCode(): int {
      return $this->_statusCode;
   }
   
   public function getMessageBody(): string {
      return $this->_messageBody;
   }
   
   public function __construct(string $messageBody, int $statusCode=200) {
      $this->_messageBody = $messageBody;
      $this->_statusCode = $statusCode;
   }
   
}