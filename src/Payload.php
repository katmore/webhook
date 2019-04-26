<?php
namespace Webhook;

use Webhook\PayloadData\Sender;
use Webhook\PayloadData\Organization;
use Webhook\PayloadData\Repository;

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
   
   /**
    * @var \Webhook\PayloadData\Repository
    */
   public $repository;
   
   use PopulatorTrait;
   
   /**
    * Indicate that the populating of this Payload object is complete.
    * @return void
    */
   public function populateComplete() {
      
      if (!$this->sender instanceof Sender) {
         $this->sender = (new Sender)->populateFromObject($this->sender);
      }
      
      if (!$this->repository instanceof Repository) {
         $this->repository = (new Repository)->populateFromObject($this->repository);
      }
      
   }
   
   /**
    * @param object $input payload input
    */
   public function __construct($input) {
      $this->populateFromObject($input);
   }
   
}