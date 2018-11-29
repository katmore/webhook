<?php
declare(strict_types=1);
namespace Webhook\Tests\Unit;

use PHPUnit\Framework\TestCase;

use Webhook\TestCase\PushPayloadTrait;
use Webhook\TestCase\PayloadDataTrait;

use Webhook\PayloadData\Pusher;

use stdClass;

class PusherDataTest extends TestCase {
   use PushPayloadTrait;
   use PayloadDataTrait;
   
   public function pushEventPusherObjectProvider() : array {
      $pusherObj = static::getExpectedPushRequestObjectValue('pusher');
      if (!is_object($pusherObj)) {
         trigger_error("the 'pusher' property of push request object was not an object type as expected",E_USER_ERROR);
      }
      return [
         [$pusherObj],
      ];
   }
   
   /**
    * @dataProvider pushEventPusherObjectProvider
    */
   public function testPushEventPusher(stdClass $pusher_obj) {
      $pusher = new Pusher;
      $pusher->populateFromObject($pusher_obj);
      
      $this->payloadObjectEqualityTests($pusher_obj, $pusher);
   }
   
   
   
   
   
   
   
}