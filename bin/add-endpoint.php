#!/usr/bin/env php
<?php
new class() {
   
   const ME = 'add-endpoint.php';
   
   const ME_LABEL = 'Webhook End-Point Service Installer';
   
   const ME_URL = 'https://github.com/katmore/webhook#end-point-installer-script';
    
   const COPYRIGHT = '(c) 2016-2018 Doug Bird. All Rights Reserved.';
   
   const ME_ABOUT = "Creates a webhook endpoint that updates a local git (or SVN) repository.";
      
   const FALLBACK_REPO_TYPE = 'git';
   
   const VALID_REPO_TYPES = ['git','svn'];
   
   /**
    * @var int
    */
   private $exitStatus = 0;
   
   /**
    * @var bool
    */
   private $quiet = false;
   
   /**
    * @var bool
    */
   private $verbose = false;
   
   /**
    * @var bool
    */
   private $nonInteractive = false;
   
   /**
    * @return void
    * @static
    */
   private function printUsage() {
      echo "Usage:\n";
      //'[--help|--quiet|--verbose] ';
      echo "   ".self::ME." [--help] | [<...Options>]\n";
      echo "   ".self::ME." [-quiet|--verbose] [<...More Options>]\n";
      echo "   ".self::ME." [[--non-interactive] --repo-url=<Repo URL> --hub-secret=<Github Webhook Secret>] [<...More Options>]\n";
      echo "   ".self::ME." [--repo-path=<path to local repository> [--bad-repo-path-ok] [--repo-type=(svn|git)=git]] [<...More Options>]\n";
      echo "   ".self::ME." [--overwrite-ok] [--endpoint-script=<endpoint script name>] [<...More Options>]\n";
      echo "   ".self::ME." [--no-autoload-ok] [<...More Options>]\n";
      
   }
   
   private function printHintError() {
      $this->printErrLine([
         "Hint, try:",
         "   ".self::ME." --usage",
         "   ".self::ME." --help",
      ]);
   }
   
   private function printHelp() {
      echo self::ME_ABOUT."\n".self::ME_URL."\n\n";
      $fallbackRepoType = self::FALLBACK_REPO_TYPE;
      $validRepoTypesCsv = implode(", ",self::VALID_REPO_TYPES);
      $validRepoTypesPsv = implode("|",self::VALID_REPO_TYPES);
      echo <<<"EOT"
Runtime Mode Options:

  --help
   Outputs this message then exits.
   
  --usage
   Outputs message regarding available switches and options then exits.

Output Control Options:

  --quiet
   Enable "quiet mode": the only output will be errors to STDERR.

  --verbose (ignored if --quiet switch is present)
   Enable "verbose mode": outputs extra information (such as full system paths, etc.)

  --non-interactive
   Force "non interactive mode": no input prompts will be issued

Endpoint Configuration Options:

  --repo-url=<URL>
   Required in "non interactive mode"
   Remote repository URL

  --hub-secret=<secret>
   Required in "non interactive mode"
   Shared Webhook Secret

   --endpoint-script=<BASENAME>
   Name of webservice endpoint script to create (ie: reponame.php)
   Default value: {basename path of --repo-url}.php

  --skip-repo
   Switch to specify that the endpoint will not update a local repository.

  --repo-path=<PATH>
   Optional; ignored if '--skip-repo' switch is specified.
   Local path to a repository to update, if any.

  --repo-type=($validRepoTypesPsv)=$fallbackRepoType
   Optional; ignored if '--skip-repo' switch is specified.
   Local repository type of --repo-path, if any.
   Must be one of the following values: $validRepoTypesCsv
   Default value: $fallbackRepoType

Path Configuration Options:

  --no-autoload-ok
   Bypass the check for the composer generated vendor/autoload.php file.

  --bad-repo-path-ok
   Skip the local path check for the --repo-path, if applicable.

  --overwrite-ok
   Always overwrite an endpoint file if it already exists.

EOT;
      
      if (! is_file(__DIR__ . "/../vendor/autoload.php") && !class_exists('\Webhook\Payload')) {
         if (!isset(getopt("",['no-autoload-ok'])['no-autoload-ok'])) {
            $dir = realpath(__DIR__.'/../');
            echo <<<"EOT"

Warning: class autoloading is missing (i.e. 'vendor/autoload.php'), please run composer.
   (Hint, try: 'cd $dir && composer update' or use the '--no-autoload-ok' flag to bypass this check)

EOT;
         }
      }
   }
    
   /**
    * @return void
    * @static
    */
   private function printIntro() {
      echo self::ME_LABEL."\n".self::COPYRIGHT."\n";
   }
    
   /**
    * @return void
    * @param string[]
    * @static
    */
   private function printErrLine(array $strLines) {
      $stderr = fopen('php://stderr', 'w');
      foreach ($strLines as $line) fwrite($stderr, "$line"."\n");
      fclose($stderr);
   }
   /**
    * @return void
    * @param string[]
    * @static
    */
   private function printLine(array $strLines) {
      foreach ($strLines as $line) echo "$line"."\n";
   }
   
   private function getEndpointSrc(string $repo_url,string $hub_secret,string $repo_path=null,string $repo_type=null) {
      $src = <<<'EOT'
<?php
/**
 * Webservice End-Point for responding to GitHub Webhook Events
 * 
 * Boilerplate Generated at %generated-time%
 *    by %ME%
 *  
 * @link %ME_URL%
 * 
 */


EOT;
      
      $src .= <<<'EOT'
/*
 * string $config['RepoUrl']
 *    GitHub repository URL: i.e: https://api.github.example.com/repos/my-org/my-repo
 */
$config['RepoUrl'] = '%escaped-repo-url%';

/*
 * string $config['Secret']
 *    The "Secret" configured in Github for the webhook.
 */
$config['Secret'] = base64_decode('%base64-hub-secret%');

EOT;
      if ($repo_path!==null && $repo_type!==null) {
         $src .= <<<'EOT'
/*
 * string $config['RepoPath']
 *    The local system path to the repository.
 *    A null or empty value will skip the repo update.
 */
$config['RepoPath'] = base64_decode('%base64-repo-path%');
/*
 * string $config['RepoPath']
 *    The repository type, i.e. 'git' or 'svn'.
 */
$config['RepoType'] = '%repo-type%';

EOT;
      } else {
         $src .= <<<'EOT'

function onPushEvent(\Webhook\Payload\PushEvent $payload) {
   /*
    *
    * --- place code in this function ---
    * --- that should execute when a Github 'push' event occurs ---
    *
    */
}

function onPingEvent(\Webhook\Payload\PingEvent $payload) {
   /*
    *
    * --- place code in this function ---
    * --- that should execute when a Github 'ping' event occurs ---
    *
    */
}

EOT;
      }
         
      $src .= <<<'EOT'

function onOtherEvent(\Webhook\Payload\Event $payload) {
   /*
    *
    * --- place code in this function ---
    * --- that should execute when a Github event occurs other than 'ping' or 'push' ---
    *
    */
}

if (is_file(__DIR__."/../vendor/autoload.php")) {
   require __DIR__."/../vendor/autoload.php";
}

header('Content-Type: text/plain');

$callback = new \Webhook\Callback($config['Secret'],function(\Webhook\Payload $payload ) use (&$config) {


EOT;
   if ($repo_path!==null && $repo_type!==null) {
      $src .= <<<'EOT'
   /*
    * on a "push" event, do a "git pull" or "svn up" on the local system copy of the repo
    */

EOT;
   }
      $src .= <<<'EOT'
   if ($payload instanceof \Webhook\Payload\PushEvent) {


EOT;
      if ($repo_path!==null && $repo_type!==null) {
         $src .= <<<'EOT'
      if (!empty($config['RepoPath'])) {
         if (is_dir($config['RepoPath'])) {

            if ($config['RepoType']=='git') {
               
               exec('cd '.$config['RepoPath'].' && git pull 2>&1',$out,$exit_status);
            
            } else if ($config['RepoType']=='svn') {
               
               exec('svn up '.$config['RepoPath'].' 2>&1',$out,$exit_status);
            
            }
            
            if ($exit_status!=0) http_response_code(500);
            
            echo implode("\n",$output)."\n\n";
            
            if ($exit_status!=0) echo "exit status: $exit_status\n";

         } else {

            echo "skipped repo update because the path was invalid, see \$config['RepoPath']\n";

         }
      }

EOT;
      } else {
         $src .= <<<'EOT'
      $ret = onPushEvent($payload);
      if ($ret===false) {
         http_response_code(500);
      }

EOT;
      }
      $src .= <<<'EOT'
      echo "event: ".$payload->getEvent()."\n";
      
      echo "sender login: ".$payload->sender->login."\n";
      echo "sender avatar_url: ".$payload->sender->avatar_url."\n";
      
      echo "pusher name: ".$payload->pusher->name."\n";
      echo "pusher email: ".$payload->pusher->email."\n";
      
      return;
      
   }

EOT;
      
   if ($repo_path!==null && $repo_type!==null) {
      $src .= <<<'EOT'
   /*
    * on a "ping" event, do a "git status" or "svn info" on the local system copy of the repo
    */

EOT;
   }
      
      $src .= <<<'EOT'
   if ($payload instanceof \Webhook\Payload\PingEvent) {
      

EOT;
      
      if ($repo_path!==null && $repo_type!==null) {
         $src .= <<<'EOT'
      if (!empty($config['RepoPath'])) {

         if (is_dir($config['RepoPath'])) {

            if ($config['RepoType']=='git') {
                
               exec('cd '.$config['RepoPath'].' && git status 2>&1',$out,$exit_status);
            
            } else if ($config['RepoType']=='svn') {
                
               exec('svn info '.$config['RepoPath'].' 2>&1',$out,$exit_status);
            
            }
            
            if ($exit_status!=0) http_response_code(500);
            
            echo implode("\n",$output)."\n\n";
            
            if ($exit_status!=0) echo "exit status: $exit_status\n";

         } else {

            echo "skipped repo status because the path was invalid, see \$config['RepoPath']\n";

         }
      }

EOT;
      } else {
         $src .= <<<'EOT'
      $ret = onPingEvent($payload);
      if ($ret===false) {
         http_response_code(500);
      }

EOT;
      }

      $src .= <<<'EOT'
      echo "event: ".$payload->getEvent()."\n";
      echo "zen: ".$payload->zen;
      
      echo "sender login: ".$payload->sender->login."\n";
      echo "sender avatar_url: ".$payload->sender->avatar_url."\n";
      
      return;
      
   }
   
   $ret = onOtherEvent($payload);
   if ($ret===false) {
      http_response_code(500);
   }

   echo "event: ".$payload->getEvent()."\n";
   
},new \Webhook\UrlCallbackRule($config['RepoUrl']));

register_shutdown_function(function() {
   $last_error = error_get_last();
   if ($last_error && isset($last_error['type'])) {
      if (!in_array($last_error['type'],[E_DEPRECATED,E_WARNING,E_NOTICE,E_RECOVERABLE_ERROR,E_USER_DEPRECATED,E_USER_NOTICE,E_USER_WARNING])) {
         http_response_code (500);
      }
   }
});

try {
   $request = \Webhook\Request::service(file_get_contents('php://input'),isset($_SERVER)?$_SERVER:[]);
   if ($request->getRequestMethod()!=='POST') {
      throw new \Webhook\InvalidRequestException("requestMethod must be POST");
   }
   $callback->validateRequest($request->getHubSignature(), $request->getMessageBody(), $request->getPayload());
} catch(\Webhook\InvalidRequestException $e) {
   http_response_code(500);
   echo $e->getMessage();
}

EOT;

      $src=str_replace("%generated-time%",date("c"),$src);
      $src=str_replace("%ME_LABEL%",self::ME_LABEL,$src);
      $src=str_replace("%ME%",self::ME,$src);
      $src=str_replace("%ME_URL%",self::ME_URL,$src);
      
      $src=str_replace("%base64-hub-secret%",base64_encode($hub_secret),$src);
      $src=str_replace("%escaped-repo-url%",str_replace(['\\','\''],['\\\\','\\\''],$repo_url),$src);
      $src=str_replace("%repo-url%",$repo_url,$src);
      
      if ($repo_path!==null && $repo_type!==null) {
         $src=str_replace("%base64-repo-path%",base64_encode($repo_path),$src);
         $src=str_replace("%repo-type%",$repo_type,$src);
      }
      
      return $src;
   }
   private $printedInteractiveIntro = false;
   private function printInteractiveIntroOnce() {
      echo "\n";
      if ($this->printedInteractiveIntro) return;
      $this->printedInteractiveIntro = true;
      $this->printLine(["Interactive mode: provide configuration details or Ctrl+C at any time to abort...",""]);
   }
   
   public function __construct() {

      if ('cli' !== PHP_SAPI) {
         if (!headers_sent()) {
            header('Content-type: text/plain');
            http_response_code(500);
         }
         echo "\nERROR: ".basename(__FILE__)." must be run from the command-line\n";
         return;
      }

      if (isset(getopt("",["help",])['help'])) {
         $this->printIntro();
         $this->printHelp();
         return;
      }
      
      if (isset(getopt("",["usage",])['usage'])) {
         $this->printIntro();
         $this->printUsage();
         return;
      }

      if (!($this->quiet=isset(getopt("",["quiet",])['quiet']))) {
         $this->verbose=isset(getopt("",["verbose",])['verbose']);
      }
      
      if (!($this->nonInteractive = !stream_isatty ( STDOUT ))) {
         $this->nonInteractive = isset(getopt("",["non-interactive",])['non-interactive']);
      }
      
      $this->quiet || $this->printIntro();
      
      if (!is_file(__DIR__ . "/../vendor/autoload.php") && !class_exists('\Webhook\Payload')) {
         $skipAutoloadCheck = false;
         if (isset(getopt("",['no-autoload-ok'])['no-autoload-ok'])) {
            $skipAutoloadCheck = true;
         } elseif (!$this->nonInteractive) {
            $this->printErrLine(["Warning: class autoloading is missing (i.e. 'vendor/autoload.php')"]);
            $this->printInteractiveIntroOnce();
            $v = trim(readline("Abort because autoloading is missing? (choose 'n' to continue anyway) [(y)/n]: "));
            echo "\n";
            if (substr($v,0,1)==='n') {
               $skipAutoloadCheck = true;
            }
         }
         if (!$skipAutoloadCheck) {
            $dir = realpath(__DIR__.'/../');
            $this->printErrLine([
               "Error: class autoloading is missing (i.e. 'vendor/autoload.php'), please run composer.",
               "   (Hint, try: 'cd $dir && composer update' or use the '--no-autoload-ok' flag to bypass this check)",
            ]);
            return $this->exitStatus = 1;
         }
      }
      
      $endpointCfg = [
         'repo-url'=>'',
         'repo-path'=>null,
         'repo-type'=>null,
         'hub-secret'=>'',
         'endpoint-script'=>'',
      ];
      
      $cfgSourceOption = [];
      
      foreach($endpointCfg as $k=>&$v) {
         if (!empty(getopt("",["$k::",])[$k])) {
            $v=getopt("",["$k::",])[$k];
            $cfgSourceOption []=$k;
         }
      }
      unset($k);
      unset($v);
      
      $required = ['repo-url','hub-secret'];
      
      if (!$this->nonInteractive) {
         $missing = [];
         foreach($required as $k) {
            if (empty($endpointCfg[$k])) {
               $missing[]=$k;
            }
         }
         unset($k);
         
         if (count($missing)) {
            $this->printInteractiveIntroOnce();
            foreach($required as $k) {
               if (in_array($k,$cfgSourceOption)) {
                  continue;
               }
               $default="";
               if (!empty($endpointCfg[$k])) $default = " ({$endpointCfg[$k]})";
               for($i=0;$i<5;$i++) {
                  $v = trim(readline("Enter the $k$default: "));
                  echo "\n";
                  if (!empty($v)) {
                     $endpointCfg[$k]=$v;
                     break 1;
                  }
                  if (!empty($endpointCfg[$k])) {
                     break 1;
                  }
               }
               unset($v);
               unset($default);
               if (empty($endpointCfg[$k])) {
                  $this->printErrLine([self::ME. ": (ERROR) failed to get value for '$k' after $i tries"]);
                  return $this->exitStatus=1;
               }
               unset($i);
            }
            unset($k);
         }
         unset($missing);
         
         if (empty($endpointCfg['repo-path']) && !isset(getopt("",["skip-repo",])['skip-repo'])) {
            $this->printInteractiveIntroOnce();
            $v = trim(readline("Shall this endpoint update a local copy of a git or svn repository? [y/(n)]: "));
            echo "\n";
            if (substr($v,0,1)==='y') {
               for($i=0;$i<5;$i++) {
                  $v = trim(readline("Enter the local repository path: "));
                  echo "\n";
                  if (!empty($v)) {
                     $endpointCfg['repo-path']=$v;
                     break 1;
                  }
               }
               if (empty($endpointCfg['repo-path'])) {
                  $this->printErrLine([self::ME. ": (ERROR) failed to get value for 'repo-path' after $i tries"]);
                  return $this->exitStatus=1;
               }
               unset($i);
            }
         }
         
         if (!empty($endpointCfg['repo-path']) && empty($endpointCfg['repo-type']) && !isset(getopt("",["skip-repo",])['skip-repo'])) {
            $repoTypes = self::VALID_REPO_TYPES;
            foreach($repoTypes as &$type) {
               if ($type===self::FALLBACK_REPO_TYPE) {
                  $type = "($type)";
               }
            }
            unset($type);
            $this->printInteractiveIntroOnce();
            $v = trim(readline("Enter the repository type [".implode("/",$repoTypes)."]: "));
            echo "\n";
            if (!empty($v)) {
               $endpointCfg['repo-type']=self::FALLBACK_REPO_TYPE;
            }
         }
      }
      
      $missing = [];
      foreach($required as $k) {
         if (empty($endpointCfg[$k])) {
            $missing[]=$k;
            $this->printErrLine([self::ME . ": (ERROR) missing required value for ".$k]);
         }
      }
      unset($k);
      
      if (count($missing)) {
         return $this->exitStatus = 2;
      }
      unset($missing);
      
      
      
      
      
      if (!empty($endpointCfg['repo-path']) && (empty(realpath($endpointCfg['repo-path'])) || !is_dir($endpointCfg['repo-path']) || !is_writable($endpointCfg['repo-path']))) {
         
         $this->printErrLine([self::ME . ": (WARNING) 'repo-path' did not resolve to a writeable directory: {$endpointCfg['repo-path']}"]);
         
         $skipRepoPathCheck = false;
         if (isset(getopt("",["bad-repo-path-ok",])['bad-repo-path-ok'])) {
            $skipRepoPathCheck = true;
         } elseif (!$this->nonInteractive) {
            $this->printInteractiveIntroOnce();
            $v = trim(readline("Abort because the 'repo-path' is invalid? (choose 'n' to continue anyway) [(y)/n]: "));
            echo "\n";
            if (substr($v,0,1)==='n') {
               $skipRepoPathCheck = true;
            }
         }
         
         if (!$skipRepoPathCheck) {
            $this->printErrLine([self::ME . ": aborted due to invalid 'repo-path', use --bad-repo-path-ok to skip this check"]);
            if (in_array('repo-path',$cfgSourceOption)) {
               $this->exitStatus = 2;
            } else {
               $this->exitStatus = 1;
            }
            return;
         }
      }
      
      $error = false;
      
      if (empty(parse_url($endpointCfg['repo-url'],\PHP_URL_SCHEME))) {
         $this->printErrLine([self::ME . ": (ERROR) 'repo-url' is missing a URL-SCHEME and is thus an invalid remote repository URL: {$endpointCfg['repo-url']}"]);
         $error = true;
         if (in_array('repo-url',$cfgSourceOption)) $this->exitStatus = 2;
      } else
      if (empty(parse_url($endpointCfg['repo-url'],\PHP_URL_HOST))) {
         $this->printErrLine([self::ME . ": (ERROR) 'repo-url' is missing a URL-HOST and is thus an invalid remote repository URL: {$endpointCfg['repo-url']}"]);
         $error = true;
         if (in_array('repo-url',$cfgSourceOption)) $this->exitStatus = 2;
      } else
      if (empty(parse_url($endpointCfg['repo-url'],\PHP_URL_PATH))) {
         $this->printErrLine([self::ME . ": (ERROR) 'repo-url' is missing a URL-PATH and is thus an invalid remote repository URL: {$endpointCfg['repo-url']}"]);
         $error = true;
         if (in_array('repo-url',$cfgSourceOption)) $this->exitStatus = 2;
      } else {
         $path = explode("/",parse_url($endpointCfg['repo-url'],\PHP_URL_PATH));
         if (count($path)<3) {
            $this->printErrLine([self::ME . ": (ERROR) did not recognize a valid repository name in the 'repo-url' : {$endpointCfg['repo-url']}"]);
            $error = true;
            if (in_array('repo-url',$cfgSourceOption)) $this->exitStatus = 2;
         }
      }
      
      if ($endpointCfg['repo-type']!==null && (!in_array($endpointCfg['repo-type'],self::VALID_REPO_TYPES,true))) {
         $this->printErrLine([self::ME . ": (ERROR) the 'repo-type' \"{$endpointCfg['repo-type']}\" is invalid, it must be one of the following: ".implode(", ",self::VALID_REPO_TYPES)]);
         $error = true;
         if (in_array('repo-type',$cfgSourceOption)) $this->exitStatus = 2;
      }
      
      if ($error) {
         if ($this->exitStatus===0) {
            $this->printErrLine([self::ME . ": (FATAL) aborted because of invalid user input"]);
            $this->exitStatus = 1;
         } elseif ($this->exitStatus===2) {
            $this->printErrLine([self::ME . ": (FATAL) aborted because there is at least one invalid parameter"]);
         }
         return;
         
      }
      unset($error);
      
      if ($endpointCfg['repo-type']===null && $endpointCfg['repo-path']!==null) {
         $endpointCfg['repo-type'] = self::FALLBACK_REPO_TYPE;
      }
      
      if (!empty(getopt("",["endpoint-script::",])['endpoint-script'])) {
         $webEndpointScript=getopt("",["endpoint-script::",])['endpoint-script'];
      } else {
         $webEndpointScript = pathinfo(parse_url($endpointCfg['repo-url'],\PHP_URL_PATH),\PATHINFO_DIRNAME)."/".pathinfo(parse_url($endpointCfg['repo-url'],\PHP_URL_PATH),\PATHINFO_BASENAME);
      }
      if (substr($webEndpointScript,0,4)!='.php') {
         $webEndpointScript.='.php';
      }
      $webEndpointScript = trim(str_replace('/','-',$webEndpointScript),'-');
      $webEndpointPath = __DIR__ . "/../web/$webEndpointScript";
      
      $scriptDir = pathinfo($webEndpointPath,\PATHINFO_DIRNAME);
      if (!is_dir($scriptDir) || !is_writable($scriptDir)) {
         $this->printErrLine([self::ME . ": (FATAL) web endpoint directory is not accessible: $scriptDir"]);
         if (in_array('endpoint-script',$cfgSourceOption)) {
            $this->exitStatus = 2;
         } else {
            $this->exitStatus = 1;
         }
         return;
      }
      
      $endpointShortname = pathinfo($scriptDir,\PATHINFO_FILENAME)."/".pathinfo($webEndpointScript,\PATHINFO_BASENAME);
      
      $overwrite = null;
      if (is_file($webEndpointPath)) {
         $webEndpointPath = realpath($webEndpointPath);
         $overwrite = false;
         if (isset(getopt("",["overwrite-ok",])['overwrite-ok'])) {
            $overwrite = true;
         } elseif (!$this->nonInteractive) {
            $this->printErrLine([self::ME.": (WARNING) the endpoint file already exists: $webEndpointPath"]);
            $this->printInteractiveIntroOnce();
            $v = trim(readline("Ok to overwrite existing file? [y/(n)]: "));
            echo "\n";
            if (substr($v,0,1)==='y') {
               $overwrite = true;
            }
         }
         
         if (!$overwrite) {
            $this->printErrLine([self::ME . ": aborted because endpoint file already exists, use --overwrite-ok to skip this check"]);
            if (in_array('endpoint-script',$cfgSourceOption)) {
               $this->exitStatus = 2;
            } else {
               $this->exitStatus = 1;
            }
            return;
         }
      }
      
      $this->verbose && $this->printLine(["endpoint path: $webEndpointPath"]);
      
      $this->quiet || $this->printLine(["generating endpoint: $endpointShortname"]);
      
      if (empty($endpointCfg['repo-path']) || empty($endpointCfg['repo-type']) || isset(getopt("",["skip-repo",])['skip-repo'])) {
         $endpointCfg['repo-path'] = null;
         $endpointCfg['repo-type'] = null;
      }
      
      $endpointSrc = $this->getEndpointSrc( $endpointCfg['repo-url'], $endpointCfg['hub-secret'], $endpointCfg['repo-path'], $endpointCfg['repo-type']);
      
      //(string $repo_url,string $hub_secret,string $repo_path=null,string $repo_type=null)
      $errorMessage = [];
      $errorReporting = error_reporting(error_reporting() | E_NOTICE);
      $errorHandler = set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$errorMessage)
      {
         $errorMessage []= $errstr;
      });
      $endpointResult = file_put_contents($webEndpointPath, $endpointSrc);
      set_error_handler($errorHandler);
      error_reporting($errorReporting);
      
      if (false===$endpointResult) {
         foreach($errorMessage as $errstr) {
            $this->printErrLine([self::ME . ": file_put_contents() error: ".$errstr]);
         }
         unset($errstr);
         
         $this->printErrLine([self::ME . ": (FATAL) failed to endpoint '$webEndpointPath'"]);
         return $this->exitStatus = 1;
      }
      
      if ($overwrite===true && isset(getopt("",["overwrite-ok",])['overwrite-ok'])) {
         $this->printErrLine([self::ME.": (WARNING) existing file was overwritten: $webEndpointPath"]);
      }
      
      $this->quiet || $this->printLine(['(success)']);
   }
   
   public function __destruct() {  
      if ('cli' === PHP_SAPI) {
         $this->exitStatus===2 && $this->printHintError();
         exit($this->exitStatus);
      }
   }

};






