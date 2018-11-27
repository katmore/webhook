<?php
namespace Webhook;

abstract class CallbackRule {

   public function __toString() {
      return $this->getValue();
   }
   /**
    * @var string
    */
   protected $_ruleValue;
   public function getValue():string {
      if(
            ( !is_array( $this->_ruleValue ) ) &&
            ( ( !is_object( $this->_ruleValue ) && settype( $this->_ruleValue, 'string' ) !== false ) ||
                  ( is_object( $this->_ruleValue ) && method_exists( $this->_ruleValue, '__toString' ) ) )
            )
      {
         return (string) $this->_ruleValue;
      }
      return "";
   }
   abstract public function __construct(string $ruleValue);
}