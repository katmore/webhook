<?php
namespace Webhook;

abstract class CallbackRule {

   /**
    * Provides the rule value.
    * @return string
    */
   public function __toString() {
      return $this->getValue();
   }
   /**
    * @var string rule value
    * @private
    */
   protected $_ruleValue;
   /**
    * Provides the rule value.
    * @return string
    */
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
   /**
    * @param string $ruleValue the rule value
    */
   abstract public function __construct(string $ruleValue);
}