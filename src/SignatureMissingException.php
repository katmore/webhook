<?php
namespace Webhook;

class SignatureMissingException extends InvalidRequestException {
   const REASON_TEXT = "missing hubSignature";
   public function __construct() {
      parent::__construct(static::REASON_TEXT,static::REASON_CODE_MISSING_SIGNATURE);
   }
}