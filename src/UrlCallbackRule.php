<?php
namespace Webhook;

class UrlCallbackRule extends CallbackRule {
   public function __construct(string $url) {
      $this->_ruleValue = $url;
   }
}