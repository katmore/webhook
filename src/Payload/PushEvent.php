<?php
namespace Webhook\Payload;

use Webhook\Payload;
use Webhook\PayloadData;

/**
 * Payload data provided by PushEvent
 * @see https://developer.github.com/v3/activity/events/types/#pushevent
 */
class PushEvent extends Payload {
   
   /**
    * @var \Webhook\PayloadData\Organization
    */
   public $organization;
   
   /**
    * @var \Webhook\PayloadData\Repository
    */
   public $repository;
   
   /**
    * @var string
    *    The full Git ref that was pushed. Example: "refs/heads/master".
    */
   public $ref;
   
   /**
    * @var string
    *    The SHA of the most recent commit on ref after the push.
    */
   public $before;
    
   /**
    * @var string
    *    The SHA of the most recent commit on ref before the push.
    */
   public $after;
   
   /**
    * @var string
    *    Compare View URL
    */
   public $compare;
   
   /**
    * @var \Webhook\PayloadData\Commit[]
    */
   public $commits = [];
   
   /**
    * @var \Webhook\PayloadData\HeadCommit
    */
   public $head_commit;
   
   /**
    * @var \Webhook\PayloadData\Pusher
    */
   public $pusher;
   
   /**
    * @var object
    * @private
    */
   private $input;
   
   const EVENT_NAME = 'push';
   
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
      
      foreach($this->commits as &$commit) {
         if ((!$commit instanceof PayloadData\Commit) && is_object($commit)) {
            $commit = (new PayloadData\Commit)->populateFromObject($commit);
         }
      }
      
      if ((!$this->head_commit instanceof PayloadData\HeadCommit) && is_object($this->head_commit)) {
         $this->head_commit = (new PayloadData\HeadCommit)->populateFromObject($this->head_commit);
      }
      
      if ((!$this->pusher instanceof PayloadData\Pusher) && is_object($this->pusher)) {
         $this->pusher = (new PayloadData\Pusher)->populateFromObject($this->pusher);
      }
      
      if (!$this->organization instanceof PayloadData\Organization) {
         $this->organization = (new PayloadData\Organization)->populateFromObject($this->organization);
      }
      
      if (!$this->repository instanceof PayloadData\Repository) {
         $this->repository = (new PayloadData\Repository)->populateFromObject($this->repository);
      }
   }
}











