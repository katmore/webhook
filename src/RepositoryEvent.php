<?php
namespace Webhook;

class RepositoryEvent {
   
   private $_RepositoryUrl;
   public function getRepositoryUrl() {
      return $this->_RepositoryUrl;
   }
   
   private $_GitHubEvent;
   public function getGitHubEvent() {
      return $this->_GitHubEvent;
   }
   
   
   public function isHubSignatureValid(string $HubSignature,string $RawPayload) {
      list($algo, $hash) = explode('=', $HubSignature, 2) + ['', ''];
      if ($hash !== hash_hmac($algo, $RawPayload, $this->_HubSecret)) {
         return false;
      }
      return true;
   }
   
   private $_HubSecret;
   
   /**
    * @param string $HubSecret
    * @param string $RepositoryUrl
    * @param string $GitHubEvent 
    */
   public function __construct(string $HubSecret,string $RepositoryUrl=null,string $GitHubEvent=null) {
      $this->_HubSecret = $HubSecret;
   }
   
}