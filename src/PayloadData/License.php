<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;

class License implements Populatable {
   
   use PopulatorTrait;
   
   /**
    * @var string repo license key
    */
   public $key;
   
   /**
    * @var string name of repo license
    */
   public $name;
   
   /**
    * @var string SPDX identifier of the repo license
    */
   public $spdx_id;
   
   /**
    * @var string|null the repo license URL, if any
    */
   public $url;
   
   /**
    * @var string base64 encoded repo license node id 
    */
   public $node_id;
}