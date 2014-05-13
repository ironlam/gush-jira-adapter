<?php

namespace Gush\Service;

/**
 * Service class that handles projects.
 */
class ProjectService extends AbstractService
{
    /**
     * Method to retrieve all projects.
     * 
     * @return boolean|array
     */
    public function getAll()
    {
        return $this->performQuery(
            $this->createUrl('project')
        );
    }

    public function getProjectId($projectId)
    {
        return $this->performQuery(
            $this->createUrl('project/'.$projectId)
        );
    }
}
