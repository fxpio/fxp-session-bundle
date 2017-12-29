<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SessionBundle\Tests\Command;

/**
 * Tests case for InitSessionPdoCommand with MySQL driver.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class InitSessionPdoCommandMysqlTest extends AbstractInitSessionPdoCommandTest
{
    /**
     * @var string
     */
    protected $driver = 'mysql';
}
