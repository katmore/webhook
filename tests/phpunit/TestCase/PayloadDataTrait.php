<?php
declare(strict_types=1);
namespace Webhook\TestCase;

use stdClass;

use Webhook\Populatable;
use ReflectionClass;
use PhpDocReader\PhpDocReader;
use InvalidArgumentException;

trait PayloadDataTrait {
   
   public function payloadIterableTests(  $object_node,  $data_node, string $node_name="") {
      
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
            $this->assertArrayHasKey($key, $object_node,"Failed on: $node_name.$key");
            $object_node_value = $object_node[$key];
         } else {
            $this->assertObjectHasAttribute($key, $object_node,"Failed on: $node_name.$key");
            $object_node_value = $object_node->$key;
         }
         if (is_scalar($val)) {
            $this->assertEquals($object_node_value, $val,"Failed on: $node_name.$key");
         } else if (is_array($val)) {
            $this->assertInternalType('array', $object_node_value,"Failed on: $node_name.$key");
            $this->payloadIterableTests($object_node_value, $val,$node_name.".$key");
         } else if (is_object($val)) {
            $this->assertInternalType('object', $object_node_value,"Failed on: $node_name.$key");
            $this->payloadIterableTests($object_node_value, $val,$node_name.".$key");
         }
      }
      unset($key);
      unset($val);
         
   }
   
   public function payloadObjectEqualityTests(stdClass $object,Populatable $data) {
      $r = new ReflectionClass($data);
      $reader = new PhpDocReader();
      foreach($data as $prop=>$val) {
         //"Failed on: $node_name.$key"
         $node_name = get_class($data);
         $this->assertObjectHasAttribute($prop,$object,"Failed on: $node_name.$prop");
         if (is_scalar($val)) {
            $this->assertAttributeEquals($object->$prop, $prop, $data,"Failed on: $node_name.$prop");
         } else if (is_object($val)) {
            $this->assertAttributeInternalType('object', $prop, $object,"Failed on: $node_name.$prop");
            $rp = $r->getProperty($prop);
            $propClass = $reader->getPropertyClass($rp);
            $this->assertAttributeInstanceOf($propClass, $prop, $data,"Failed on: $node_name.$prop");
            $this->payloadIterableTests($object->$prop, $val,"$node_name.$prop");
         } else if (is_array($val)) {
            $this->assertAttributeInternalType('array', $prop, $object,"Failed on: $node_name.$prop");
            $this->payloadIterableTests($object->$prop, $val,"$node_name.$prop");
         }
      }
   }
}