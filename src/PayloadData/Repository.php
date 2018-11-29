<?php
namespace Webhook\PayloadData;

use Webhook\Populatable;
use Webhook\PopulatorTrait;
use Webhook\PopulateListener;

class Repository implements Populatable,PopulateListener {
   
   /**
    * @var string The id of the repository.
    */
   public $id;
   
   /**
    * @var string name of the repository, i.e. "my-repo"
    */
   public $name;
   
   /**
    * @var string organization followed by a forward-slash and name of the repository, i.e. "my-org/my-repo"
    */
   public $full_name;
   
   /**
    * @var bool true if a private repo, <b>bool</b> false otherwise
    */
   public $private;
   
   /**
    * @var string repo description
    */
   public $description;
   
   /**
    * @var bool true if repo is a fork of another repo, <b>bool</b> false otherwise
    */
   public $fork;
   
   /**
    * @var string the "html" (front-end) url of the repo
    */
   public $html_url;
   
   /**
    * @var string the default url of the repo
    */
   public $url;
   
   /**
    * @var string the "git" protocol url of the repo
    */
   public $git_url;
   
   /**
    * @var string the "ssh" connection string url of the repo
    */
   public $ssh_url;
   
   /**
    * @var string the url of this repo is a clone of
    */
   public $clone_url;
   
   /**
    * @var string the url to use for this repo with Subversion
    */
   public $svn_url;
   
   /**
    * @var string this repo's homepage URL
    */
   public $homepage;
   
   /**
    * @var int size of this repo in KB
    */
   public $size;
   
   /**
    * @var \Webhook\PayloadData\RepositoryOwner repository owner object for this repo
    */
   public $owner;
   
   /**
    * @var string the programming language declared of this repo
    */
   public $language;
   
   /**
    * @var bool true if there are issues, <b>bool</b> false otherwise
    */
   public $has_issues;
   
   /**
    * @var bool true if there are projects, <b>bool</b> false otherwise
    */
   public $has_projects;
   
   /**
    * @var int number of forks this repo has
    */
   public $forks_count;
   
   /**
    * @var bool true if this repo is archived, <b>bool</b> false otherwise
    */
   public $archived;
   
   /**
    * @var int number of open issues this repo has
    */
   public $open_issues_count;
   
   /**
    * @var \Webhook\PayloadData\License license object
    */
   public $license;
   
   /**
    * @var int number of forks this repo has
    */
   public $forks;
   
   /**
    * @var int number of issues this repo has
    */
   public $open_issues;
   
   /**
    * @var string default branch for this repo
    */
   public $default_branch;
   
   /**
    * @var string the master branch of this repo
    */
   public $master_branch;
   
   /**
    * @var string the name of the organization that owns this repo
    */
   public $organization;
   
   use PopulatorTrait;
   
   public function populateComplete() {
      
      if (!$this->owner instanceof RepositoryOwner) {
         $this->owner = (new RepositoryOwner)->populateFromObject($this->owner);
      }
      
      if (!$this->license instanceof License) {
         $this->license = (new License)->populateFromObject($this->license);
      }
      
   }
   
   
   
   
   
   
   
   
   
   
   
   
}