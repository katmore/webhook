<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;
use Webhook\PopulateListener;

class Repository implements Populatable,PopulateListener {
   
   /**
    * @var string The id of the repository.
    */
   public $id;
   
   public $name;
   
   public $full_name;
   
   public $private;
   
   public $description;
   
   public $fork;
   
   public $html_url;
   
   public $url;
   
   public $git_url;
   
   public $ssh_url;
   
   public $clone_url;
   
   public $svn_url;
   
   public $homepage;
   
   public $size;
   
   /**
    * @var \Webhook\PayloadData\RepositoryOwner
    */
   public $owner;
   
   use PopulatorTrait;
   
   public function populateComplete() {
      
      if (!$this->owner instanceof RepositoryOwner) {
         $this->owner = (new RepositoryOwner)->populateFromObject($this->owner);
      }
      
   }
}