<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;

class Sender implements Populatable {
   /**
    * @var string 
    */
   public $login;
   
   /**
    * @var string
    */
   public $id;
   
   /**
    * @var string
    */
   public $avatar_url;
   
   /**
    * @var string
    */
   public $gravitar_id;
   
   /**
    * @var string
    */
   public $url;
   
   /**
    * @var string
    */
   public $html_url;
   
   /**
    * @var string
    */
   public $type;
   
   /**
    * @var string
    */
   public $site_admin;
   use PopulatorTrait;
}