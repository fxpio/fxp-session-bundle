<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SessionBundle\Tests\Command\Fixtures;

/**
 * Mock for PDO.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PDOMock extends \PDO
{
    public function __construct()
    {
    }
}