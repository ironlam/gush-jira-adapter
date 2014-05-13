<?php

namespace Gush\Tests;

/**
 * Mocks empty JSON response for unit testing purposes.
 */
class EmptyResponseMock
{
    protected $response;

    public function __construct()
    {
    }

    public function json()
    {
        return [];
    }
}
