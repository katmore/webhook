<?php
declare(strict_types=1);
namespace Webhook\Tests\Unit;

use PHPUnit\Framework\TestCase;

use Webhook\TestCase\PayloadDataTrait;
use Webhook\TestCase\PushPayloadTrait;
use Webhook\TestCase\PingPayloadTrait;
use Webhook\Request;
use Webhook\MessageBodyInvalidException;
use Webhook\InvalidRequestException;
use Webhook\EventMissingException;
use Webhook\Payload\Event;

use stdClass;

class RequestDataTest extends TestCase {
   use PushPayloadTrait;
   use PingPayloadTrait;
   use PayloadDataTrait;
   
   public function invalidMessageBodyProvider() : array {
      return [
         ['','sha1='.hash_hmac('sha1','', "my-secret"),'push'],
         ['{ "some_bad_json"','sha1='.hash_hmac('sha1','', "my-secret"),'push'],
         ['foo=bar&meep=morp','sha1='.hash_hmac('sha1','', "my-secret"),'push'],
         ['"valid JSON but not object"','sha1='.hash_hmac('sha1','', "my-secret"),'push'],
         ['["valid JSON but not object"]','sha1='.hash_hmac('sha1','', "my-secret"),'push'],
      ];
   }
   
   /**
    * @dataProvider invalidMessageBodyProvider
    */
   public function testMessageBodyInvalidExceptionThrown(string $request_body, string $hub_signature, string $event) {
      $this->expectException(MessageBodyInvalidException::class);
      $this->expectExceptionCode(InvalidRequestException::REASON_CODE_MESSAGE_BODY_INVALID);
      new Request($request_body, $hub_signature, $event);
   }
   
   public function requestBodySignatureProvider() : array {
      return [
         [static::getPushRequestBody(),static::getPushRequestSignature("my-secret")],
         [static::getPingRequestBody(),static::getPingRequestSignature("my-secret")],
      ];
   }
   
   /**
    * @dataProvider requestBodySignatureProvider
    */
   public function testEventMissingExceptionExceptionThrown(string $request_body, string $hub_signature, string $event='') {
      $this->expectException(EventMissingException::class);
      $this->expectExceptionCode(InvalidRequestException::REASON_CODE_MISSING_EVENT);
      new Request($request_body, $hub_signature, $event);
   }
   
   
   
   public function payloadClassRequestProvider() : array {
      $genericEventBody = <<<EVENT_BODY
{
  "sender": {
    "login": "examplegituser",
    "id": 999999,
    "node_id": "MDQ6VXNlcjk5OTk5OQ==",
    "avatar_url": "https://avatars0.githubusercontent.example.com/u/999999?v=4",
    "gravatar_id": "",
    "url": "https://api.github.example.com/users/examplegituser",
    "html_url": "https://github.example.com/examplegituser",
    "followers_url": "https://api.github.example.com/users/examplegituser/followers",
    "following_url": "https://api.github.example.com/users/examplegituser/following{/other_user}",
    "gists_url": "https://api.github.example.com/users/examplegituser/gists{/gist_id}",
    "starred_url": "https://api.github.example.com/users/examplegituser/starred{/owner}{/repo}",
    "subscriptions_url": "https://api.github.example.com/users/examplegituser/subscriptions",
    "organizations_url": "https://api.github.example.com/users/examplegituser/orgs",
    "repos_url": "https://api.github.example.com/users/examplegituser/repos",
    "events_url": "https://api.github.example.com/users/examplegituser/events{/privacy}",
    "received_events_url": "https://api.github.example.com/users/examplegituser/received_events",
    "type": "User",
    "site_admin": false
  }
}
EVENT_BODY;
      $genericEventBody = json_encode(json_decode($genericEventBody));
      $genericEventSignature = 'sha1='.hash_hmac('sha1',$genericEventBody,'my-secret');
      return [
         [static::getExpectedPushPayloadClass(),static::getPushRequestObject(),new Request(static::getPushRequestBody(), static::getPushRequestSignature("my-secret"), static::getExpectedPushEventName())],
         [static::getExpectedPingPayloadClass(),static::getPingRequestObject(),new Request(static::getPingRequestBody(), static::getPingRequestSignature("my-secret"), static::getExpectedPingEventName())],
         [static::getExpectedPushPayloadClass(),static::getPushRequestObject(),new Request(static::getPushRequestBody(), static::getPushRequestSignature("my-secret"), static::getExpectedPushEventName(),'application/json')],
         [static::getExpectedPingPayloadClass(),static::getPingRequestObject(),new Request(static::getPingRequestBody(), static::getPingRequestSignature("my-secret"), static::getExpectedPingEventName(),'application/json')],
         [Event::class,json_decode($genericEventBody),new Request($genericEventBody,$genericEventSignature,'wtf','application/json')],
      ];
   }
   
