<?php
namespace Webhook;

/**
 * Trait to facilitate populating an object's properties from input. 
 */
trait PopulatorTrait {
   
   /**
    * Instantiates a new object of this class; assigning values to any public properties of the new object
    *    corresponding element values with matching keys names of the specified map array. If this class
    *    implements the \Webhook\ 
    *
    * @param array $input
    * @return object
    */
   public function populateFromArray(array $input) {
      $object = $this;
      foreach($object as $p=>$v) {
         if (isset($input[$p])) {
            $object->$p = $input[$p];
         }
      }
      if ($object instanceof PopulateComplete) $object->populateComplete();
      return $object;
   }
   
   /**
    * Instantiates a new object of this class; assigning values to any public properties of the new object
    *    corresponding matching public properties with matching names of the specified map object.
    * 
    * @param object $input 
    * @return object
    */
   public function populateFromObject($input) {
      
      if (!is_object($input)) $input=new \stdClass;
      
      $object = $this;
      
      $mapProp = (new \ReflectionObject($input))->getProperties(\ReflectionProperty::IS_PUBLIC);
      $mapList = [];
      foreach($mapProp as $v) $mapList[]=$mapProp->$v;
      
      foreach($object as $p=>$v) {
         
         if (in_array($p,$mapList)) {
            $object->$p = $input->$p;
         }
         
      }
      if ($object instanceof PopulateComplete) $object->populateComplete();
      return $object;
      
   }
   
}