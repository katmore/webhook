<?php
declare(strict_types=1);
namespace Webhook\Tests\Unit;

use PHPUnit\Framework\TestCase;

use Webhook\TestCase\PushPayloadTrait;
use Webhook\TestCase\PayloadDataTrait;

use Webhook\PayloadData\HeadCommit;

use stdClass;

class HeadCommitDataTest extends TestCase {
   use PushPayloadTrait;
   use PayloadDataTrait;
   
   public function pushEventHeadCommitObjectProvider() : array {
      $headCommitObj = static::getExpectedPushRequestObjectValue('head_commit');
      if (!is_object($headCommitObj)) {
         trigger_error("the 'head_commit' property of push request object was not an object type as expected",E_USER_ERROR);
      }
      return [
         [$headCommitObj],
      ];
   }
   
   /**
    * @dataProvider pushEventHeadCommitObjectProvider
    */
   public function testPushEventHeadCommit(stdClass $head_commit_obj) {
      $headCommit = new HeadCommit;
      $headCommit->populateFromObject($head_commit_obj);
      
      $this->payloadObjectEqualityTests($head_commit_obj, $headCommit);
   }
   
   
   
   
   
   
   
}