<?php
declare(strict_types=1);
namespace Webhook\Tests\Unit;

use PHPUnit\Framework\TestCase;

use Webhook\TestCase\PingPayloadTrait;
use Webhook\TestCase\PayloadDataTrait;

use Webhook\PayloadData\LastResponse;

use stdClass;

class LastResponseDataTest extends TestCase {
   use PingPayloadTrait;
   use PayloadDataTrait;
   
   public function pingEventLastResponseObjectProvider() : array {
      $hookObj = static::getExpectedPingRequestObjectValue('hook');
      if (!is_object($hookObj)) {
         trigger_error("the 'hook' property of ping request object was not an object type as expected",E_USER_ERROR);
      }
      if (!property_exists($hookObj, 'last_response')) {
         trigger_error("missing 'last_response' property from 'hook' property of ping request object",E_USER_ERROR);
      }
      if (!is_object($hookObj->last_response)) {
         trigger_error("the 'last_response' property was not an object as expected in 'hook' property of ping request object",E_USER_ERROR);
      }
      return [
         [$hookObj->last_response],
      ];
   }
   
   /**
    * @dataProvider pingEventLastResponseObjectProvider
    */
   public function testPingEventLastResponse(stdClass $last_response_obj) {
      $lastResponse = new LastResponse;
      $lastResponse->populateFromObject($last_response_obj);
      
      $this->payloadObjectEqualityTests($last_response_obj, $lastResponse);
   }
   
   
   
   
   
   
   
}