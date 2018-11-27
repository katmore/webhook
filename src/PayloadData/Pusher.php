<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;

class Pusher implements Populatable {
   
   public $name;
   
   public $email;
   
   use PopulatorTrait;
   
}