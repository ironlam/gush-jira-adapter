<?php

/**
 * This file is part of Gush.
 *
 * (c) Luis Cordova <cordoval@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Gush\Adapter;

use Gush\Config;
use JiraApi\Clients\IssueClient;
use JiraApi\Clients\ProjectClient;
use JiraApi\Search\SearchBuilder;

/**
 * @author Luis Cordova <cordoval@gmail.com>
 */
class JiraIssueTracker implements IssueTracker
{
    /**
     * @var string|null
     */
    protected $url;

    /**
     * @var string|null
     */
    protected $domain;

    /**
     * @var IssueClient
     */
    protected $issueClient;

    /**
     * @var IssueClient
     */
    protected $projectClient;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Config
     */
    protected $globalConfig;

    /**
     * @param array  $config
     * @param Config $globalConfig
     */
    public function __construct(array $config, Config $globalConfig)
    {
        $this->config = $config;
        $this->globalConfig = $globalConfig;
        $this->buildJiraClient();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRepository($remoteUrl)
    {
        return false !== stripos($remoteUrl, 'atlassian.com');
    }

    private function buildJiraClient()
    {
        $this->url = rtrim($this->config['base_url'], '/');
        $this->domain = rtrim($this->config['repo_domain_url'], '/');

        $auth = $this->config['authentication'];

        $this->issueClient = new IssueClient($this->url, $auth['username'], $auth['password-or-token']);
        $this->projectClient = new ProjectClient($this->url, $auth['username'], $auth['password-or-token']);
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate()
    {
        /** @var \GuzzleHttp\Message\Response $response */
        $response = $this->projectClient->getAll();

        return 200 === $response->getStatusCode();
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthenticated()
    {
        return $this->authenticate();
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenGenerationUrl()
    {
        throw new \Exception('This feature is not implemented for the Jira Adapter.');
    }

    /**
     * {@inheritdoc}
     */
    public function openIssue($subject, $body, array $options = [])
    {
        $issue = $this->issueClient
            ->create(array_merge($options, ['title' => $subject, 'body' => $body]))
            ->json()
        ;

        return $issue['id'];
    }

    /**
     * {@inheritdoc}
     */
    public function getIssue($id)
    {
        return $this->adaptIssueStructure(
            $this->issueClient->get($id)->json()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIssueUrl($id)
    {
        return sprintf('%s/issue/%d', $this->url, $id);
    }

    /**
     * @todo implement getAllIssues method on client
     *
     * {@inheritdoc}
     */
    public function getIssues(array $parameters = [], $page = 1, $perPage = 30)
    {
        $jql = [];
        foreach ($parameters as $key => $value) {
            switch ($key) {
                case 'number':
                    $jql[] = sprintf('id=%s', $value);
                    break;
                case 'state':
                    $jql[] = sprintf('status=%s', $value);
                    break;
                case 'title':
                    $jql[] = sprintf('summary~%s', $value);
                    break;
                case 'body':
                    $jql[] = sprintf('description~%s', $value);
                    break;
                case 'user':
                    $jql[] = sprintf('reporter=%s', $value);
                    break;
                case 'labels':
                    $jql[] = sprintf('labels in (%s)', implode(',', $value));
                    break;
                case 'closed_by':
                case 'assignee':
                    $jql[] = sprintf('assignee=%s', $value);
                    break;
                case 'milestone':
                    $jql[] = sprintf('versions in (%s)', $value);
                    break;
                case 'created_at':
                    $nextDay = clone $value;
                    $nextDay->modify('+1 day');
                    $jql[] = sprintf(
                        'created>=%s and created<=%s',
                        $value->format('Y-m-d'),
                        $nextDay->format('Y-m-d')
                    );
                    break;
                case 'updated_at':
                    $nextDay = clone $value;
                    $nextDay->modify('+1 day');
                    $jql[] = sprintf(
                        'updated>=%s and updated<=%s',
                        $value->format('Y-m-d'),
                        $nextDay->format('Y-m-d')
                    );
                    break;
            }
        }

        $searchBuilder = new SearchBuilder();
        $searchBuilder
            ->setJql(implode(' and ', $jql))
            ->setLimit($perPage)
            ->setPage($page)
        ;

        $fetchedIssues = $this->issueClient->search($searchBuilder);

        $issues = [];
        foreach ($fetchedIssues as $issue) {
            $issues[] = $this->adaptIssueStructure($issue);
        }

        return $issues;
    }

    /**
     * {@inheritdoc}
     */
    public function updateIssue($id, array $parameters)
    {
        $this->issueClient->update($id, $parameters);
    }

    /**
     * @todo ask user how to change issue status
     *
     * {@inheritdoc}
     */
    public function closeIssue($id)
    {
        throw new \Exception('This feature has yet to be implemented. Feel free to create a PR.');
    }

    /**
     * {@inheritdoc}
     */
    public function createComment($id, $message)
    {
        $comment = $this->issueClient
            ->createComment($id, ['body' => $message])
            ->json()
        ;

        return $comment['self'];
    }

    /**
     * {@inheritdoc}
     */
    public function getComments($id)
    {
        $comments = $this->issueClient
            ->getComments($id)
            ->json()
        ;

        return array_map([$this, 'adaptCommentStructure'], $comments['comments']);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabels()
    {
        throw new \Exception('This feature is not supported by the tracker');
    }

    /**
     * {@inheritdoc}
     */
    public function getMilestones(array $parameters = [])
    {
        throw new \Exception('This feature has yet to be implemented. Feel free to create a PR.');
    }

    /**
     * Turns given structure into adapter issue structure
     *
     * @param  array $issue
     *
     * @return array
     */
    protected function adaptIssueStructure(array $issue)
    {
        return [
            'url' => $issue['self'],
            'number' => $issue['id'],
            'state' => isset($issue['status']) ? $issue['status']['name'] : null,
            'title' => $issue['summary'],
            'body' => $issue['description'],
            'user' => $issue['reporter']['name'],
            'labels' => $issue['labels'],
            'assignee' => $issue['assignee']['name'],
            'milestone' => count($issue['versions']) > 0 ? $issue['versions'][0] : null,
            'created_at' => new \DateTime($issue['created']),
            'updated_at' => new \DateTime($issue['updated']),
            'closed_by' => $issue['assignee']['name'],
            'pull_request' => false,
        ];
    }

    /**
     * Converts api comment to gush comment structure
     *
     * @param  array  $comment
     *
     * @return array
     */
    protected function adaptCommentStructure(array $comment)
    {
        return [
            'id' => $comment['id'],
            'url' => $comment['self'],
            'body' => $comment['body'],
            'user' => $comment['author']['name'],
            'created_at' => new \DateTime($comment['created']),
            'updated_at' => new \DateTime($comment['updated']),
        ];
    }
}
