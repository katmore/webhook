<?php
declare(strict_types=1);
namespace Webhook\TestCase;

trait PingPayloadTrait {
   
   public function pingRequestObjectProvider() : array {
      return [
         [static::getPingRequestObject()],
      ];
   }
   
   public static function getExpectedPingRequestObjectValue(string $property) {
      $request = static::getPingRequestObject();
      if (!property_exists($request, $property)) {
         trigger_error("property '$property' does not exist in the ping request object",E_USER_ERROR);
      }
      return $request->$property;
   }
   
   public static function getPingRequestObject() : \stdClass {
      if (null===($request = json_decode(static::getPingRequestBody()))) {
         trigger_error(
            "getPingRequestBody() provided invalid JSON: ".
            json_last_error_msg ()
            ,
            E_USER_ERROR);
      }
      if (!$request instanceof \stdClass) {
         trigger_error("request body was not a JSON object type as expected",E_USER_ERROR);
      }
      return $request;
   }
   
   public static function getPingRequestBody() : string {
      return <<<"PUSH_REQUEST_BODY"
{
  "zen": "It's not fully flopped until it's farting.",
  "hook_id": 66666666,
  "hook": {
    "type": "Repository",
    "id": 66666666,
    "name": "web",
    "active": true,
    "events": [
      "push"
    ],
    "config": {
      "content_type": "json",
      "insecure_ssl": "0",
      "secret": "********",
      "url": "https://myorg.example.com/my-webhook/my-repo.php"
    },
    "updated_at": "2018-11-28T09:00:48Z",
    "created_at": "2018-11-28T09:00:48Z",
    "url": "https://api.github.example.com/repos/my-org/my-repo/hooks/66666666",
    "test_url": "https://api.github.example.com/repos/my-org/my-repo/hooks/66666666/test",
    "ping_url": "https://api.github.example.com/repos/my-org/my-repo/hooks/66666666/pings",
    "last_response": {
      "code": null,
      "status": "unused",
      "message": null
    }
  },
  "repository": {
    "id": 77777777,
    "node_id": "MDEwOlJlcG9zaXRvcnk3Nzc3Nzc3Nw==",
    "name": "my-repo",
    "full_name": "my-org/my-repo",
    "private": true,
    "owner": {
      "login": "my-org",
      "id": 44444444,
      "node_id": "GwetcgpLdotXMGXB8dJCYY5ECtFsbq0H",
      "avatar_url": "https://avatars1.githubusercontent.example.com/u/44444444?v=4",
      "gravatar_id": "",
      "url": "https://api.github.example.com/users/my-org",
      "html_url": "https://github.example.com/my-org",
      "followers_url": "https://api.github.example.com/users/my-org/followers",
      "following_url": "https://api.github.example.com/users/my-org/following{/other_user}",
      "gists_url": "https://api.github.example.com/users/my-org/gists{/gist_id}",
      "starred_url": "https://api.github.example.com/users/my-org/starred{/owner}{/repo}",
      "subscriptions_url": "https://api.github.example.com/users/my-org/subscriptions",
      "organizations_url": "https://api.github.example.com/users/my-org/orgs",
      "repos_url": "https://api.github.example.com/users/my-org/repos",
      "events_url": "https://api.github.example.com/users/my-org/events{/privacy}",
      "received_events_url": "https://api.github.example.com/users/my-org/received_events",
      "type": "Organization",
      "site_admin": false
    },
    "html_url": "https://github.example.com/my-org/my-repo",
    "description": "web and backround services for the ap-saas system",
    "fork": false,
    "url": "https://api.github.example.com/repos/my-org/my-repo",
    "forks_url": "https://api.github.example.com/repos/my-org/my-repo/forks",
    "keys_url": "https://api.github.example.com/repos/my-org/my-repo/keys{/key_id}",
    "collaborators_url": "https://api.github.example.com/repos/my-org/my-repo/collaborators{/collaborator}",
    "teams_url": "https://api.github.example.com/repos/my-org/my-repo/teams",
    "hooks_url": "https://api.github.example.com/repos/my-org/my-repo/hooks",
    "issue_events_url": "https://api.github.example.com/repos/my-org/my-repo/issues/events{/number}",
    "events_url": "https://api.github.example.com/repos/my-org/my-repo/events",
    "assignees_url": "https://api.github.example.com/repos/my-org/my-repo/assignees{/user}",
    "branches_url": "https://api.github.example.com/repos/my-org/my-repo/branches{/branch}",
    "tags_url": "https://api.github.example.com/repos/my-org/my-repo/tags",
    "blobs_url": "https://api.github.example.com/repos/my-org/my-repo/git/blobs{/sha}",
    "git_tags_url": "https://api.github.example.com/repos/my-org/my-repo/git/tags{/sha}",
    "git_refs_url": "https://api.github.example.com/repos/my-org/my-repo/git/refs{/sha}",
    "trees_url": "https://api.github.example.com/repos/my-org/my-repo/git/trees{/sha}",
    "statuses_url": "https://api.github.example.com/repos/my-org/my-repo/statuses/{sha}",
    "languages_url": "https://api.github.example.com/repos/my-org/my-repo/languages",
    "stargazers_url": "https://api.github.example.com/repos/my-org/my-repo/stargazers",
    "contributors_url": "https://api.github.example.com/repos/my-org/my-repo/contributors",
    "subscribers_url": "https://api.github.example.com/repos/my-org/my-repo/subscribers",
    "subscription_url": "https://api.github.example.com/repos/my-org/my-repo/subscription",
    "commits_url": "https://api.github.example.com/repos/my-org/my-repo/commits{/sha}",
    "git_commits_url": "https://api.github.example.com/repos/my-org/my-repo/git/commits{/sha}",
    "comments_url": "https://api.github.example.com/repos/my-org/my-repo/comments{/number}",
    "issue_comment_url": "https://api.github.example.com/repos/my-org/my-repo/issues/comments{/number}",
    "contents_url": "https://api.github.example.com/repos/my-org/my-repo/contents/{+path}",
    "compare_url": "https://api.github.example.com/repos/my-org/my-repo/compare/{base}...{head}",
    "merges_url": "https://api.github.example.com/repos/my-org/my-repo/merges",
    "archive_url": "https://api.github.example.com/repos/my-org/my-repo/{archive_format}{/ref}",
    "downloads_url": "https://api.github.example.com/repos/my-org/my-repo/downloads",
    "issues_url": "https://api.github.example.com/repos/my-org/my-repo/issues{/number}",
    "pulls_url": "https://api.github.example.com/repos/my-org/my-repo/pulls{/number}",
    "milestones_url": "https://api.github.example.com/repos/my-org/my-repo/milestones{/number}",
    "notifications_url": "https://api.github.example.com/repos/my-org/my-repo/notifications{?since,all,participating}",
    "labels_url": "https://api.github.example.com/repos/my-org/my-repo/labels{/name}",
    "releases_url": "https://api.github.example.com/repos/my-org/my-repo/releases{/id}",
    "deployments_url": "https://api.github.example.com/repos/my-org/my-repo/deployments",
    "created_at": "2017-04-04T07:23:26Z",
    "updated_at": "2018-11-28T08:57:17Z",
    "pushed_at": "2018-11-28T08:57:12Z",
    "git_url": "git://github.example.com/my-org/my-repo.git",
    "ssh_url": "git@github.example.com:my-org/my-repo.git",
    "clone_url": "https://github.example.com/my-org/my-repo.git",
    "svn_url": "https://github.example.com/my-org/my-repo",
    "homepage": "https://example.com/homepage",
    "size": 12345,
    "stargazers_count": 0,
    "watchers_count": 0,
    "language": "PHP",
    "has_issues": true,
    "has_projects": true,
    "has_downloads": true,
    "has_wiki": false,
    "has_pages": false,
    "forks_count": 0,
    "mirror_url": null,
    "archived": false,
    "open_issues_count": 22,
    "license": {
      "key": "other",
      "name": "Other",
      "spdx_id": "NOASSERTION",
      "url": null,
      "node_id": "MDc6TGljZW5zZTA="
    },
    "forks": 0,
    "open_issues": 22,
    "watchers": 0,
    "default_branch": "master"
  },
  "sender": {
    "login": "examplegituser",
    "id": 999999,
    "node_id": "MDQ6VXNlcjk5OTk5OQ==",
    "avatar_url": "https://avatars0.githubusercontent.example.com/u/999999?v=4",
    "gravatar_id": "",
    "url": "https://api.github.example.com/users/examplegituser",
    "html_url": "https://github.example.com/examplegituser",
    "followers_url": "https://api.github.example.com/users/examplegituser/followers",
    "following_url": "https://api.github.example.com/users/examplegituser/following{/other_user}",
    "gists_url": "https://api.github.example.com/users/examplegituser/gists{/gist_id}",
    "starred_url": "https://api.github.example.com/users/examplegituser/starred{/owner}{/repo}",
    "subscriptions_url": "https://api.github.example.com/users/examplegituser/subscriptions",
    "organizations_url": "https://api.github.example.com/users/examplegituser/orgs",
    "repos_url": "https://api.github.example.com/users/examplegituser/repos",
    "events_url": "https://api.github.example.com/users/examplegituser/events{/privacy}",
    "received_events_url": "https://api.github.example.com/users/examplegituser/received_events",
    "type": "User",
    "site_admin": false
  }
}
PUSH_REQUEST_BODY;
   }
   
   
}