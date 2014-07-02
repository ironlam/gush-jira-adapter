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
use Gush\Util\ArrayUtil;
use JiraApi\Clients\IssueClient;
use JiraApi\Clients\ProjectClient;

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
     * @var array
     */
    protected $config;

    /**
     * @var \Gush\Config
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
        $this->issueClient = $this->buildJiraClient();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRepository($remoteUrl)
    {
        return false !== stripos($remoteUrl, 'atlassian.net');
    }

    /**
     * @return IssueClient
     */
    protected function buildJiraClient()
    {
        $this->url = rtrim($this->config['base_url'], '/');
        $this->domain = rtrim($this->config['repo_domain_url'], '/');

        return new IssueClient(
            $this->url,
            $this->config['authentication'][IssueClient::AUTH_HTTP_PASSWORD]['username'],
            $this->config['authentication'][IssueClient::AUTH_HTTP_PASSWORD]['password']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate()
    {
        $projectClient = new ProjectClient(
            $this->url,
            $this->config['authentication'][IssueClient::AUTH_HTTP_PASSWORD]['username'],
            $this->config['authentication'][IssueClient::AUTH_HTTP_PASSWORD]['password']
        );

        /** @var \GuzzleHttp\Message\Response $response */
        $response = $projectClient->getAll();

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
        throw new \Exception('not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function openIssue($subject, $body, array $options = [])
    {
        $issue = $this->issueClient->create(
            array_merge($options, ['title' => $subject, 'body' => $body])
        );

        return $issue['number'];
    }

    /**
     * {@inheritdoc}
     */
    public function getIssue($id)
    {
        return $this->adaptIssueStructure(
            $this->issueClient->get(
                $id
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIssueUrl($id)
    {
        return sprintf('%s/%s/%s/issues/%d', $this->domain, $this->getUsername(), $this->getRepository(), $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getIssues(array $parameters = [], $page = 1, $perPage = 30)
    {
        // FIXME is not respecting the pagination

        $fetchedIssues = $pager->fetchAll(
            $this->issueClient->api('issue'),
            'all',
            [
                $this->getUsername(),
                $this->getRepository(),
                $parameters
            ]
        );

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
        $this->issueClient->update(
            $id,
            $parameters
        );
    }

    /**
     * {@inheritdoc}
     */
    public function closeIssue($id)
    {
        $this->updateIssue($id, ['state' => 'closed']);
    }

    /**
     * {@inheritdoc}
     */
    public function createComment($id, $message)
    {
        $comment = $this->issueClient->createComment(
            $id,
            ['body' => $message]
        );

        return $comment['html_url'];
    }

    /**
     * {@inheritdoc}
     */
    public function getComments($id)
    {
        $fetchedComments = $pager->fetchAll(
            $this->issueClient->api('issue')->comments(),
            'all',
            [
                $id,
            ]
        );

        $comments = [];

        foreach ($fetchedComments as $comment) {
            $comments[] = [
                'id' => $comment['id'],
                'url' => $comment['html_url'],
                'body' => $comment['body'],
                'user' => $comment['user']['login'],
                'created_at' => !empty($comment['created_at']) ? new \DateTime($comment['created_at']) : null,
                'updated_at' => !empty($comment['updated_at']) ? new \DateTime($comment['updated_at']) : null,
            ];
        }

        return $comments;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabels()
    {
        return ArrayUtil::getValuesFromNestedArray(
            $this->issueClient->all(),
            'name'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getMilestones(array $parameters = [])
    {
        throw new \Exception('not sure how to implement');
    }
}
