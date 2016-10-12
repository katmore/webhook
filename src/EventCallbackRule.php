<?php
namespace Webhook;

class EventCallbackRule extends CallbackRule {
   public function __construct(string $event) {
      $this->_ruleValue = $event;
   }
}