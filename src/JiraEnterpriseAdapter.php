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

use Github\Client;
use Github\HttpClient\CachedHttpClient;
use Guzzle\Plugin\Log\LogPlugin;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Pierre du Plessis <pdples@gmail.com>
 */
class JiraEnterpriseAdapter extends GitHubAdapter
{
    /**
     * {@inheritdoc}
     */
    public function supportsRepository($remoteUrl)
    {
        // always returns false as its not save to determine this (yet)
        return false;
    }
}

