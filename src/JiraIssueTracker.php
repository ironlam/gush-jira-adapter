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
            $this->config['username'],
            $this->config['password']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate()
    {
        $credentials = $this->config['authentication'];

        if (IssueClient::AUTH_HTTP_PASSWORD === $credentials['http-auth-type']) {
            $this->issueClient->authenticate(
                $credentials['username'],
                $credentials['password-or-token'],
                $credentials['http-auth-type']
            );
        } else {
            $this->issueClient->authenticate(
                $credentials['password-or-token'],
                $credentials['http-auth-type']
            );
        }

        $this->authenticationType = $credentials['http-auth-type'];
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthenticated()
    {
        if (IssueClient::AUTH_HTTP_PASSWORD === $this->authenticationType) {
            return is_array(
                $this->issueClient->api('authorizations')->all()
            );
        }

        return is_array($this->issueClient->api('me')->show());
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenGenerationUrl()
    {
        return sprintf('%s/settings/applications', $this->url);
    }

    /**
     * {@inheritdoc}
     */
    public function openIssue($subject, $body, array $options = [])
    {
        $api = $this->issueClient->api('issue');

        $issue = $api->create(
            $this->getUsername(),
            $this->getRepository(),
            array_merge($options, ['title' => $subject, 'body' => $body])
        );

        return $issue['number'];
    }

    /**
     * {@inheritdoc}
     */
    public function getIssue($id)
    {
        $api = $this->issueClient->api('issue');

        return $this->adaptIssueStructure(
            $api->show(
                $this->getUsername(),
                $this->getRepository(),
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

        $pager = new ResultPager($this->client);
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
        $api = $this->issueClient->api('issue');

        $api->update(
            $this->getUsername(),
            $this->getRepository(),
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
        $api = $this->issueClient->api('issue')->comments();

        $comment = $api->create(
            $this->getUsername(),
            $this->getRepository(),
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
        $pager = new ResultPager($this->client);

        $fetchedComments = $pager->fetchAll(
            $this->issueClient->api('issue')->comments(),
            'all',
            [
                $this->getUsername(),
                $this->getRepository(),
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
        $api = $this->issueClient->api('issue')->labels();

        return ArrayUtil::getValuesFromNestedArray(
            $api->all(
                $this->getUsername(),
                $this->getRepository()
            ),
            'name'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getMilestones(array $parameters = [])
    {
        $api = $this->issueClient->api('issue')->milestones();

        return ArrayUtil::getValuesFromNestedArray(
            $api->all(
                $this->getUsername(),
                $this->getRepository(),
                $parameters
            ),
            'title'
        );
    }
}
