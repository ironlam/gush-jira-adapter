<?php

namespace Gush\Tests\Service;

use Gush\Tests\TestCase;
use Gush\Service\IssueService;

class IssueServiceTest extends TestCase
{
    public function testIssueServiceGet()
    {        
        $jsonFile = __DIR__ . '/../assets/response/issue.json';

        $service = new IssueService(
            $this->getClientMock($jsonFile)
        );

        $result = $service->get('AA-999');

        $this->assertEquals('AA-999', $result['key']);
        $this->assertEquals('Bug', $result['fields']['issuetype']['name']);
    }

    /**
     * @expectedException \Guzzle\Http\Exception\BadResponseException
     */
    public function testIssueServiceGetException()
    {
        $service = new IssueService($this->getClientMockException());

        $service->get('PROJECT', 'repository', 'branch');
    }

    public function testIssueServiceGetNoData()
    {
        $service = new IssueService($this->getClientMockNoData());

        $result = $service->get('PROJECT', 'repository', 'branch');

        $this->assertEquals(array(), $result);
    }

    public function testIssueServiceGetErrors()
    {
        $service = new IssueService($this->getClientMockErrors());

        $result = $service->get('PROJECT', 'repository', 'branch');

        $this->assertEquals(false, $result);
    }

    /**
     * @group now
     */
    public function testCreateAnIssue()
    {
        $jsonFile = __DIR__ . '/../assets/response/issueGRA.json';

        $service = new IssueService(
            $this->getClientMock($jsonFile)
        );

        $data = [
            "fields" => [
                "project" => ["id" => "10000"],
                "summary" => "something's wrong",
                "issuetype" => ["id" => "10000"],
                "assignee" => ["name" => "homer"],
                "reporter" => [ "name" => "smithers"],
                "priority" => ["id" => "20000"],
                "timetracking" =>  [
                    "originalEstimate" => "10",
                    "remainingEstimate" => "10",
                ],
                "description" =>  "description",
                "duedate" =>  "2011-03-11",
            ]
        ];

        $result = $service->createIssue($data);

        $this->assertEquals('AA-999', $result['key']);
        $this->assertEquals('Bug', $result['fields']['issuetype']['name']);
    }
}
