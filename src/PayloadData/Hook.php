<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;
use Webhook\PopulateListener;
use Webhook\PayloadData\HookConfig;
use Webhook\PayloadData\LastResponse;

class Hook implements Populatable,PopulateListener  {
   
   /**
    * @var string The webhook's id.
    */
   public $id;
   
   /**
    * @var string Points to the webhook's API resource.
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
    * @var boolean Wheather or not this webhook is active.
    */
   public $active;
   
   /**
    * @var \Webhook\PayloadData\HookConfig
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
    * @var \Webhook\PayloadData\LastResponse
    */
   public $last_response;
   
   use PopulatorTrait;
   
   public function populateComplete() {
      
      if ((!$this->config instanceof HookConfig) && is_object($this->config)) {
         $this->config = (new HookConfig)->populateFromObject($this->config);
      }
      
      if ((!$this->last_response instanceof LastResponse) && is_object($this->last_response)) {
         $this->last_response = (new LastResponse)->populateFromObject($this->last_response);
      }
      
   }
   
}









