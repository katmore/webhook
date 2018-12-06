<?php
declare(strict_types=1);
namespace Webhook\Tests\Unit;

use PHPUnit\Framework\TestCase;

use ReflectionClass;

use Webhook\EventCallbackRule;
use Webhook\UrlCallbackRule;
use Webhook\Callback;
use Webhook\Payload;
use Webhook\Payload\Event;
use Webhook\Payload\PushEvent;
use Webhook\Payload\PingEvent;
use Webhook\InvalidRequestException;
use Webhook\CallbackRule;

use Webhook\TestCase\PushPayloadTrait;
use Webhook\TestCase\PingPayloadTrait;

class CallbackTest extends TestCase {
   
   use PingPayloadTrait;
   use PushPayloadTrait;
   
   public function urlCallbackRuleProvider() : array {
      $data = [];
      
      $param = [];
      
      $param []= new UrlCallbackRule('http://example.com/some-url-1');
      $param []= new UrlCallbackRule('http://example.com/some-url-2');
      
      $data []= $param;
      
      return $data;
   }
   /**
    * @dataProvider urlCallbackRuleProvider
    */
   public function testUrlCallbackRulePopulation(UrlCallbackRule ...$url_callback_rule) {
      
      $callback = new Callback('some-hub-secret',function(){},...$url_callback_rule);
      $urlRule = (new ReflectionClass($callback))->getProperty('_urlRule');
      $urlRule->setAccessible(true);
      $this->assertEquals($url_callback_rule,$urlRule->getValue($callback));
      
   }
   
   public function urlRuleProvider() : array {
      $data = [];
      
      foreach(['url','html_url','git_url','ssh_url','clone_url','svn_url'] as $prop) {
         $param = [];
         
         $param []= new Event(json_decode(json_encode([
            'event'=>'some-event',
            'repository'=>[
               $prop=>"http://example.com/some-$prop-url",
               'name'=>'bla'
            ]
         ])),'some-event');
         $param []= 'I am callback';
         $param []= function() { echo 'I am callback'; };
         $param []= new UrlCallbackRule("http://example.com/some-$prop-url");
         $param []= new UrlCallbackRule('http://example.com/some-url-2');
         
         $data []= $param;
      }
      unset($prop);
      
      return $data;
   }
   
   /**
    * @dataProvider urlRuleProvider
    */
   public function testValidatePayloadWithUrlRule(Event $event,string $callback_echo_str,callable $callback,UrlCallbackRule ...$url_callback_rule) {
      $callback = new Callback('some-hub-secret',$callback,...$url_callback_rule);
      $this->expectOutputString($callback_echo_str);
      $callback->validatePayload($event);
   }
   
   
   public function noUrlMatchProvider() : array {
      $data = [];
      
      foreach(['url','html_url','git_url','ssh_url','clone_url','svn_url'] as $prop) {
         $param = [];
         
         $param []= new Event(json_decode(json_encode([
            'event'=>'some-event',
            'repository'=>[
               $prop=>"http://example.com/some-$prop-url",
               'name'=>'bla'
            ]
         ])),'some-event');
         $param []= 'I am callback';
         $param []= function() { echo 'I am callback'; };
         $param []= new UrlCallbackRule("http://example.com/not-matching-url-1");
         $param []= new UrlCallbackRule('http://example.com/not-matching-url-2');
         
         $data []= $param;
      }
      unset($prop);
      
      return $data;
   }
   
   /**
    * @dataProvider noUrlMatchProvider
    */
   public function testThrowsInvalidRequestExceptionOnNoUrlMatch(Event $event,string $callback_echo_str,callable $callback,UrlCallbackRule ...$url_callback_rule) {
      $callback = new Callback('some-hub-secret',$callback,...$url_callback_rule);
      $this->expectException(InvalidRequestException::class);
      $callback->validatePayload($event);
   }
   
   public function noEventMatchProvider() : array {
      $data = [];
      
      $param = [];
      
      $param []= new Event(json_decode(json_encode([
         'event'=>'some-event',
         'repository'=>[
            'url'=>"http://example.com/some-url",
            'name'=>'bla'
         ]
      ])),'some-event');
//       $param []= 'I am callback';
//       $param []= function() { echo 'I am callback'; };
      $param []= new EventCallbackRule("some-other-event-1");
      $param []= new EventCallbackRule('some-other-event-2');
      
      $data []= $param;
      
      return $data;
   }
   
