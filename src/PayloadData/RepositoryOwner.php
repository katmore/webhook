<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;

class RepositoryOwner implements Populatable {
    
   /**
    * @var string
    *    The repostory owner's login.
    */
   public $login;
   
   /**
    * @var int
    *    The repostory owner's id.
    */
   public $id;
   
   use PopulatorTrait;
}