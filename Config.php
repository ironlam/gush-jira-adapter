<?php

namespace Gush;

class Config
{
    public function __construct()
    {
        $this->set('jira_api.url', 'url');
        $this->set('jira_api.credentials', 'credentials');
        jira_api.limit => 100
    }

    public function x()
    {
        $baseUrl = 'jira_api.url';
        $client = new \Guzzle\Http\Client($baseUrl, ['curl.options' => ['CURLOPT_USERPWD' => 'jira_api.credentials']]);
        $issues = new IssueService($client);
        $search = new SearchService($client);
        $projects = new ProjectService($client);
}