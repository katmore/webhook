<?php
namespace Webhook\Payload;

use Webhook\Payload;
use Webhook\PayloadData\Commit;
use Webhook\PayloadData\HeadCommit;
use Webhook\PayloadData\Pusher;

/**
 * Payload data provided by PushEvent
 * @see https://developer.github.com/v3/activity/events/types/#pushevent
 */
class PushEvent extends Payload {
   
   public function getEvent(): string {
      return "push";
   }
   
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
   
   public function populateComplete() {
      parent::populateComplete();
      
      foreach($this->commits as &$commit) {
         if ((!$commit instanceof Commit) && is_object($commit)) {
            $commit = (new Commit)->populateFromObject($commit);
         }
      }
      
      if ((!$this->head_commit instanceof HeadCommit) && is_object($this->head_commit)) {
         $this->head_commit = (new HeadCommit)->populateFromObject($this->head_commit);
      }
      
      if ((!$this->pusher instanceof Pusher) && is_object($this->pusher)) {
         $this->pusher = (new Pusher)->populateFromObject($this->pusher);
      }
   }
}











