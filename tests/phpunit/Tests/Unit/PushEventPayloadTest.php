<?php
declare(strict_types=1);
namespace Webhook\Tests\Unit;

use PHPUnit\Framework\TestCase;

use Webhook\TestCase\PushPayloadTrait;
use Webhook\TestCase\PayloadDataTrait;

use Webhook\Payload\PushEvent;
use Webhook\Payload\Event;

use stdClass;

class PushEventPayloadTest extends TestCase {
   use PushPayloadTrait;
   use PayloadDataTrait;
   
   /**
    * @dataProvider pushRequestObjectProvider
    * @covers \Webhook\Payload\PushEvent::populateComplete()
    * @covers \Webhook\Payload\PushEvent::populateFromObject()
    * @covers \Webhook\Payload\PushEvent::__construct()
    */
   public function testPushEventPayloadData(stdClass $push_request_obj)  {
      
      $pushEvent = new PushEvent($push_request_obj);
      
      $this->payloadObjectEqualityTests($push_request_obj, $pushEvent);
      
   }
   
   public function pushEventDataProvider() : array {
      return [
         [new PushEvent(static::getPushRequestObject())],
      ];
   }
   
   /**
    * @depends testPushEventPayloadData
    * @dataProvider pushEventDataProvider
    */
   public function testPushEventPayloadEventName(PushEvent $push_event) {
      
      $this->assertEquals(static::getExpectedPushEventName(), $push_event->getEvent());
      
   }
   
   /**
    * @depends testPushEventPayloadEventName
    * @dataProvider pushEventDataProvider
    */
   public function testPushEventPayloadToEvent(PushEvent $push_event) {
      $event = $push_event->toEvent();
      $this->assertEquals($push_event->getEvent(), $event->getEvent());
      
   }
   
   public function pushEventDataToEventProvider() : array {
      return [
         [static::getPushRequestObject(),(new PushEvent(static::getPushRequestObject()))->toEvent()],
      ];
   }
   
   /**
    * @depends testPushEventPayloadToEvent
    * @dataProvider pushEventDataToEventProvider
    */
   public function testPushEventPayloadToEventData(stdClass $push_request_obj,Event $event) {
      $this->payloadObjectEqualityTests($push_request_obj, $event);
   }
   
   public function pushEventPayloadToEventDataPayloadDataProvider() : array {
      return [
         [static::getPushRequestObject(),new PushEvent(static::getPushRequestObject())],
      ];
   }
   
   /**
    * @depends testPushEventPayloadToEventData
    * @dataProvider pushEventPayloadToEventDataPayloadDataProvider
    */
   public function testPushEventPayloadToEventDataPayloadData(stdClass $push_request_obj,PushEvent $push_event) {
      
      $event = new Event($push_request_obj,static::getExpectedPushEventName());
      
      $this->assertEquals($push_event->toEvent(), $event->toEvent());
      
      $pushRequestData = json_decode(json_encode($push_request_obj),true);
      $eventPayloadData = $event->getPayloadData();
      $this->assertEquals($pushRequestData, $eventPayloadData);
      
      $pushRequestData = json_encode($pushRequestData);
      $eventPayloadData = json_encode($eventPayloadData);
      $this->assertEquals($pushRequestData, $eventPayloadData);
   }
   
   
   
   
   
   
}