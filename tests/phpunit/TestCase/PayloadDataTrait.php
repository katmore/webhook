<?php
declare(strict_types=1);
namespace Webhook\TestCase;

use stdClass;

use Webhook\Populatable;
use ReflectionClass;
use PhpDocReader\PhpDocReader;
use InvalidArgumentException;

trait PayloadDataTrait {
   
   public function payloadIterableTests(  $object_node,  $data_node) {
      
      if (!is_object($object_node) && !is_array($object_node)) {
         throw new InvalidArgumentException('$object_node argument must be an object or array type, instead got: '.gettype($object_node));
      }
      
      if (!is_object($data_node) && !is_array($data_node)) {
         throw new InvalidArgumentException('$$data_node argument must be an object or array type, instead got: '.gettype($data_node));
      }
      
      $this->assertInternalType(gettype($data_node), $object_node);
      
      if (is_array($data_node)) {
         $this->assertCount(count($data_node),$object_node);
      }
      
      foreach($data_node as $key=>$val) {
         if (is_array($data_node)) {
            $this->assertArrayHasKey($key, $object_node);
            $object_node_value = $object_node[$key];
         } else {
            $this->assertObjectHasAttribute($key, $object_node);
            $object_node_value = $object_node->$key;
         }
         if (is_scalar($val)) {
            $this->assertEquals($object_node_value, $val);
         } else if (is_array($val)) {
            $this->assertInternalType('array', $object_node_value);
            $this->payloadIterableTests($object_node_value, $val);
         } else if (is_object($val)) {
            $this->assertInternalType('object', $object_node_value);
            $this->payloadIterableTests($object_node_value, $val);
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
            $this->payloadIterableTests($object->$prop, $val);
         } else if (is_array($val)) {
            $this->assertAttributeInternalType('array', $prop, $object);
            $this->payloadIterableTests($object->$prop, $val);
         }
      }
   }
}