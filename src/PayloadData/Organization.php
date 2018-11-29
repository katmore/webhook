<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;

class Organization implements Populatable {
   
   /**
    * @var string The organization's github username.
    */
   public $login;
   
   /**
    * @var int The organization's github id.
    */
   public $id;
   
   /**
    * @var string Points to the organization's api URL.
    */
   public $url;
   
   /**
    * @var string Points to the organization's repo api URL.
    */
   public $repos_url;
   
   /**
    * @var string Points to the organization's events api URL.
    */
   public $events_url;
   
   /**
    * @var string Points to the organization's hooks api URL.
    */
   public $hooks_url;
   
   /**
    * @var string Points to the organization's issues api URL.
    */
   public $issues_url;
   
   /**
    * @var string Points to the organization's members api URL.
    */
   public $members_url;
   
   /**
    * @var string Points to the organization's public members api URL.
    */
   public $public_members_url;
   
   /**
    * @var string Points to the organization's avatar URL.
    */
   public $avatar_url;
   
   /**
    * @var string The description of the organization.
    */
   public $description;
   
   use PopulatorTrait;
}