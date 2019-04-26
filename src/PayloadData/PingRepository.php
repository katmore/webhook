<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;
use Webhook\PopulateListener;

class PingRepository implements Populatable,PopulateListener {
   
   use RepositoryTrait;
   
   use PopulatorTrait;
   
   public function populateComplete() {
      
      $this->respositoryPopulateComplete();
      
   }
   
}