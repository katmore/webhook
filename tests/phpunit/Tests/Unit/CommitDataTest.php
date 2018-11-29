<?php
declare(strict_types=1);
namespace Webhook\Tests\Unit;

use PHPUnit\Framework\TestCase;

use Webhook\TestCase\PushPayloadTrait;
use Webhook\TestCase\PayloadDataTrait;

use Webhook\PayloadData\Commit;

use stdClass;

class CommitDataTest extends TestCase {
   use PushPayloadTrait;
   use PayloadDataTrait;
   
   public function pushEventCommitsElementsProvider() : array {
      
      $commits = static::getExpectedPushRequestObjectValue('commits');
      
      $ret = [];
      foreach($commits as $c) {
         $ret []= [$c];
      }
      unset($c);
      return $ret;
   }
   
   
   /**
    * @dataProvider pushEventCommitsElementsProvider
    */
   public function testPushEventCommitsElements(stdClass $commit_element)  {
      
      $commit = new Commit;
      $commit->populateFromObject($commit_element);
      
      $this->payloadObjectEqualityTests($commit_element, $commit);
      
   }   
   
}