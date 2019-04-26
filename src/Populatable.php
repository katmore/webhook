<?php
namespace Webhook;
/**
 * Interface for populating an object's data from input.
 */
interface Populatable {
   public function populateFromArray(array $input);
   public function populateFromObject($input);
}