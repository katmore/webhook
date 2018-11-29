<?php
declare(strict_types=1);
namespace Webhook\Tests\Unit;

use PHPUnit\Framework\TestCase;

use Webhook\TestCase\PingPayloadTrait;
use Webhook\TestCase\PayloadDataTrait;

use Webhook\PayloadData\HookConfig;

use stdClass;

class HookConfigDataTest extends TestCase {
   use PingPayloadTrait;
   use PayloadDataTrait;
   
   public function pingEventHookConfigObjectProvider() : array {
      $hookObj = static::getExpectedPingRequestObjectValue('hook');
      if (!is_object($hookObj)) {
         trigger_error("the 'hook' property of ping request object was not an object type as expected",E_USER_ERROR);
      }
      if (!property_exists($hookObj, 'config')) {
         trigger_error("missing 'config' property from 'hook' property of ping request object",E_USER_ERROR);
      }
      if (!is_object($hookObj->config)) {
         trigger_error("the 'config' property was not an object as expected in 'hook' property of ping request object",E_USER_ERROR);
      }
      return [
         [$hookObj->config],
      ];
   }
   
   /**
    * @dataProvider pingEventHookConfigObjectProvider
    * @covers \Webhook\PayloadData\HookConfig::populateFromObject()
    */
   public function testPingEventHookConfig(stdClass $hook_config_obj) {
      $hookConfig = new HookConfig;
      $hookConfig->populateFromObject($hook_config_obj);
      
      $this->payloadObjectEqualityTests($hook_config_obj, $hookConfig);
   }
   
   
   
   
   
   
   
}