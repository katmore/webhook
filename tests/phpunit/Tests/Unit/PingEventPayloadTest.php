<?php
declare(strict_types=1);
namespace Webhook\Tests\Unit;

use PHPUnit\Framework\TestCase;

use Webhook\TestCase\PingPayloadTrait;
use Webhook\TestCase\PayloadDataTrait;

use Webhook\Payload\PingEvent;
use Webhook\Payload\Event;

use stdClass;

class PingEventPayloadTest extends TestCase {
   use PingPayloadTrait;
   use PayloadDataTrait;
   
   /**
    * @dataProvider pingRequestObjectProvider
    * @covers \Webhook\Payload\PingEvent::populateComplete()
    * @covers \Webhook\Payload\PingEvent::populateFromObject()
    * @covers \Webhook\Payload\PingEvent::__construct()
    */
   public function testPingEventPayloadData(stdClass $ping_request_obj)  {
      
      $pingEvent = new PingEvent($ping_request_obj);
      
      $this->payloadObjectEqualityTests($ping_request_obj, $pingEvent);
      
   }
   
   public function pingEventDataProvider() : array {
      return [
         [new PingEvent(static::getPingRequestObject())],
      ];
   }
   
   /**
    * @depends testPingEventPayloadData
    * @dataProvider pingEventDataProvider
    */
   public function testPingEventPayloadEventName(PingEvent $ping_event) {
      
      $this->assertEquals(static::getExpectedPingEventName(), $ping_event->getEvent());
      
   }
   
   /**
    * @depends testPingEventPayloadEventName
    * @dataProvider pingEventDataProvider
    */
   public function testPingEventPayloadToEvent(PingEvent $ping_event) {
      $event = $ping_event->toEvent();
      $this->assertEquals($ping_event->getEvent(), $event->getEvent());
      
   }
   
   public function pingEventDataToEventProvider() : array {
      return [
         [static::getPingRequestObject(),(new PingEvent(static::getPingRequestObject()))->toEvent()],
      ];
   }
   
   /**
    * @depends testPingEventPayloadToEvent
    * @dataProvider pingEventDataToEventProvider
    */
   public function testPingEventPayloadToEventData(stdClass $ping_request_obj,Event $event) {
      $this->payloadObjectEqualityTests($ping_request_obj, $event);
   }
   
   
   
   
   
   
   
   
   
   
   
}