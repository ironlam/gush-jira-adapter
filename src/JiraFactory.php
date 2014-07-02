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
        $configurator = new DefaultConfigurator(
            $helperSet->get('dialog'),
            'GitHub issue tracker',
            'https://api.github.com/',
            'https://github.com'
        );

        return $configurator;
    }
}
