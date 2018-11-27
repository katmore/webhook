<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;
use Webhook\PopulateListener;

/**
 * Commit data of the Events API payload
 * @see https://developer.github.com/v3/activity/events/types/#pushevent
 */
class Commit implements Populatable,PopulateListener  {
   /**
    * @var string The SHA of the commit.
    */
   public $sha;
   
   use CommitTrait;
   
   use PopulatorTrait;
   
}