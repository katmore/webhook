<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;

class Pusher implements Populatable {
   
   /**
    * @var string name of the "pusher"
    */
   public $name;
   
   /**
    * @var string email address of the "pusher"
    */
   public $email;
   
   use PopulatorTrait;
   
}