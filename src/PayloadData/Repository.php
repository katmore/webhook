<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;
use Webhook\PopulateListener;

class Repository implements Populatable,PopulateListener {
   
   use RepositoryTrait;
   
   use PopulatorTrait;
   
   /**
    * @var string default branch for this repo
    */
   public $default_branch;
   
   /**
    * @var string the master branch of this repo
    */
   public $master_branch;
   
   /**
    * @var string the name of the organization that owns this repo
    */
   public $organization;
   
   public function populateComplete() {
      
      $this->respositoryPopulateComplete();
      
   }
   
   
   
   
   
   
   
   
   
   
   
   
}