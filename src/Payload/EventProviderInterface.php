<?php
namespace Webhook\Payload;

interface EventProviderInterface {
  
   public function toEvent() : Event;
   
}