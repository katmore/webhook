<?php
declare(strict_types=1);
namespace Webhook\Tests\Unit;

use PHPUnit\Framework\TestCase;

use Webhook\TestCase\PushPayloadTrait;
use Webhook\TestCase\PayloadDataTrait;

use Webhook\PayloadData\GitUser;

use stdClass;

class GitUserDataTest extends TestCase {
   use PushPayloadTrait;
   use PayloadDataTrait;
   
   public function pushEventGitUserProvider() : array {
      $headCommitObj = static::getExpectedPushRequestObjectValue('head_commit');
      if (!is_object($headCommitObj)) {
         trigger_error("the 'head_commit' property of push request object was not an object type as expected",E_USER_ERROR);
      }
      if (!property_exists($headCommitObj, 'author')) {
         trigger_error("missing 'author' property from 'head_commit' property of push request object",E_USER_ERROR);
      }
      if (!property_exists($headCommitObj, 'committer')) {
         trigger_error("missing 'committer' property from 'head_commit' property of push request object",E_USER_ERROR);
      }
      if (!is_object($headCommitObj->author)) {
         trigger_error("the 'author' property was not an object as expected in 'head_commit' property of push request object",E_USER_ERROR);
      }
      if (!is_object($headCommitObj->committer)) {
         trigger_error("the 'committer' property was not an object as expected in 'head_commit' property of push request object",E_USER_ERROR);
      }
      return [
         [$headCommitObj->author],
         [$headCommitObj->committer],
      ];
   }
   
   /**
    * @dataProvider pushEventGitUserProvider
    */
   public function testPushEventGitUser(stdClass $git_user_obj) {
      $gitUser = new GitUser;
      $gitUser->populateFromObject($git_user_obj);
      
      $this->payloadObjectEqualityTests($git_user_obj, $gitUser);
   }
   
   
   
   
   
   
   
}