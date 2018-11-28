# Webhook

Wrappers and webservice to handle [Github Webhook requests](https://developer.github.com/webhooks/).

[Webhook Project Homepage](https://github.com/katmore/webhook)

## Description
The Webhook Project facilitates workflow integration of Github Webhook requests. It provides [class wrappers](#wrapper-classes) for existing projects and an optional [end-point installer script](#end-point-installer-script) for a self-contained solution that is easy to deploy.

## Requirements
 * PHP 7.2 or higher

## Usage
### End-point Installer Script
The command-line script [bin/add-endpoint.php](bin/add-endpoint.php) creates a webservice end-point that responds to a Github Webhook for the **PushEvent** on a remote repository by updating a local repository and to a **PingEvent** by displaying a success message. 

The simplest way to prepare the end-point installer is to copy this project somewhere and run Composer:
```sh
git clone https://github.com/katmore/webhook.git 
cd webhook
composer update
```
The installer can be invoked without any arguments; it will prompt for all the required parameters (such as the remote URL, local repo path, webhook secret, etc.):

```sh
php bin/add-endpoint.php
```
The `--help` switch will provide details on more advanced usage (such as quiet and non-interactive modes).
```sh
php bin/add-endpoint.php --help
```

### Wrapper Classes
To use this project's wrapper classes within your existing project, the main topics of focus will be the [**Webhook\Request** class](src/Request.php) and **Payload** objects. As a recomended first step, add a dependancy using Composer to your existing project:
  ```sh
composer require katmore/webhook
  ```

The **Webhook\Request** class facilitates interpreting the message body and related HTTP headers of a Github Webhook request. The **Webhook\Request** class constructor will instantiate and populate a [**Webhook\Payload**](src/Payload.php) child class having a class name that corresponds to the Webhook "Event Type": it searches for the existence of a class having the same ["short name"](http://php.net/manual/en/reflectionclass.getshortname.php) as the [GitHub Event Type](https://developer.github.com/v3/activity/events/types) within the namespace [**Webhook\Payload**](src/Payload). If a thusly named **Webhook\Payload** child class is not defined for a particular event; the [Webhook\Payload\Event](src/Payload/Event.php) class is used by default. For example, a [Webhook\Payload\PushEvent object](src/Payload/PushEvent.php) is created and populated for a [**PushEvent** Webhook request](https://developer.github.com/v3/activity/events/types/#pushevent). 

The **Payload** object as populated by the **Webhook\Request** constructor is available using the **Webhook\Request::getPayload()** method as detailed in the example below:

```php
/*
 * obtain the messageBody; in this case, by reading from the php input stream
 */
$messageBody = file_get_contents('php://input');

/*
 * obtain the 'hubSignature'; for example, from the value of the HTTP header 'HTTP_X_HUB_SIGNATURE'
 */
$hubSignature = $_SERVER['HTTP_X_HUB_SIGNATURE'];

/*
 * obtain the 'gitHubEvent'; for example, from the value of the HTTP header 'HTTP_X_GITHUB_EVENT'
 */
$gitHubEvent = $_SERVER['HTTP_X_GITHUB_EVENT'];

/*
 * instiantate a Webhook\Request object...
 */
$request = new \Webhook\Request($messageBody, $hubSignature, $gitHubEvent);

/*
 * get the payload object...
 * For more info on payloads for the various github events, see:
 *    https://developer.github.com/v3/activity/events/types
 */
$payload = $request->getPayload();

/*
 * The payload object will be an instance of the 
 *    \Webhook\Payload\PushEvent class
 *    if the github event was a 'Push Event'.
 *  
 * The payload object will be an instance of the 
 *    \Webhook\Payload\PingEvent class
 *    if the github event was a 'Ping Event'.
 *
 * The payload object will be an instance of the 
 *    \Webhook\Payload\Event class
 *    for all other events.
 */
var_dump($payload);
```
### Validating a request's "Hub Signature"
At some point in the handling of a Webhook request it is critical that the "Hub Signature" be validated against the shared "Secret" for obvious security reasons. The [end-point installer](#endpoint-installer-script) and [end-point example](#endpoint-installer-script) both accomplish this by using the **Callback::validateRequest()** method of the [**Webhook\Callback** class](src/Callback.php). 

```php
/*
 * the 'Secret' field corresponding to the expected Webhook request
 */
$hubSecret = "My Secret";

/*
 * obtain the messageBody; in this case, by reading from the php input stream
 */
$messageBody = file_get_contents('php://input');

/*
 * obtain the 'hubSignature'; for example, from the value of the HTTP header 'HTTP_X_HUB_SIGNATURE'
 */
$hubSignature = $_SERVER['HTTP_X_HUB_SIGNATURE'];

/*
 * obtain the 'gitHubEvent'; for example, from the value of the HTTP header 'HTTP_X_GITHUB_EVENT'
 */
$gitHubEvent = $_SERVER['HTTP_X_GITHUB_EVENT'];

/*
 * instiantate a Webhook\Request object...
 */
$request = new \Webhook\Request($messageBody, $hubSignature, $gitHubEvent);

/*
 * instantiate 'Callback' controller object
 */
$callback = new \Webhook\Callback($hubSecret,function(\Webhook\Payload $payload) {
   echo "event: ".$payload->getEvent()."\n";
   if ($payload instanceof \Webhook\Payload\PushEvent) {
     //
     // place custom code here that should be executed upon every Webhook 'push' event
     //
   }
});

try {
  /*
   * validate the request with the 'Callback' controller object
   */
  $callback->validateRequest($request->getHubSignature(), $request->getMessageBody(), $request->getPayload());
} catch(\Webhook\InvalidRequest $e) {
   /*
    * force a 500 HTTP response code upon encountering an 'InvalidRequest' exception,
    */
   http_response_code(500);
   echo "Invalid Request: ".$e->getMessage();
}
```

Alternatively, there may be situations where it is desired to implement this validation natively by using the `hash_hmac()` function as shown in the example below:
```php
/*
 * the 'Secret' field corresponding to the expected Webhook request
 */
$hubSecret = "My Secret";

/*
 * obtain the messageBody; in this case, by reading from the php input stream
 */
$messageBody = file_get_contents('php://input');

/*
 * obtain the signature via HTTP header
 */
$hubSignature = $_SERVER['HTTP_X_HUB_SIGNATURE'];

/*
 * validate the signature
 */
list($algo, $hash) = explode('=', $hubSignature, 2) + ['', ''];
if ($hash !== hash_hmac($algo, $messageBody, $hubSecret)) {
   echo "Invalid Signature!";
   return;
}
```

### Using the provided end-point example

An end-point example is provided at [web/endpoint-example.php](web/endpoint-example.php) which responds to a **PushEvent** by invoking a 'git pull' or any custom code placed in a callback function, as configured. It also responds to a a **PingEvent** with a success message.

   * copy the provided [web/endpoint-example.php](web/endpoint-example.php)...
   
   ```sh
   cp web/endpoint-example.php web/my-repo-endpoint.php
   ```
   * edit to specify configuration...
     * change the value of `$config['RepoUrl']` to your GitHub repository URL:
     
     ```php
     $config['RepoUrl'] = 'https://github.com/my-organization/my-repo';
     ```
     * change the value of `$config['Secret']` to the "Secret" configured in Github for the webhook:
     
     ```php
     $config['Secret'] = 'My Secret';
     ```
     * leave the value of `$config['RepoPath']` empty to skip the repo update, or change it to the local system path of a repository to perform a `git update` on every 'push' Webhook event:
     
     ```php
     $config['RepoPath'] = '';
     //$config['RepoPath'] = '/path/to/my/repo';
     ```
     
     * place any custom code within the `onPushEvent` function that should be executed on every 'push' Webhook event
     
     ```php
     function onPushEvent(Payload\PushEvent $payload) {
         /*
          *
          * --- place code in this function --
          * --- that should execute when a Github 'push' event occurs --
          *
          */
          //
          // place your custom code here
          //
     }
     ```
    

## Legal
"Webhook" is distributed under the terms of the [MIT license](LICENSE) or the [GPLv3](GPLv3) license.

Copyright (c) 2016-2018, Doug Bird.
All rights reserved.
