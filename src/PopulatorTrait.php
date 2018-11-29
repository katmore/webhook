<?php
namespace Webhook;

/**
 * Trait to facilitate populating an object's properties from input. 
 */
trait PopulatorTrait {
   
   /**
    * Populates this object by assigning values to any public properties of the new object
    *    corresponding matching public properties with matching names of the specified map object.
    * 
    * @param object $input 
    * @return object populated object
    */
   public function populateFromObject($input) {
      
      if (!is_object($input)) $input=new \stdClass;
      
      $object = $this;
      
      $mapProp = (new \ReflectionObject($input))->getProperties(\ReflectionProperty::IS_PUBLIC);
      
      $mapList = [];
      
      foreach($mapProp as $v) $mapList[]=$v->getName();
      
      unset($v);
      
      foreach($object as $p=>$v) {
         
         if (in_array($p,$mapList,true)) {
            $object->$p = $input->$p;
         }
         
      }
      
      if ($object instanceof PopulateListener) $object->populateComplete();
      
      return $object;
      
   }
   
}