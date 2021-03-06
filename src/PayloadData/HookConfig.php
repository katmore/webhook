<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;

class HookConfig implements Populatable  {
   
   /**
    * @var string url
    */
   public $url;
   
   /**
    * @var string payload content type, i.e. "json" or "x-www-form-urlencoded" (but NOT the full MIME-type name)
    */
   public $content_type;
   
   /**
    * @var string "0" if the SSL certificate was validated, "1" otherwise
    */
   public $insecure_ssl;
   
   use PopulatorTrait;
}