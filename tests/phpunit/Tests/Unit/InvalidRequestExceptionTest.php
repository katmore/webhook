<?php
declare(strict_types=1);
namespace Webhook\Tests\Unit;

use PHPUnit\Framework\TestCase;

use Webhook\InvalidRequestException;

class InvalidRequestExceptionTest extends TestCase {
   public function constructorParamsProvider() : array {
      return [
         ['some-reason',100],
      ];
   }
   /**
    * @dataProvider constructorParamsProvider
    */
   public function testContructorParms(string $reason, int $reason_code) {
      $e = new InvalidRequestException($reason,$reason_code);
      $this->assertEquals($reason,$e->getReason());
      $this->assertEquals($reason_code,$e->getCode());
   }
   
   public function testDefaultParams() {
      $e = new InvalidRequestException;
      $this->assertEquals('unknown error',$e->getReason());
      $this->assertEquals(0,$e->getCode());
   }
}