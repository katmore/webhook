<?php
namespace Webhook;

use Webhook\PayloadData\Sender;

abstract class Payload implements Populatable, PopulateListener {
   
   /**
    * Provides the event name.
    * @return string
    */
   abstract public function getEvent():string;
   
   /**
    * @var \Webhook\PayloadData\Sender
    */
   public $sender;
   
   use PopulatorTrait;
   
   /**
    * Indicates that the populating of this Payload object is complete.
    * @return void
    */
   public function populateComplete() {
      
      if (!$this->sender instanceof Sender) {
         $this->sender = (new Sender)->populateFromObject($this->sender);
      }
      
   }
   
   /**
    * @param object $input payload input
    */
   public function __construct($input) {
      $this->populateFromObject($input);
   }
   
}