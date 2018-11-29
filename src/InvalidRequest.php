<?php
namespace Webhook;

class InvalidRequest extends \Exception {
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
    * @param string reason the request was invalid
    */
   public function __construct(string $reason) {
      $this->_reason = $reason;
      parent::__construct($reason);
   }
}