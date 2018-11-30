<?php
namespace Webhook;

class MessageBodyInvalidException extends InvalidRequestException {
   const REASON_TEXT = "messageBody is invalid: must be a JSON object or urlencoded string";
   public function __construct() {
      parent::__construct(static::REASON_TEXT,static::REASON_CODE_MESSAGE_BODY_INVALID);
   }
}