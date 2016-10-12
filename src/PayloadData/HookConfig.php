<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;

class HookConfig implements Populatable  {
   
   public $url;
   
   public $content_type;
   
   public $insecure_ssl;
   
   use PopulatorTrait;
}