<?php
declare(strict_types=1);
namespace Webhook\Tests\Unit;

use PHPUnit\Framework\TestCase;

use Webhook\TestCase\PushPayloadTrait;
use Webhook\TestCase\PayloadDataTrait;

use Webhook\PayloadData\Repository;

use stdClass;

class RepositoryDataTest extends TestCase {
   use PushPayloadTrait;
   use PayloadDataTrait;
   
   public function pushEventRepositoryObjectProvider() : array {
      $repositoryObj = static::getExpectedPushRequestObjectValue('repository');
      if (!is_object($repositoryObj)) {
         trigger_error("the 'repository' property of push request object was not an object type as expected",E_USER_ERROR);
      }
      return [
         [$repositoryObj],
      ];
   }
   
   /**
    * @dataProvider pushEventRepositoryObjectProvider
    * @covers \Webhook\PayloadData\Repository::populateComplete()
    * @covers \Webhook\PayloadData\Repository::populateFromObject()
    */
   public function testPushEventRepository(stdClass $repository_obj) {
      $repository = new Repository;
      $repository->populateFromObject($repository_obj);
      
      $this->payloadObjectEqualityTests($repository_obj, $repository);
   }
   
   
   
   
   
   
   
}