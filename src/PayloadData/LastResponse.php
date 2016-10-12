<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;

class LastResponse implements Populatable {
   
   /**
    * @var string
    */
   public $code;
   
   /**
    * @var string
    */
   public $status;
   
   /**
    * @var string
    */
   public $message;
   
   use PopulatorTrait;
   
}