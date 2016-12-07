<?php
namespace Webhook\Payload;

use Webhook\Payload;

class Event extends Payload {
   /**
    * @var string
    */
   private $_event;
   public function getEvent(): string {
      return $this->_event;
   }
   
   /**
    * @var array
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
      
      foreach((array) $input as $p=>$v) {
         if (in_array($p,$pubProp,true)) continue;
         $this->_payloadData[$p] = $v;
      }
      unset($p);
      unset($v);
      
      
   }
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
}