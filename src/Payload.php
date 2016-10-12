<?php
namespace Webhook;

use \Webhook\PayloadData\Sender;
use \Webhook\PayloadData\Organization;
use \Webhook\PayloadData\Repository;

abstract class Payload implements Populatable, PopulateListener {
   
   abstract public function getEvent():string;
   
   /**
    * @var \Webhook\PayloadData\Sender
    */
   public $sender;
   
   /**
    * @var \Webhook\PayloadData\Organization
    */
   public $organization;
   
   /**
    * @var \Webhook\PayloadData\Repository
    */
   public $repository;
   
   use PopulatorTrait;
   
   public function populateComplete(): void {
      
      if (!$this->sender instanceof Sender) {
         $this->sender = (new Sender)->populateFromObject($this->sender);
      }
      
      if (!$this->organization instanceof Organization) {
         $this->organization = (new Organization)->populateFromObject($this->organization);
      }
      
      if (!$this->repository instanceof Repository) {
         $this->repository = (new Repository)->populateFromObject($this->repository);
      }
      
   }
   
   public function __construct(object $input) {
      $this->populateFromObject($input);
   }
   
}