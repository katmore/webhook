<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;

class RepositoryOwner implements Populatable {
    
   /**
    * @var string
    *    The repostory owner's name.
    */
   public $name;
   
   /**
    * @var string
    *    The repostory owner's email address.
    */
   public $email;
   
   use PopulatorTrait;
}