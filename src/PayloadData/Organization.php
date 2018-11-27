<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;

class Organization implements Populatable {
   
   /**
    * @var string The organization's username.
    */
   public $login;
   
   /**
    * @var string The organization's id.
    */
   public $id;
   
   /**
    * @var string Points to the organization's api resource.
    */
   public $url;
   
   /**
    * @var string Points to the organization's repo api resource.
    */
   public $repos_url;
   
   /**
    * @var string Points to the organization's events api resource.
    */
   public $events_url;
   
   /**
    * @var string Points to the organization's hooks api resource.
    */
   public $hooks_url;
   
   /**
    * @var string Points to the organization's issues api resource.
    */
   public $issues_url;
   
   /**
    * @var string Points to the organization's members api resource.
    */
   public $members_url;
   
   /**
    * @var string Points to the organization's public members api resource.
    */
   public $public_members_url;
   
   /**
    * @var string Points to the organization's avatar.
    */
   public $avatar_url;
   
   /**
    * @var string The description of the company.
    */
   public $description;
   
   use PopulatorTrait;
}