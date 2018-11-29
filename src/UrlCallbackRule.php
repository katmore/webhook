<?php
namespace Webhook;

class UrlCallbackRule extends CallbackRule {
   /**
    * @param string $url url
    */
   public function __construct(string $url) {
      $this->_ruleValue = $url;
   }
}