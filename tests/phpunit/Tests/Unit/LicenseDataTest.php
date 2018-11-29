<?php
declare(strict_types=1);
namespace Webhook\Tests\Unit;

use PHPUnit\Framework\TestCase;

use Webhook\TestCase\PingPayloadTrait;
use Webhook\TestCase\PayloadDataTrait;

use Webhook\PayloadData\License;

use stdClass;

class LicenseDataTest extends TestCase {
   use PingPayloadTrait;
   use PayloadDataTrait;
   
   public function pingEventLicenseObjectProvider() : array {
      $repositoryObj = static::getExpectedPingRequestObjectValue('repository');
      if (!is_object($repositoryObj)) {
         trigger_error("the 'repository' property of ping request object was not an object type as expected",E_USER_ERROR);
      }
      if (!property_exists($repositoryObj, 'license')) {
         trigger_error("missing 'license' property from 'repository' property of ping request object",E_USER_ERROR);
      }
      if (!is_object($repositoryObj->license)) {
         trigger_error("the 'license' property was not an object as expected in 'repository' property of ping request object",E_USER_ERROR);
      }
      return [
         [$repositoryObj->license],
      ];
   }
   
   /**
    * @dataProvider pingEventLicenseObjectProvider
    * @covers \Webhook\PayloadData\License::populateFromObject()
    */
   public function testPingEventLicense(stdClass $license_obj) {
      $license = new License;
      $license->populateFromObject($license_obj);
      
      $this->payloadObjectEqualityTests($license_obj, $license);
   }
   
   
   
   
   
   
   
}