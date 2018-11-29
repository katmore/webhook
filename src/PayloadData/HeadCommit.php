<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;
use Webhook\PopulateListener;

/**
 * Commit data of the Events API payload
 * @see https://developer.github.com/v3/activity/events/types/#pushevent
 */
class HeadCommit implements Populatable,PopulateListener  {
   
   use CommitTrait;
   
   use PopulatorTrait;
}