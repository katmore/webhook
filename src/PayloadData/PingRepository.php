<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;

class PingRepository implements Populatable {
   
   use RepositoryTrait;
   
   use PopulatorTrait;
   
}