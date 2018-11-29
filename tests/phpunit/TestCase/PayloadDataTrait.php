<?php
declare(strict_types=1);
namespace Webhook\TestCase;

use stdClass;

use Webhook\Populatable;
use ReflectionClass;
use PhpDocReader\PhpDocReader;

trait PayloadDataTrait {
   
   public function payloadIterableTests( iterable $object_node, iterable $data_node) {
      
      $this->assertInternalType(gettype($data_node), $object_node);
      
      if (is_array($data_node)) {
         $this->assertCount(count($data_node),$object_node);
      }
      
      foreach($data_node as $key=>$val) {
         $this->assertArrayHasKey($key, $object_node);
         if (is_scalar($val)) {
            $this->assertEquals($object_node[$key], $val);
         } else if (is_array($val)) {
            $this->assertInternalType('array', $object_node[$key]);
            $this->payloadIterableTests($object_node[$key], $val);
         } else if (is_object($val)) {
            $this->assertInternalType('object', $object_node[$key]);
            $this->payloadIterableTests($object_node[$key], $val);
         }
      }
      unset($key);
      unset($val);
   }
   
   public function payloadObjectEqualityTests(stdClass $object,Populatable $data) {
      $r = new ReflectionClass($data);
      $reader = new PhpDocReader();
      foreach($data as $prop=>$val) {
         $this->assertObjectHasAttribute($prop, $object);
         if (is_scalar($val)) {
            $this->assertAttributeEquals($object->$prop, $prop, $data);
         } else if (is_object($val)) {
            $this->assertAttributeInternalType('object', $prop, $object);
            $rp = $r->getProperty($prop);
            $propClass = $reader->getPropertyClass($rp);
            $this->assertAttributeInstanceOf($propClass, $prop, $data);
         } else if (is_array($val)) {
            $this->assertAttributeInternalType('array', $prop, $object);
            $this->payloadIterableTests($object->$prop, $val);
         }
      }
   }
}