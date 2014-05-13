<?php

namespace Gush\Service;

/**
 * Service class that manages issues.
 */
class IssueService extends AbstractService
{    
    /**
     * Retrieve details for a specific issue.
     * 
     * @param string $key
     * 
     * @return array
     */
    public function get($key)
    {
        return $this->performQuery(
            $this->createUrl(
                sprintf('issue/%s', $key)
            )
        );
    }

    public function createIssue($data)
    {
        return $this->performPost(
            $this->createUrl('issue'),
            $data
        );
    }
}
