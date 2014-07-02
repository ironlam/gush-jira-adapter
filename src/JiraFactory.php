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
use Symfony\Component\Console\Helper\HelperSet;

/**
 * @author Luis Cordova <cordoval@gmail.com>
 */
class JiraFactory
{
    public static function createIssueTracker($adapterConfig, Config $config)
    {
        return new JiraIssueTracker($adapterConfig, $config);
    }

    public static function createIssueTrackerConfigurator(HelperSet $helperSet)
    {
        return new DefaultConfigurator(
            $helperSet->get('question'),
            'Jira issue tracker',
            'https://attlassian.net/',
            'https://attlassian.net'
        );
    }
}
