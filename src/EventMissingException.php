<?php
namespace Webhook;

class EventMissingException extends InvalidRequestException {
   const REASON_TEXT = "missing gitHubEvent";
   public function __construct() {
      parent::__construct(static::REASON_TEXT,static::REASON_CODE_MISSING_EVENT);
   }
}