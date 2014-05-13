<?php

namespace Gush\Service;

/**
 * Service class that handles searches.
 */
class SearchService extends AbstractPagedService
{
    /**
     * Search for issues.
     * 
     * @param array $params
     * 
     * @return Boolean|array
     */
    public function search(array $params = array())
    {
        return $this->performQuery(
            $this->createUrl('search', $params)
        );
    }
}
