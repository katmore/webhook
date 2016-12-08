#Webhook
Wrappers to handle [Github Webhook requests](https://developer.github.com/webhooks/).

[Webhook Project Homepage](https://github.com/katmore/webhook)

##Description
The Webhook Project facilitates the usage of Github Webhook requests into a workflow. It provides [class wrappers](#webhookrequest-and-webhookpayload-classes) for integration and an [end-point installer script](#endpoint-installer-script) for a self-contained solution.

##Requirements
 * PHP 7.0 or higher
 * PSR-4 Class autoloading; for example, using Composer:
 
  ```bash
composer require katmore/webhook
  ```

##Usage
###Endpoint Installer Script
The command-line script [bin/add-endpoint.php](bin/add-endpoint.php) creates a webservice end-point that responds to a Github Webhook for the **PushEvent** on a remote repository by updating a local repository. When invoked without any arguments it will prompt for all the required parameters (such as the remote URL, local repo path, webhook secret, etc.):
```bash
php bin/add-endpoint.php
```
The `--help` switch will provide details on more advanced usage (such as quiet and non-interactive modes).
```bash
php bin/add-endpoint.php --help
```

###Webhook\Request and Webhook\Payload classes
To use this project as a wrapper, the main topics of focus will be the **"Webhook\Request"** class and **"Payload"** objects. The **Webhook\Request** class facilitates dealing with a Github Webhook request by interpreting the message body and related HTTP headers. The **Webhook\Request** class constructor will instantiate and populate a **Webhook\Payload** child class having a class name that corresponds to the Webhook "Event Type": it searches for the existence of a class having the same ["short name"](http://php.net/manual/en/reflectionclass.getshortname.php) as the [GitHub Event Type](https://developer.github.com/v3/activity/events/types) within the namespace [**Webhook\Payload**](src/Payload). For example, a [Webhook\Payload\PushEvent object](src/Payload/PushEvent.php) is created and populated for a [**PushEvent** Webhook request](https://developer.github.com/v3/activity/events/types/#pushevent). If no **Webhook\Payload** child class is defined for a particular event; the [Webhook\Payload\Event](src/Payload/Event.php) class is used by default. If successful, the **Payload** object is available  via the **Webhook\Request::getPayload()** method as detailed in the example below:

```php
/*
 * prepare the messageBody; for example, by reading from the php input stream
 */
$messageBody = file_get_contents('php://input');

/*
 * prepare the 'hubSignature'; for example, from the value of the HTTP header 'HTTP_X_HUB_SIGNATURE'
 */
$hubSignature = $_SERVER['HTTP_X_HUB_SIGNATURE'];

/*
 * prepare the 'gitHubEvent'; for example, from the value of the HTTP header 'HTTP_X_GITHUB_EVENT'
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
###Validating a request's "Hub Signature"
At some point in the handling of a Webhook request it is critical that the "Hub Signature" be validated against the shared "Secret" for obvious security reasons. The [end-point installer](#endpoint-installer-script) and [end-point example](#endpoint-installer-script) both accomplish this by using the **Callback::validateRequest()** method of the [**Webhook\Callback** class](src/Callback.php). However, there may be situations where it is more practical to implement validation natively with the [`hash_hmac()` function](http://php.net/manual/en/function.hash-hmac.php) as detailed in the example below:

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

/*
 * continue doing stuff....
 */
```

###Using the provided end-point example

An end-point example is provided at [web/endpoint-example.php](web/endpoint-example.php) which responds to a **PushEvent** by invoking 'pull' or 'update' commands on a local git or svn repository as appropriate. It also responds to a a **PingEvent** with a success message.

   * copy the provided [web/endpoint-example.php](web/endpoint-example.php)...
   
   ```bash
cp web/endpoint-example.php web/my-org/my-repo.php
    ```
   * edit "web/my-org/my-repo.php" to specify configuration...
     * change the value of `$config['RepoUrl']` to your GitHub repository URL:
     
     ```php
$config['RepoUrl'] = 'https://github.com/my-organization/my-repo';
   ```
     * change the value of `$config['Secret']` to the "Secret" configured in Github for the webhook:
     
     ```php
$config['Secret'] = 'My Secret';
   ```
     * change the value of `$config['RepoPath']` the local system path to the repository:
     
     ```php
$config['RepoPath'] = '/path/to/my/repo';
   ```
     * change the value of `$config['RepoType']` to either 'git' or 'svn', depending on the local repository type:
     
     ```php
$config['RepoType'] = 'git';
   ```

##Legal
"Webhook" is distributed under the terms of the [MIT license](LICENSE) or the [GPLv3](GPLv3) license.

Copyright (c) 2016, Doug Bird.
All rights reserved.
