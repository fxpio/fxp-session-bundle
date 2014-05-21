<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SessionBundle\Tests\Command;

/**
 * Tests case for InitSessionPdoCommand with MySQL driver.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class InitSessionPdoCommandMysqlTest extends AbstractInitSessionPdoCommandTest
{
    /**
     * @var string
     */
    protected $driver = 'mysql';
}
