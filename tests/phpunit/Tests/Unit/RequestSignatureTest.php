<?php
declare(strict_types=1);
namespace Webhook\Tests\Unit;

use PHPUnit\Framework\TestCase;

use Webhook\TestCase\PushPayloadTrait;
use Webhook\TestCase\PingPayloadTrait;
use Webhook\Request;
use Webhook\SignatureInvalidException;
use Webhook\InvalidRequestException;
use Webhook\SignatureMissingException;

class RequestSignatureTest extends TestCase {
   use PushPayloadTrait;
   use PingPayloadTrait;
   
   public function invalidSignatureProvider() : array {
      return [
         ["my-secret",static::getPushRequestSignature("my-secret-XXXX"),static::getPushRequestBody()],
         ["my-secret",static::getPingRequestSignature("my-secret-XXXX"),static::getPingRequestBody()],
      ];
   }
   
   /**
    * @dataProvider invalidSignatureProvider
    */
   public function testFalseOnInvalidSignature(string $hub_secret,string $hub_signature,string $request_body) {
      $this->assertFalse(Request::isValidSignature($hub_secret, $hub_signature, $request_body));
   }
   
   public function validSignatureProvider() : array {
      return [
         ["my-secret",static::getPushRequestSignature("my-secret"),static::getPushRequestBody()],
         ["my-secret",static::getPingRequestSignature("my-secret"),static::getPingRequestBody()],
      ];
   }
   
   /**
    * @dataProvider validSignatureProvider
    */
   public function testTrueOnValidSignature(string $hub_secret,string $hub_signature,string $request_body) {
      $this->assertTrue(Request::isValidSignature($hub_secret, $hub_signature, $request_body));
   }
   
   public function requestWithInvalidSignatureProvider() : array {
      return [
         ["my-secret",new Request(static::getPushRequestBody(), static::getPushRequestSignature("my-secret-XXXX"), static::getExpectedPushEventName())],
         ["my-secret",new Request(static::getPingRequestBody(), static::getPingRequestSignature("my-secret-XXXX"), static::getExpectedPingEventName())],
      ];
   }
   
   /**
    * @depends testFalseOnInvalidSignature
    * @dataProvider requestWithInvalidSignatureProvider
    */
   public function testThrownExceptionOnRequestWithInvalidSignature(string $hub_secret, Request $request) {
      
      $this->expectException(SignatureInvalidException::class);
      $this->expectExceptionCode(InvalidRequestException::REASON_CODE_SIGNATURE_INVALID);
      $this->expectExceptionMessage(SignatureInvalidException::REASON_TEXT);
      
      $request->validateSignature($hub_secret);
      
   }
   
   public function requestBodyEventNameProvider() : array {
      return [
         [static::getPushRequestBody(),static::getExpectedPushEventName()],
         [static::getPingRequestBody(),static::getExpectedPingEventName()]
      ];
   }
   
   /**
    * @dataProvider requestBodyEventNameProvider
    */
   public function testThrownExceptionOnRequestWithEmptySignature(string $request_body, string $event) {
      $this->expectException(SignatureMissingException::class);
      $this->expectExceptionCode(InvalidRequestException::REASON_CODE_MISSING_SIGNATURE);
      $this->expectExceptionMessage(SignatureMissingException::REASON_TEXT);
      new Request($request_body,'',$event);
   }
   
   public function requestWithValidSignatureProvider() : array {
      return [
         ["my-secret",new Request(static::getPushRequestBody(), static::getPushRequestSignature("my-secret"), static::getExpectedPushEventName())],
         ["my-secret",new Request(static::getPingRequestBody(), static::getPingRequestSignature("my-secret"), static::getExpectedPingEventName())],
      ];
   }
   
   /**
    * @depends testTrueOnValidSignature
    * @dataProvider requestWithValidSignatureProvider
    */
   public function testValidateSignature(string $hub_secret, Request $request) {
      $valid = false;
      try {
         $request->validateSignature($hub_secret);
         $valid = true;
      } catch (SignatureInvalidException $e) {
         
      }
      $this->assertTrue($valid,'Request::validateSignature() should not throw a SignatureInvalidException');
   }
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
}