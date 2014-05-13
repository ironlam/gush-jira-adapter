<?php

namespace Gush\Tests\Service;

use Gush\Tests\TestCase;
use Gush\Service\ProjectService;

class ProjectServiceTest extends TestCase
{
    public function testProjectServiceGetAll()
    {        
        $jsonFile = __DIR__ . '/../assets/response/project.json';

        $service = new ProjectService(
            $this->getClientMock($jsonFile)
        );

        $result = $service->getAll();

        $this->assertEquals(2, count($result));
    }

    public function testProjectServiceGetSingleProject()
    {
        $jsonFile = __DIR__ . '/../assets/response/projectGRA.json';

        $service = new ProjectService(
            $this->getClientMock($jsonFile)
        );

        $result = $service->getProjectId($projectId = 'GRA');

        $this->assertEquals(10000, $result['id']);
    }

    /**
     * @expectedException \Guzzle\Http\Exception\BadResponseException
     */
    public function testProjectServiceGetAllException()
    {
        $service = new ProjectService($this->getClientMockException());

        $service->getAll('PROJECT', 'repository', 'branch');
    }

    public function testProjectServiceGetAllNoData()
    {
        $service = new ProjectService($this->getClientMockNoData());

        $result = $service->getAll('PROJECT', 'repository', 'branch');

        $this->assertEquals(array(), $result);
    }

    public function testProjectServiceGetAllErrors()
    {
        $service = new ProjectService($this->getClientMockErrors());

        $result = $service->getAll(array());

        $this->assertEquals(false, $result);
    }
}
