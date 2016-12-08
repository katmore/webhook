<?php
return(function() {
   if (0!==($exitStatus=($installer = new class() {
      
      const ME = 'add-endpoint.php';
      
      const ME_LABEL = 'Webhook End-Point Service Installer';
      
      const ME_URL = 'https://github.com/katmore/webhook/blob/master/bin/add-endpoint.php';
       
      const COPYRIGHT = '(c) 2016 Doug Bird. All Rights Reserved.';
      
      const HELP_LABEL = "Webhook Project: https://github.com/katmore/webhook";
       
      const USAGE = '[--help [--quiet | --verbose] [--non-interactive] [--repo-url=<Repo URL> [--repo-path=<path to local repository> [--repo-type=<"svn"|"git">] [--hub-secret=<Github Webhook Secret>]]] [--autoload-path=<path to vendor/autoload.php> [--web-endpoint-dir=<path to web/endpoint directory>] [--endpoint-script=<endpoint script name>]]]';
      
      const FALLBACK_REPO_TYPE = 'git';
      
      private static function _getFallbackAutoload() :string{
         return __DIR__ . "/../vendor/autoload.php";
      }
      
      private static function _getFallbackWebEndpointDir() :string {
         return __DIR__ . "/../web/endpoint";
      }
      
      /**
       * @return void
       * @static
       */
      public static function showUsage() {
         echo "Usage: ".PHP_EOL;
         echo "   ".SELF::ME." ".self::USAGE.\PHP_EOL;
      }
      
      public static function showHelp() {
         echo self::HELP_LABEL.PHP_EOL;
         $fallbackRepoType = self::FALLBACK_REPO_TYPE;
         if ($fallbackAutoload = realpath(self::_getFallbackAutoload())) {
            $autoloadHelp = <<<"EOT"
--autoload-path (optional)
   Path to class autoloader (ie: vendor/autoload.php)
   default value: "$fallbackAutoload"
EOT;
         } else {
            $autoloadHelp = <<<"EOT"
--autoload-path (required)
   Path to class autoloader (ie: vendor/autoload.php)
EOT;
         }
            
         if ($fallbackWebEndpointDir = realpath(self::_getFallbackAutoload())) {
            $webEndpointDirHelp = <<<"EOT"
--web-endpoint-dir (optional)
   Path to web endpoint directory (ie: web/endpoint)
   default value: "$fallbackWebEndpointDir"
EOT;
         } else {
            $webEndpointDirHelp = <<<"EOT"
--web-endpoint-dir (required)
   Path to web endpoint directory (ie: web/endpoint)
EOT;
         }
            
         echo <<<"EOT"
Output Control:
--help
   Enable "help mode": outputs this message then exits.

--quiet
   Enable "quiet mode": only output will be errors to STDERR.

--verbose (ignored if --quiet switch is present)
   Enable "verbose mode": outputs extra information (such as full system paths, etc.)

--non-interactive
   Enable "non interactive mode": no input prompts will be issued

Endpoint Configuration:
--repo-url (required in "non interactive mode")
   Remote repository URL

--hub-secret (required in "non interactive mode")
   Shared Webhook Secret

--repo-type (optional)
   Local repository type; either: "git" or "svn"
   default value: "$fallbackRepoType"

$autoloadHelp

$webEndpointDirHelp

--endpoint-script (optional)
   Name of webservice endpoint script to create in web-endpoint-dir (ie: reponame.php)
   default value: <repository 'name'>.php
EOT;
         echo PHP_EOL;
      }
       
      /**
       * @return void
       * @static
       */
      private static function _showIntro() {
         echo self::ME_LABEL."\n".self::COPYRIGHT.\PHP_EOL;
      }
       
      /**
       * @return void
       * @param string[]
       * @static
       */
      private static function _showErrLine(array $strLines) {
         $stderr = fopen('php://stderr', 'w');
         foreach ($strLines as $line) fwrite($stderr, "$line".\PHP_EOL);
         fclose($stderr);
      }
      /**
       * @return void
       * @param string[]
       * @static
       */
      private static function _showLine(array $strLines) {
         foreach ($strLines as $line) echo "$line".\PHP_EOL;
      }
       
      /**
       * @var int
       */
      private $_exitStatus=0;
       
      /**
       * @return int Exit status
       */
      public function getExitStatus() :int { return $this->_exitStatus; }
       
      /**
       * @var bool
       */
      private $_quiet;
       
      /**
       * @var bool
       */
      private $_verbose;
       
      /**
       * @var bool
       */
      private $_nonInteractive;
      
      private static function _getEndpointSrc(string $repoUrl,string $repoPath,string $repoType,string $hubSecret,string $autoloadPath) {
         //require __DIR__."/../vendor/autoload.php";
         $src = <<<'EOT'
<?php
/**
 * Webservice End-Point for responding to GitHub Webhook Events
 * 
 * Remote Repository URL: %repo-url%
 * 
 * Generated at %generated-time% by %ME_LABEL% (%ME_URL%) 
 * 
 */
use Webhook\Callback;
use Webhook\Request;
use Webhook\InvalidRequest;
use Webhook\Payload;
use Webhook\UrlCallbackRule;

require "%autoload-path%";

$config['RepoUrl'] = '%repo-url%';
$config['Secret'] = base64_decode('%base64-hub-secret%');
$config['RepoPath'] = '%repo-path%';
$config['RepoType'] = '%repo-type%';

$callback = new Callback($config['Secret'],function(Payload $payload ) use (&$config) {
   
   if ($payload instanceof Payload\PushEvent) {
      
      if ($config['RepoType']=='git') {
         
         $line = exec('cd '.$config['RepoPath'].' && git pull 2>&1',$out,$ret);
      
      } else if ($config['RepoType']=='svn') {
         
         $line = exec('svn up '.$config['RepoPath'].' 2>&1',$out,$ret);
      
      }
      
      header('Content-Type:text/plain');
      if ($ret!=0) http_response_code(500);
      
      echo implode("\n",$out)."\n";
      unset($out);
      
   } elseif ($payload instanceof Payload\PingEvent) {
      
      if ($config['RepoType']=='git') {
          
         $line = exec('cd '.$config['RepoPath'].' && git status 2>&1',$out,$ret);
      
      } else if ($config['RepoType']=='svn') {
          
         $line = exec('svn info '.$config['RepoPath'].' 2>&1',$out,$ret);
      
      }
      
      header('Content-Type:text/plain');
      if ($ret!=0) http_response_code(500);
      
      echo implode("\n",$out)."\n";
      unset($out);
      
   } else {
      
      $payloadType = gettype($payload);
      if ($payloadType==='object') {
         $payloadType = get_class(payload);
      }
      $data = ['PayloadType'=>payloadType];
      if ($payload instanceof Payload\Event) {
         $payloadData = $payload->getPayloadData();
         switch ($payload->getEvent()) {
             case 'IssuesEvent':
                $data['IssuesEvent-Action'] = $payloadData['action'];
                $data['Issue-Url'] = $payloadData['issue']['url'];
                $data['Issue-Title'] = $payloadData['issue']['title'];
                $data['Summary'] = "The issue was {$payloadData['action']}";
                if ($payloadData['action']=='assigned') {
                   $data['Summary'] .=" to {$payloadData['assignee']['html_url']}";
                } elseif ($payloadData['action']=='unassigned') {
                   $data['Summary'] .=" from {$payloadData['assignee']['html_url']}";
                }
                $data['Summary'] .=".";
                break;
             case 'IssueCommentEvent':
                $data['IssueCommentEvent-Action'] = $payloadData['action'];
                $data['Issue-Url'] = $payloadData['issue']['url'];
                $data['Issue-Title'] = $payloadData['issue']['title'];
                $data['Summary'] = "A comment was {$payloadData['action']}";
                $data['Summary'] .= " by {$payloadData['comment']['user']['html_url']}.";
                $data['CommentBody'] = $payloadData['comment']['body'];
                break;
             /*----------------------------------------------
              * Add more 'case' statements for other Event Types you wish to handle
              * below.
              *
              * See: https://developer.github.com/v3/activity/events/types
              *  for event types and Payloads
              */
             /*uncomment the following following and edit the case block with the Github event name to 'handle' a different event*/
             //case 'SomeEvent':
             //    $data['Github-Event'] = $payload->getEvent();
             //    /*uncomment below and edit to perform a local system command*/
             //    //exec('#do-something');
             //    /*uncomment below and edit the $notifyTo to send an Email notification*/
             //    //mail($notifyTo="someone@example.com",$payload->getEvent()." on ".$payloadData['repository']['html_url'],json_encode($data,\JSON_PRETTY_PRINT));
             //
             default:
                $data['Payload'] = $payloadData;
                $data['UnhandledEvent'] = $payload->getEvent();
         }
         
         if (!headers_sent() && empty(error_get_last())) {
            header('Content-Type:application/json');
         }
         echo json_encode($data);
      }
   }
},new UrlCallbackRule($config['RepoUrl']));

register_shutdown_function(function() {
   $last_error = error_get_last();
   if ($last_error && isset($last_error['type'])) {
      if (!in_array($last_error['type'],[E_DEPRECATED,E_WARNING,E_NOTICE,E_RECOVERABLE_ERROR,E_USER_DEPRECATED,E_USER_NOTICE,E_USER_WARNING])) {
         http_response_code (500);
      }
   }
});

try {
   $request = Request::load(
         file_get_contents('php://input'),
         isset($_SERVER)?$_SERVER:[]
         );
   if ($request->getRequestMethod()!=='POST') {
      throw new InvalidRequest("requestMethod must be POST");
   }
   $callback->validateRequest($request->getHubSignature(), $request->getMessageBody(), $request->getPayload());
} catch(InvalidRequest $e) {
   http_response_code(500);
   echo "Invalid Request: ".$e->getMessage();
}
EOT;
         $src=str_replace("%repo-url%",$repoUrl,$src);
         $src=str_replace("%generated-time%",date("c"),$src);
         $src=str_replace("%ME_LABEL%",self::ME_LABEL,$src);
         $src=str_replace("%ME_URL%",self::ME_URL,$src);
         $src=str_replace("%ME_URL%",self::ME_URL,$src);
         //autoload-path
         $src=str_replace("%autoload-path%",$autoloadPath,$src);
         //base64-hub-secret
         $src=str_replace("%base64-hub-secret%",base64_encode($hubSecret),$src);
         //repo-path
         $src=str_replace("%repo-path%",$repoPath,$src);
         //repo-type
         $src=str_replace("%repo-type%",$repoType,$src);
         
         return $src;
      }
      
      public function __construct() {

         if (isset(getopt("",["help",])['help'])) {
            self::_showIntro();
            self::showHelp();
            return;
         }

         $this->_verbose = false;
         if (!($this->_quiet=isset(getopt("",["quiet",])['quiet']))) {
            $this->_verbose=isset(getopt("",["verbose",])['verbose']);
         }

         $this->_nonInteractive = isset(getopt("",["non-interactive",])['non-interactive']);

         $this->_quiet || self::_showIntro();
         
         //(string $repoUrl,string $repoPath,string $repoType,string $hubSecret,string $event,string $autoloadPath)
         $endpointCfg = [
            'repo-url'=>'',
            'repo-path'=>'',
            'repo-type'=>self::FALLBACK_REPO_TYPE,
            'hub-secret'=>'',
            'autoload-path'=>(string) realpath(self::_getFallbackAutoload()),
            'web-endpoint-dir'=>(string) realpath(self::_getFallbackWebEndpointDir()),
            'endpoint-script'=>'',
         ];
         
         foreach($endpointCfg as $k=>&$v) {
            if (!empty(getopt("",["$k::",])[$k])) $v=getopt("",["$k::",])[$k];
         }
         unset($k);
         unset($v);
         unset($opt);
         
         $required = ['repo-url','repo-path','repo-type','hub-secret','autoload-path','web-endpoint-dir'];
         
         if (!$this->_nonInteractive) {
            $missing = [];
            foreach($required as $k) {
               if (empty($endpointCfg[$k])) {
                  $missing[]=$k;
               }
            }
            unset($k);
            if (count($missing)) {
               echo PHP_EOL."Interactive mode: provide configuration details or Ctrl+C to exit...".PHP_EOL;
               foreach($required as $k) {
                  for($i=0;$i<5;$i++) {
                     $default="";
                     if (!empty($endpointCfg[$k])) $default = " ({$endpointCfg[$k]})";
                     $v = readline("Enter the $k$default: ");
                     if (!empty($v)) {
                        $endpointCfg[$k]=$v;
                        break 1;
                     }
                     if (!empty($endpointCfg[$k])) {
                        break 1;
                     }
                  }
                  if (empty($endpointCfg[$k])) {
                     self::_showErrLine([self::ME. ": (ERROR) failed to get value for '$k' after $i tries"]);
                     return $this->_exitStatus=1;
                  }
               }
            }
            unset($missing);
            unset($default);
            unset($k);
            unset($v);
            unset($i);
         }
         $missing = [];
         foreach($required as $k) {
            if (empty($endpointCfg[$k])) {
               $missing[]=$k;
               self::_showErrLine([self::ME . ": (ERROR) missing required value for ".$k]);
            }
         }
         unset($k);
         if (count($missing)) {
            return $this->_exitStatus = 1;
         }
         unset($missing);
         
         $error = false;
         if (!realpath($endpointCfg['autoload-path']) || !is_file($endpointCfg['autoload-path']) || !is_readable($endpointCfg['autoload-path'])) {
            self::_showErrLine([self::ME . ": (ERROR) 'autoload-path' did not resolve to readable file: {$endpointCfg['autoload-path']}"]);
            $error = true;
         }
         
         if (!realpath($endpointCfg['web-endpoint-dir']) || !is_dir($endpointCfg['web-endpoint-dir']) || !is_writable($endpointCfg['web-endpoint-dir'])) {
            self::_showErrLine([self::ME . ": (ERROR) 'web-endpoint-dir' did not resolve to a writeable directory: {$endpointCfg['web-endpoint-dir']}"]);
            $error = true;
         }
         
         if (!realpath($endpointCfg['repo-path']) || !is_dir($endpointCfg['repo-path']) || !is_writable($endpointCfg['repo-path'])) {
            self::_showErrLine([self::ME . ": (ERROR) 'repo-path' did not resolve to a writeable directory: {$endpointCfg['repo-path']}"]);
            $error = true;
         }
         
         if (empty(parse_url($endpointCfg['repo-url'],\PHP_URL_SCHEME))) {
            self::_showErrLine([self::ME . ": (ERROR) 'repo-url' is missing a URL-SCHEME and is thus an invalid remote repository URL: {$endpointCfg['repo-url']}"]);
            $error = true;
         } else
         if (empty(parse_url($endpointCfg['repo-url'],\PHP_URL_HOST))) {
            self::_showErrLine([self::ME . ": (ERROR) 'repo-url' is missing a URL-HOST and is thus an invalid remote repository URL: {$endpointCfg['repo-url']}"]);
            $error = true;
         } else
         if (empty(parse_url($endpointCfg['repo-url'],\PHP_URL_PATH))) {
            self::_showErrLine([self::ME . ": (ERROR) 'repo-url' is missing a URL-PATH and is thus an invalid remote repository URL: {$endpointCfg['repo-url']}"]);
            $error = true;
         } else {
            $path = explode("/",parse_url($endpointCfg['repo-url'],\PHP_URL_PATH));
            if (count($path)<3) {
               self::_showErrLine([self::ME . ": (ERROR) did not recognize a valid repository name in the 'repo-url' : {$endpointCfg['repo-url']}"]);
               $error = true;
            }
         }
         
         if (!in_array($endpointCfg['repo-type'],$validRepoTypes=['git','svn'],true)) {
            self::_showErrLine([self::ME . ": (ERROR) the 'repo-type' \"{$endpointCfg['repo-type']}\" is invalid, it must be one of the following: ".implode(", ",$validRepoTypes)]);
            $error = true;
         }
         
         if ($error) return $this->_exitStatus = 1;
         
         if (!empty(getopt("",["endpoint-script::",])['endpoint-script'])) {
            $webEndpointScript=getopt("",["endpoint-script::",])['endpoint-script'];
         } else {
            $webEndpointScript = pathinfo(parse_url($endpointCfg['repo-url'],\PHP_URL_PATH),\PATHINFO_DIRNAME)."/".pathinfo(parse_url($endpointCfg['repo-url'],\PHP_URL_PATH),\PATHINFO_BASENAME);
         }
         if (substr($webEndpointScript,0,4)!='.php') {
            $webEndpointScript.='.php';
         }
         $webEndpointPath = "{$endpointCfg['web-endpoint-dir']}/$webEndpointScript";
         
         $scriptDir = pathinfo($webEndpointPath,\PATHINFO_DIRNAME);
         if (!is_dir($scriptDir)) {
            if (!mkdir($scriptDir,0777,true)) {
               self::_showErrLine(["could not create subdir for endpoint-script: $scriptDir"]);
            }
         }
         
         $this->_verbose && self::_showLine(["web endpoint path: $webEndpointPath"]);
         
         $this->_quiet || self::_showLine(['generating endpoint...']);
         
         file_put_contents($webEndpointPath, self::_getEndpointSrc( $endpointCfg['repo-url'], $endpointCfg['repo-path'], $endpointCfg['repo-type'], $endpointCfg['hub-secret'], $endpointCfg['autoload-path']));
         
         $this->_quiet || self::_showLine(['(success)']);
      }
       

       
       
       
       
       
       
       
       
       
       
       
       
       
       
       

   })->getExitStatus())) {
      if (PHP_SAPI=='cli') {
         $installer->showUsage();
         exit($exitStatus);
      }
      return $exitStatus;
   }
})();







