<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;

/**
 * Commit author data of the Events API payload
 * @see https://developer.github.com/v3/activity/events/types/#pushevent
 */
abstract class CommitAuthor implements Populatable {
    
   /**
    * @var string
    *    The git author's name.
    */
   public $name;
    
   /**
    * @var string
    *    The git author's email address.
    */
   public $email;
    
   /**
    * @var string
    *    The git author's username.
    */
   public $username;
    
   use PopulatorTrait;
}