<?php
declare(strict_types=1);
namespace Webhook\Tests\Unit;

use PHPUnit\Framework\TestCase;

use Webhook\TestCase\PingPayloadTrait;
use Webhook\TestCase\PayloadDataTrait;

use Webhook\PayloadData\Hook;

use stdClass;

class HookDataTest extends TestCase {
   use PingPayloadTrait;
   use PayloadDataTrait;
   
   public function pingEventHookObjectProvider() : array {
      $hookObj = static::getExpectedPingRequestObjectValue('hook');
      if (!is_object($hookObj)) {
         trigger_error("the 'hook' property of ping request object was not an object type as expected",E_USER_ERROR);
      }
      return [
         [$hookObj],
      ];
   }
   
   /**
    * @dataProvider pingEventHookObjectProvider
    */
   public function testPingEventHook(stdClass $hook_obj) {
      $hook = new Hook;
      $hook->populateFromObject($hook_obj);
      
      $this->payloadObjectEqualityTests($hook_obj, $hook);
   }
   
   
   
   
   
   
   
}