#Webhook
Create webservice end-points for Github webhooks.

[Webhook Project Homepage](https://github.com/katmore/webhook)

##Description
The Webhook Project facilitates convenient creation of RESTful end-points to respond to event callbacks from Github webhooks.
For example, the [`Callback` class](src/Callback.php) validates that the webhook callback provided the same "Secret" you expected from when you initially set up a repository webhook.

##Usage
###Installation

  ```bash
composer require katmore/webhook
  ```

###Webhook/Request and Webhook/Payload classes
The most basic usage of the Webhook project is by creating a Webhook/Request object.
This will result in a Webhook/Payload object.

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
It is highly recomended that you validate the "Hub Signature" against the shared 'Secret' configured for the Webhook.
The following is an example of doing this validation with the native php `hash_hmac()` function.
```php
/*
 * prepare the messageBody; for example, by reading from the php input stream
 */
$messageBody = file_get_contents('php://input');

$hubSignature = $_SERVER['HTTP_X_HUB_SIGNATURE'];
$hubSecret = "My Secret";

/*
 * validate the secret
 */
list($algo, $hash) = explode('=', $hubSignature, 2) + ['', ''];
if ($hash !== hash_hmac($algo, $messageBody, $hubSecret)) {
   return;
}
```

###Webservice end-point example

A end-point example is provided at [web/example.php](web/example.php). Out of the box, this example responds to a 'push' event by performing the 'pull' or 'update' commands on a local git or svn repo as appropriate. It also responds to a 'ping' event with a success message. For added safety, this example also validates the "Hub Signature" against the shared Webhook "Secret" as recommended.

   * copy the provided [web/example.php](web/example.php)...
   
   ```bash
cp web/example.php web/my-end-point.php
    ```
   * edit "my-end-point.php" to specify configuration...
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