   /**
    * @dataProvider noEventMatchProvider
    */
   public function testThrowsInvalidRequestExceptionOnNoEventMatch(Event $event,EventCallbackRule ...$event_callback_rule) {
      $callback = new Callback('some-hub-secret',function(){},...$event_callback_rule);
      $this->expectException(InvalidRequestException::class);
      $callback->validatePayload($event);
   }
   
   public function eventRuleProvider() : array {
      $data = [];
      
      $param = [];
      
      $param []= new Event(json_decode(json_encode([
         'event'=>'some-event',
         'repository'=>[
            'url'=>"http://example.com/some-url",
            'name'=>'bla'
         ]
      ])),'some-event');
      $param []= 'I am callback';
      $param []= function() { echo 'I am callback'; };
      $param []= new EventCallbackRule('some-event');
      $param []= new EventCallbackRule('some-other-event-2');
      
      $data []= $param;
      
      return $data;
   }
   
   /**
    * @dataProvider eventRuleProvider
    */
   public function testValidatePayloadWithEventRule(Event $event,string $callback_echo_str,callable $callback,EventCallbackRule ...$event_callback_rule) {
      $callback = new Callback('some-hub-secret',$callback,...$event_callback_rule);
      $this->expectOutputString($callback_echo_str);
      $callback->validatePayload($event);
   }
   
   public function validRequestProvider() : array {
      return [
         [
            new PushEvent(static::getPushRequestObject()),
            'I am callback',
            function() { echo 'I am callback'; },
            'my-secret',
            static::getPushRequestSignature('my-secret'),
            static::getPushRequestBody(),
         ],
         [
            new PingEvent(static::getPushRequestObject()),
            'I am callback',
            function() { echo 'I am callback'; },
            'my-secret',
            static::getPingRequestSignature('my-secret'),
            static::getPingRequestBody(),
         ],
      ];
   }
   
   /**
    * @dataProvider validRequestProvider
    */
   public function testValidateValidRequest(
      Payload $payload,
      string $callback_echo_str,
      callable $callback,
      string $hub_secret,
      string $hub_signature,
      string $raw_payload
      ) {
         $callback = new Callback($hub_secret,$callback);
         $this->expectOutputString($callback_echo_str);
         $callback->validateRequest($hub_signature,$raw_payload,$payload);
   }
   
   public function badSignatureRequestProvider() : array {
      return [
         [
            new PushEvent(static::getPushRequestObject()),
            'I am callback',
            function() { echo 'I am callback'; },
            'my-secret-XXX',
            static::getPushRequestSignature('my-secret'),
            static::getPushRequestBody(),
         ],
         [
            new PingEvent(static::getPushRequestObject()),
            'I am callback',
            function() { echo 'I am callback'; },
            'my-secret-XXX',
            static::getPingRequestSignature('my-secret'),
            static::getPingRequestBody(),
         ],
      ];
   }
   
   /**
    * @dataProvider badSignatureRequestProvider
    */
   public function testThrowsInvalidRequestExceptionOnBadSignature(
      Payload $payload,
      string $callback_echo_str,
      callable $callback,
      string $hub_secret,
      string $hub_signature,
      string $raw_payload
      ) {
         $callback = new Callback($hub_secret,$callback);
         $this->expectException(InvalidRequestException::class);
         $callback->validateRequest($hub_signature,$raw_payload,$payload);
   }
   
   public function testCallbackRuleBadRuleValue() {
      $callbackRule = new class("some rule value") extends CallbackRule {
         public function __construct(string $ruleValue) {
            $this->_ruleValue = ['bad-rule-value'];
         }  
      };
      $this->assertEquals("",$callbackRule->getValue());
   }
   
   public function eventNameProvider() {
      return [
         ['some-event-1'],
         ['some-event-2'],
      ];
   }
   
   /**
    * @dataProvider eventNameProvider
    */
   public function testEventCallbackRule(string $event) {
      $rule = new EventCallbackRule($event);
      $this->assertEquals($event,$rule->getValue());
      
   }
   
   public function urlProvider() : array {
      return [
         ['http://example.com/some-url-1'],
         ['http://example.com/some-url-2'],
      ];
   }
   
   /**
    * @dataProvider urlProvider
    */
   public function testUrlCallbackRuleValue(string $url) {
      $rule = new UrlCallbackRule($url);
      $this->assertEquals($url,$rule->getValue());
   }
   
   
   
   
   
   
   
   
}