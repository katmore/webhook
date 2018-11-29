<?php
declare(strict_types=1);
namespace Webhook\Tests\Unit;

use PHPUnit\Framework\TestCase;

use Webhook\TestCase\PushPayloadTrait;
use Webhook\TestCase\PayloadDataTrait;

use Webhook\PayloadData\Organization;

use stdClass;

class OrganizationDataTest extends TestCase {
   use PushPayloadTrait;
   use PayloadDataTrait;
   
   public function pushEventOrganizationObjectProvider() : array {
      $organizationObj = static::getExpectedPushRequestObjectValue('organization');
      if (!is_object($organizationObj)) {
         trigger_error("the 'organization' property of push request object was not an object type as expected",E_USER_ERROR);
      }
      return [
         [$organizationObj],
      ];
   }
   
   /**
    * @dataProvider pushEventOrganizationObjectProvider
    */
   public function testPushEventOrganization(stdClass $organization_obj) {
      $organization = new Organization;
      $organization->populateFromObject($organization_obj);
      
      $this->payloadObjectEqualityTests($organization_obj, $organization);
   }
   
   
   
   
   
   
   
}