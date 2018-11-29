<?php
namespace Webhook;

interface PopulateListener {
   /**
    * Indicates that the populating of this object is complete.
    * @return void
    */
   public function populateComplete();
   
}