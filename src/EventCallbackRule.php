<?php
namespace Webhook;

class EventCallbackRule extends CallbackRule {
   /**
    * @param string $event name of the github event
    */
   public function __construct(string $event) {
      $this->_ruleValue = $event;
   }
}