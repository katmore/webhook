<?php
namespace Webhook;

class InvalidDelivery extends \Exception {
   public function getReason() {
      return $this->_reason;
   }
   private $_reason;
   public function __construct($reason) {
      $this->_reason = $reason;
      parent::__construct($reason);
   }
}