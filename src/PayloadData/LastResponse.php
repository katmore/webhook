<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;

class LastResponse implements Populatable {
   
   /**
    * @var int http response code
    */
   public $code;
   
   /**
    * @var string response status string
    */
   public $status;
   
   /**
    * @var string response message
    */
   public $message;
   
   use PopulatorTrait;
   
}