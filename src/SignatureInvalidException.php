<?php
namespace Webhook;

class SignatureInvalidException extends InvalidRequestException {
   const REASON_TEXT = "invalid hubSignature";
   public function __construct() {
      parent::__construct(static::REASON_TEXT,static::REASON_CODE_SIGNATURE_INVALID);
   }
}