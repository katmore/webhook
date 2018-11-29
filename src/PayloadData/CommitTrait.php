<?php
namespace Webhook\PayloadData;

/**
 * Commit data of the Events API payload
 * @see https://developer.github.com/v3/activity/events/types/#pushevent
 */
trait CommitTrait {
   
   /**
    * @var string sha of the commit
    */
   public $id;
   
   /**
    * @var string sha of the commit tree
    */
   public $tree_id;
   
   /**
    * @var string The commit message.
    */
   public $message;
    
   /**
    * @var \Webhook\PayloadData\GitUser object of the git user that authored the commit
    */
   public $author;
   
   /**
    * @var \Webhook\PayloadData\GitUser object of the git user that created the commit
    */
   public $committer;
    
   /**
    * @var string Points to the commit API resource.
    */
   public $url;
    
   /**
    * @var bool true if this commit is distinct to all others pushed before, <b>bool</b> false otherwise
    */
   public $distinct;
   
   /**
    * @var string
    *    The time of the commit expressed as a ISO 8601 Timestamp.
    */
   public $timestamp;
   
   /**
    * @var string[] paths in the repo that were added with this commit
    */
   public $added = [];
   
   /**
    * @var string[] paths in the repo that were removed with this commit
    */
   public $removed = [];
   
   /**
    * @var string[] paths in the repo that were modified with this commit
    */
   public $modified = [];
   
   /**
    * Indicates that the populating of this object is complete.
    * @return void
    */
   public function populateComplete() {
      
      if (!$this->author instanceof GitUser) {
         $this->author = (new GitUser)->populateFromObject($this->author);
      }
      
      if (!$this->committer instanceof GitUser) {
         $this->committer = (new GitUser)->populateFromObject($this->committer);
      }
      
   }
}