   /**
    * @dataProvider payloadClassRequestProvider
    * @covers \Webhook\Request::__construct()
    */
   public function testRequestPayload(string $payload_class,stdClass $request_object,Request $request) {
      
      $this->assertEquals(sha1(json_encode($request_object)),sha1(json_encode(json_decode($request->getMessageBody()))));
      
      $payload = $request->getPayload();
      
      $this->assertInstanceOf($payload_class, $payload);
      
      //$this->payloadObjectEqualityTests($request_object, $payload);
   }
   
   public function validRequestProvider() : array {
      return [
         [new Request(static::getPushRequestBody(), static::getPushRequestSignature("my-secret"), static::getExpectedPushEventName())],
         [new Request(static::getPingRequestBody(), static::getPingRequestSignature("my-secret"), static::getExpectedPingEventName())],
      ];
   }
   
   /**
    * @dataProvider validRequestProvider
    * @depends testRequestPayload
    */
   public function testContentTypeIsJson(Request $request) {
      
      $this->assertEquals('application/json', $request->getContentType());
      
   }
   
   public function eventRequestProvider() : array {
      return [
         [static::getExpectedPushEventName(),new Request(static::getPushRequestBody(), static::getPushRequestSignature("my-secret"), static::getExpectedPushEventName())],
         [static::getExpectedPingEventName(),new Request(static::getPingRequestBody(), static::getPingRequestSignature("my-secret"), static::getExpectedPingEventName())],
      ];
   }
   
   /**
    * @dataProvider eventRequestProvider
    * @depends testRequestPayload
    */
   public function testGitHubEvent(string $event, Request $request) {
      $this->assertEquals($event, $request->getGitHubEvent());
   }
   
   public function signatureRequestProvider() : array {
      return [
         [static::getPushRequestSignature("my-secret"),new Request(static::getPushRequestBody(), static::getPushRequestSignature("my-secret"), static::getExpectedPushEventName())],
         [static::getPingRequestSignature("my-secret"),new Request(static::getPingRequestBody(), static::getPingRequestSignature("my-secret"), static::getExpectedPingEventName())],
      ];
   }
   
   /**
    * @dataProvider signatureRequestProvider
    * @depends testRequestPayload
    */
   public function testHubSignature(string $hub_signature, Request $request) {
      $this->assertEquals($hub_signature, $request->getHubSignature());
   }
   
   public function deliveryRequestProvider() : array {
      $pushDelivery = 'f8d91fad-e273-4648-bb33-d7a2312f6253';
      $pingDelivery = '791c24cb-e2b2-45ab-8d43-a235fdba23db';
      return [
         [
            $pushDelivery,
            new Request(
               static::getPushRequestBody(), 
               static::getPushRequestSignature("my-secret"), 
               static::getExpectedPushEventName(),
               '',
               $pushDelivery
            )
         ],
         [
            $pingDelivery,
            new Request(
               static::getPingRequestBody(),
               static::getPingRequestSignature("my-secret"),
               static::getExpectedPingEventName(),
               '',
               $pingDelivery
               )
         ],
      ];
   }
   
   /**
    * @dataProvider deliveryRequestProvider
    * @depends testRequestPayload
    */
   public function testGitHubDelivery(string $git_hub_delivery, Request $request) {
      $this->assertEquals($git_hub_delivery, $request->getGitHubDelivery());
   }
   
   public function requestMethodRequestProvider() : array {
      $requestMethod = 'POST';
      return [
         [
            $requestMethod,
            new Request(
               static::getPushRequestBody(),
               static::getPushRequestSignature("my-secret"),
               static::getExpectedPushEventName(),
               '',
               '',
               $requestMethod
               )
         ],
         [
            $requestMethod,
            new Request(
               static::getPingRequestBody(),
               static::getPingRequestSignature("my-secret"),
               static::getExpectedPingEventName(),
               '',
               '',
               $requestMethod
               )
         ],
      ];
   }
   
   /**
    * @dataProvider requestMethodRequestProvider
    * @depends testRequestPayload
    */
   public function testRequestMethod(string $request_method, Request $request) {
      $this->assertEquals($request_method, $request->getRequestMethod());
   }
   
   public function userAgentRequestProvider() : array {
      $userAgent = 'GitHub-Hookshot/123456';
      return [
         [
            $userAgent,
            new Request(
               static::getPushRequestBody(),
               static::getPushRequestSignature("my-secret"),
               static::getExpectedPushEventName(),
               '',
               '',
               '',
               $userAgent
               )
         ],
         [
            $userAgent,
            new Request(
               static::getPingRequestBody(),
               static::getPingRequestSignature("my-secret"),
               static::getExpectedPingEventName(),
               '',
               '',
               '',
               $userAgent
               )
         ],
      ];
   }
   
   /**
    * @dataProvider userAgentRequestProvider
    * @depends testRequestPayload
    */
   public function testUserAgent(string $user_agent, Request $request) {
      $this->assertEquals($user_agent, $request->getUserAgent());
   }
   
   
   
   
   
   
   
   
   
   
   
   
}