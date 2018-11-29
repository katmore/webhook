<?php
declare(strict_types=1);
namespace Webhook\Tests\Unit;

use PHPUnit\Framework\TestCase;

use Webhook\TestCase\PushPayloadTrait;
use Webhook\TestCase\PayloadDataTrait;

use Webhook\PayloadData\Sender;

use stdClass;

class SenderDataTest extends TestCase {
   use PushPayloadTrait;
   use PayloadDataTrait;
   
   public function pushEventSenderObjectProvider() : array {
      $senderObj = static::getExpectedPushRequestObjectValue('sender');
      if (!is_object($senderObj)) {
         trigger_error("the 'sender' property of push request object was not an object type as expected",E_USER_ERROR);
      }
      return [
         [$senderObj],
      ];
   }
   
   /**
    * @dataProvider pushEventSenderObjectProvider
    */
   public function testPushEventSender(stdClass $sender_obj) {
      $sender = new Sender;
      $sender->populateFromObject($sender_obj);
      $this->payloadObjectEqualityTests($sender_obj, $sender);
   }
   
   
   
   
   
   
   
}