<?php
namespace Webhook\Payload;

use Webhook\Payload;
use Webhook\PayloadData\Hook;

class PingEvent extends Payload {
   
   public function getEvent(): string {
      return "ping";
   }
   
   /**
    * @var string Random string of GitHub zen.
    */
   public $zen;
   
   /**
    * @var string The ID of the webhook that triggered the ping.
    */
   public $hook_id;
   
   /**
    * @var \Webhook\PayloadData\Hook The webhook configuration.
    */
   public $hook;
   
   public function populateComplete() {
      parent::populateComplete();
      
      if ((!$this->hook instanceof Hook) && is_object($this->hook)) {
         $this->hook = (new Hook)->populateFromObject($this->hook);
      }
   }
}