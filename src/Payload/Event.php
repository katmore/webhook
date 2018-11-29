<?php
namespace Webhook\Payload;

use Webhook\Payload;

class Event extends Payload implements EventProviderInterface {
   
   /**
    * Populates a "generic" Payload <b>Event</b> object corresponding to this event.
    * @return \Webhook\Payload\Event
    */
   public function toEvent() : Event {
      return $this;
   }
   
   /**
    * @var string event name
    * @private
    */
   private $_event;
   /**
    * Provides the event name.
    * @return string
    */
   public function getEvent(): string {
      return $this->_event;
   }
   
   /**
    * @var array assoc array of payload data
    * @private
    */
   private $_payloadData=[];
   
   /**
    * @return array assoc array of payload data.
    * @see https://developer.github.com/v3/activity/events/types/ for assoc key's and potential values.
    */
   public function getPayloadData() :array {
      return $this->_payloadData;
   }
   
   /**
    * @param object $input payload input
    * @param string $gitHubEvent gitHubEvent name
    */
   public function __construct($input,string $gitHubEvent) {
      
      parent::__construct($input);
      
      $this->_event = $gitHubEvent;
      
      $pubProp = [];
      foreach((new \ReflectionObject($input))->getProperties(\ReflectionProperty::IS_PUBLIC) as $v) {
         $pubProp[]=$v->getName();
      }
      unset($v);
      
      $this->_payloadData = json_decode(json_encode($input),true);
      
      
   }
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
}