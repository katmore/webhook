<?php
namespace Webhook\PayloadData;

/**
 * Commit data of the Events API payload
 * @see https://developer.github.com/v3/activity/events/types/#pushevent
 */
trait CommitTrait {
   /**
    * @var string The commit message.
    */
   public $message;
    
   /**
    * @var \Webhook\PayloadData\CommitAuthor
    *    The git author of the commit.
    */
   public $author;
    
   /**
    * @var string Points to the commit API resource.
    */
   public $url;
    
   /**
    * @var bool Whether this commit is distinct from any that have been pushed before.
    */
   public $distinct;
   
   /**
    * @var string
    *    The time of the commit expressed as a ISO 8601 Timestamp.
    */
   public $timestamp;
   
   public function populateComplete() {
      
      if (!$this->author instanceof CommitAuthor) {
         $this->author = (new CommitAuthor)->populateFromObject($this->author);
      }
      
   }
}