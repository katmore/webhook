<?php
namespace Webhook;

class InvalidRequestException extends \Exception {
   
   const REASON_CODE_SIGNATURE_INVALID = 100;
   const REASON_CODE_MISSING_EVENT = 101;
   const REASON_CODE_MISSING_SIGNATURE = 102;
   const REASON_CODE_MESSAGE_BODY_INVALID = 103;
   
   /**
    * Provides the reason the request was invalid.
    * @return string reason
    */
   public function getReason(): string {
      return $this->_reason;
   }
   /**
    * @var string reason the request was invalid
    * @private
    */
   private $_reason;
   /**
    * @param string $reason reason the request was invalid
    * @param int $reason_code reason code
    */
   public function __construct(string $reason="unknown error",int $reason_code=0) {
      $this->_reason = $reason;
      parent::__construct("Invalid Request: $reason",$reason_code);
   }
}