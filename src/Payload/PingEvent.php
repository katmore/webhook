<?php
namespace Webhook\Payload;

use Webhook\Payload;
use Webhook\PayloadData\Hook;
use Webhook\PayloadData\PingRepository;

class PingEvent extends Payload implements EventProviderInterface {
   
   /**
    * @var string The random message generated for this ping event.
    */
   public $zen;
   
   /**
    * @var int The ID of the webhook that triggered this ping event.
    */
   public $hook_id;
   
   /**
    * @var \Webhook\PayloadData\Hook hook object
    */
   public $hook;
   
   /**
    * @var \Webhook\PayloadData\PingRepository
    */
   public $repository;
   
   /**
    * @var object
    * @private
    */
   private $input;
   
   const EVENT_NAME = 'ping';
   
   /**
    * Populates a "generic" Payload <b>Event</b> object corresponding to this event.
    * @return \Webhook\Payload\Event
    */
   public function toEvent() : Event {
      return new Event($this->input,static::EVENT_NAME);
   }
   
   /**
    * Provides the event name.
    * @return string
    */
   public function getEvent(): string {
      return static::EVENT_NAME;
   }
   
   /**
    * @param object $input payload input
    */
   public function __construct($input) {
      $this->input = $input;
      parent::__construct($input);
   }
   
   /**
    * Indicates that the populating of this Payload object is complete.
    * @return void
    */
   public function populateComplete() {
      parent::populateComplete();
      
      if ((!$this->hook instanceof Hook) && is_object($this->hook)) {
         $this->hook = (new Hook)->populateFromObject($this->hook);
      }
      
      if (!$this->repository instanceof PingRepository) {
         $this->repository = (new PingRepository)->populateFromObject($this->repository);
      }
   }
}