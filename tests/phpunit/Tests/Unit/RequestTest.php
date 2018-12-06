<?php
declare(strict_types=1);
namespace Webhook\Tests\Unit;

use PHPUnit\Framework\TestCase;

use Webhook\Request;
use Webhook\Payload;

use Webhook\TestCase\PushPayloadTrait;

class RequestTest extends TestCase {
   
   use PushPayloadTrait;
   
   public function validServiceParamProvider() : array {
      $pushDelivery = 'f8d91fad-e273-4648-bb33-d7a2312f6253';
      $userAgent = 'GitHub-Hookshot/123456';
      //$pingDelivery = '791c24cb-e2b2-45ab-8d43-a235fdba23db';
      return [
         [
            static::getPushRequestBody(),
            [
               'HTTP_X_HUB_SIGNATURE'=>static::getPushRequestSignature('my-secret'),
               'HTTP_X_GITHUB_EVENT'=>static::getExpectedPushEventName(),
               'CONTENT_TYPE'=>'application/json',
               'HTTP_X_GITHUB_DELIVERY'=>$pushDelivery,
               'REQUEST_METHOD'=>'POST',
               'HTTP_USER_AGENT'=>$userAgent,
               
            ],
            'my-secret',
         ],
      ];
   }
   
   /**
    * @runInSeparateProcess
    * @dataProvider validServiceParamProvider
    */
   public function testService(string $message_body,array $param,string $hub_secret) {
      $request = Request::service($message_body,$param);
      $this->assertTrue($request->isValidSignature($hub_secret,$param['HTTP_X_HUB_SIGNATURE'],$message_body));
      $this->assertEquals($message_body,$request->getMessageBody());
      $this->assertInstanceOf(Payload::class,$request->getPayload());
      
      
   }
}