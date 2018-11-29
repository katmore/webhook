<?php
declare(strict_types=1);
namespace Webhook\Tests\Unit;

use PHPUnit\Framework\TestCase;

use Webhook\TestCase\PushPayloadTrait;
use Webhook\TestCase\PayloadDataTrait;

use Webhook\PayloadData\RepositoryOwner;

use stdClass;

class RepositoryOwnerDataTest extends TestCase {
   use PushPayloadTrait;
   use PayloadDataTrait;
   
   public function pushEventRepositoryOwnerObjectProvider() : array {
      $repositoryObj = static::getExpectedPushRequestObjectValue('repository');
      if (!is_object($repositoryObj)) {
         trigger_error("the 'repository' property of push request object was not an object type as expected",E_USER_ERROR);
      }
      if (!property_exists($repositoryObj, 'owner')) {
         trigger_error("missing 'owner' property from 'repository' property of push request object",E_USER_ERROR);
      }
      if (!is_object($repositoryObj->owner)) {
         trigger_error("the 'owner' property was not an object as expected in 'repository' property of push request object",E_USER_ERROR);
      }
      return [
         [$repositoryObj->owner],
      ];
   }
   
   /**
    * @dataProvider pushEventRepositoryOwnerObjectProvider
    */
   public function testPushEventRepositoryOwner(stdClass $repository_owner_obj) {
      $repositoryOwner = new RepositoryOwner;
      $repositoryOwner->populateFromObject($repository_owner_obj);
      
      $this->payloadObjectEqualityTests($repository_owner_obj, $repositoryOwner);
   }
   
   
   
   
   
   
   
}