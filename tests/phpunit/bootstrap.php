<?php
/**
 * PHPUnit bootstrap file
 *
 * - includes webhook/vendor/autoload.php
 */

$config['app_root'] = __DIR__.'/../../';
$config['autoload'] = "{$config['app_root']}/vendor/autoload.php";

require $config['autoload'];