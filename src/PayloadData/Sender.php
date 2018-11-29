<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;

class Sender implements Populatable {
   /**
    * @var string the github username of the event sender
    */
   public $login;
   
   /**
    * @var int the unique github ID of the event sender
    */
   public $id;
   
   /**
    * @var string the avatar URL of the event sender
    */
   public $avatar_url;
   
   /**
    * @var string the gravatar ID, if any, of the event sender
    */
   public $gravatar_id;
   
   /**
    * @var string the github API URL of the event sender
    */
   public $url;
   
   /**
    * @var string the github URL of the event sender
    */
   public $html_url;
   
   /**
    * @var string the type of event sender (i.e. "User")
    */
   public $type;
   
   /**
    * @var bool true if event sender is a site admin, <b>bool</b> false otherwise
    */
   public $site_admin;
   
   use PopulatorTrait;
}