#Webhook
Wrapper and webservice end-points to handle Github Webhook requests.

[Webhook Project Homepage](https://github.com/katmore/webhook)

##Description
The Webhook Project facilitates handling of from Github webhooks. It may be used as a [wrapper that can be integrated to other projects](#webhookrequest-and-webhookpayload-classes); or, as a solution to provide full webservice end-points (see the [end-point installer script](#endpoint-installer-script) or the [end-point example](#using-the-provided-end-point-example)).

##Usage
###Class autoloading
PSR-4 compliant class autoloading is required for any usage; this can be done with Composer.
  ```bash
composer require katmore/webhook
  ```

###Endpoint Installer Script
A php cli script [bin/add-endpoint.php](bin/add-endpoint.php) is provided for creating a Webhook that responds the Push Event on your repository by updating a local repository. Basic usage via command line will prompt for all the required parameters (such as the remote URL, local repo path, webhook secret, etc.):
```bash
php bin/add-endpoint.php
```
The `--help` switch will provide details on more advanced usage (such as quiet and non-interactive modes).
```bash
php bin/add-endpoint.php --help
```

###Webhook\Request and Webhook\Payload classes
To use this project as a wrapper, the main topics of focus will be the **"Webhook\Request"** class and **"Webhook\Payload"** child objects. The **Request** class facilitates dealing with a Github Webhook request by interpreting the message body and related HTTP headers. The **Request** constructor will create and populate a **Payload** child class with a name that corresponds to the Webhook "Event Type". It searches for the existence of a class having the same ["short name"](http://php.net/manual/en/reflectionclass.getshortname.php) as the [GitHub Event Type](https://developer.github.com/v3/activity/events/types) within the namespace [**Webhoook\Payload**](src/Payload). For example, a [Webhook\Payload\PushEvent object](src/Payload/PushEvent.php) is created and populated for a [PushEvent PushEvent Webhook request](https://developer.github.com/v3/activity/events/types/#pushevent)). If no Payload class is defined for a particular event, a [Webhook\Payload\Event object](src/Payload/Event.php) is populated by default. If successful, the **Payload** object is available from via the **Webhook\Request::getPayload()** method as detailed in the example below:

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
 * instiantate a Webhook/Request object...
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
At some point in the handling of a Webhook request it is critical that the "Hub Signature" be validated against the shared "Secret" for obvious security reasons. The [end-point installer](#endpoint-installer-script) and [end-point example](#endpoint-installer-script) both accomplish this by using the **Callback::validateRequest()** method of the [**Webhook\Callback** class](src/Callback.php). However, there may be situations where it is more practical to implement validation natively with the [php `hash_hmac()` function](http://php.net/manual/en/function.hash-hmac.php) as detailed in the example below:

```php
/*
 * prepare the messageBody; for example, by reading from the php input stream
 */
$messageBody = file_get_contents('php://input');

$hubSignature = $_SERVER['HTTP_X_HUB_SIGNATURE'];
$hubSecret = "My Secret";

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

An end-point example is provided at [web/endpoint-example.php](web/endpoint-example.php). Out of the box, this example responds to a 'push' event by performing the 'pull' or 'update' commands on a local git or svn repo as appropriate. It also responds to a 'ping' event with a success message. For added safety, this example also validates the "Hub Signature" against the shared Webhook "Secret" as recommended.

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
