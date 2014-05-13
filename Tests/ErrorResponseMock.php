<?php

namespace Gush\Tests;

/**
 * Mocks JSON response that returns an error for unit testing purposes.
 */
class ErrorResponseMock
{
    protected $response;

    public function __construct()
    {
    }

    public function json()
    {
        return array(
            'errorMessages' => ['This is an error message.'],
            'errors'        => [],
         );
    }
}
