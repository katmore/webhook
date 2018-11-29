<?php
namespace Webhook;
/**
 * Interface for populating an object's data from input.
 */
interface Populatable {
   /**
    * Populates the current object instance by mapping values from an assoc array.
    * @param array $input assoc array input
    * @return object populated object
    */
   public function populateFromArray(array $input);
   /**
    * Populates the current object instance by mapping values from another object.
    * @param object $input object input
    * @return object populated object
    */
   public function populateFromObject($input);
}