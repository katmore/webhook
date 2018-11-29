<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;
use Webhook\PopulateListener;

class Hook implements Populatable,PopulateListener  {
   
   /**
    * @var int The webhook's id.
    */
   public $id;
   
   /**
    * @var string The URL this webhook will perform a POST request on.
    */
   public $url;
   
   /**
    * @var string This webhook's name.
    */
   public $name;
   
   /**
    * @var string[] GitHub-Event Types this webhook is triggered by.
    */
   public $events;
   
   /**
    * @var bool true if this webhook is active, <b>bool</b> false otherwise
    */
   public $active;
   
   /**
    * @var \Webhook\PayloadData\HookConfig hook config object
    */
   public $config;
   
   /**
    * @var string ISO 8601 timestamp when this webhook was updated.
    */
   public $updated_at;
   
   /**
    * @var string ISO 8601 timestamp when this webhook was created.
    */
   public $created_at;
   
   /**
    * @var \Webhook\PayloadData\LastResponse last response object
    */
   public $last_response;
   
   use PopulatorTrait;
   
   /**
    * Indicates that the populating of this object is complete.
    * @return void
    */
   public function populateComplete() {
      
      if ((!$this->config instanceof HookConfig) && is_object($this->config)) {
         $this->config = (new HookConfig)->populateFromObject($this->config);
      }
      
      if ((!$this->last_response instanceof LastResponse) && is_object($this->last_response)) {
         $this->last_response = (new LastResponse)->populateFromObject($this->last_response);
      }
      
   }
   
}









