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

use Fxp\Bundle\SessionBundle\Command\InitSessionPdoCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

/**
 * Tests case for InitSessionPdoCommand.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractInitSessionPdoCommandTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $application;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $definition;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $kernel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;

    /**
     * @var InitSessionPdoCommand
     */
    protected $command;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperSet;

    /**
     * @var string
     */
    protected $driver = 'driver';

    public function setUp()
    {
        if (!class_exists('Symfony\Component\Console\Application')) {
            $this->markTestSkipped('Symfony Console is not available.');
        }

        $this->application = $this->getMockBuilder('Symfony\\Bundle\\FrameworkBundle\\Console\\Application')
            ->disableOriginalConstructor()
            ->getMock();
        $this->definition = $this->getMockBuilder('Symfony\\Component\\Console\\Input\\InputDefinition')
            ->disableOriginalConstructor()
            ->getMock();
        $this->kernel = $this->getMockBuilder('Symfony\\Component\\HttpKernel\\KernelInterface')->getMock();
        $this->helperSet = $this->getMockBuilder('Symfony\\Component\\Console\\Helper\\HelperSet')->getMock();
        $this->container = $this->getMockBuilder('Symfony\\Component\\DependencyInjection\\ContainerInterface')->getMock();

        $this->application->expects($this->any())
            ->method('getDefinition')
            ->will($this->returnValue($this->definition));
        $this->definition->expects($this->any())
            ->method('getArguments')
            ->will($this->returnValue([]));
        $this->definition->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue([
                new InputOption('--verbose', '-v', InputOption::VALUE_NONE, 'Increase verbosity of messages.'),
                new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'),
                new InputOption('--no-debug', null, InputOption::VALUE_NONE, 'Switches off debug mode.'),
        ]));
        $this->application->expects($this->any())
            ->method('getKernel')
            ->will($this->returnValue($this->kernel));
        $this->application->expects($this->once())
            ->method('getHelperSet')
            ->will($this->returnValue($this->helperSet));
        $this->kernel->expects($this->any())
            ->method('getContainer')
            ->will($this->returnValue($this->container));

        /* @var Application $application */
        $application = $this->application;

        $pdoMock = $this->createConfiguration();
        $this->command = new InitSessionPdoCommand($pdoMock);
        $this->command->setApplication($application);
    }

    public function testTableIsCreated()
    {
        $returnCode = $this->command->run(new ArrayInput([]), new NullOutput());
        $this->assertEquals(0, $returnCode);
    }

    public function testTableIsAlreadyCreated()
    {
        $ex = new \PDOException('Table aready exist');
        $ref = new \ReflectionClass($ex);
        $pCode = $ref->getProperty('code');
        $pCode->setAccessible(true);
        $pCode->setValue($ex, '42S01');

        /* @var ContainerInterface $container */
        $container = $this->container;
        /* @var \PHPUnit_Framework_MockObject_MockObject $pdoMock*/
        $pdoMock = $container->get('fxp_session.handler.pdo');
        $pdoMock->expects($this->any())
            ->method('createTable')
            ->willThrowException($ex);

        $returnCode = $this->command->run(new ArrayInput([]), new NullOutput());
        $this->assertEquals(0, $returnCode);
    }

    /**
     * @expectedException \PDOException
     * @expectedExceptionMessage PDO exception
     */
    public function testPdoAnotherException()
    {
        /* @var ContainerInterface $container */
        $container = $this->container;
        /* @var \PHPUnit_Framework_MockObject_MockObject $pdoMock*/
        $pdoMock = $container->get('fxp_session.handler.pdo');
        $pdoMock->expects($this->any())
            ->method('createTable')
            ->willThrowException(new \PDOException('PDO exception'));

        $this->command->run(new ArrayInput([]), new NullOutput());
    }

    /**
     * @return PdoSessionHandler
     */
    protected function createConfiguration()
    {
        /* @var PdoSessionHandler $pdoMock */
        $pdoMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler')
            ->disableOriginalConstructor()
            ->getMock();

        $this->container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($p) use ($pdoMock) {
                if ('fxp_session.handler.pdo' === $p) {
                    return $pdoMock;
                }

                return;
            })
        );
        $this->container->expects($this->any())
            ->method('has')
            ->will($this->returnCallback(function ($p) use ($pdoMock) {
                if ('fxp_session.handler.pdo' === $p) {
                    return true;
                }

                return false;
            })
        );

        return $pdoMock;
    }
}
