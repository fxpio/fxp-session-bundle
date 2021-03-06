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
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpKernel\KernelInterface;

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

    protected function setUp(): void
    {
        if (!class_exists(Application::class)) {
            $this->markTestSkipped('Symfony Console is not available.');
        }

        $this->application = $this->getMockBuilder(\Symfony\Bundle\FrameworkBundle\Console\Application::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->definition = $this->getMockBuilder(InputDefinition::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->kernel = $this->getMockBuilder(KernelInterface::class)->getMock();
        $this->helperSet = $this->getMockBuilder(HelperSet::class)->getMock();
        $this->container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $this->application->expects($this->any())
            ->method('getDefinition')
            ->willReturn($this->definition)
        ;
        $this->definition->expects($this->any())
            ->method('getArguments')
            ->willReturn([])
        ;
        $this->definition->expects($this->any())
            ->method('getOptions')
            ->willReturn([
                new InputOption('--verbose', '-v', InputOption::VALUE_NONE, 'Increase verbosity of messages.'),
                new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'),
                new InputOption('--no-debug', null, InputOption::VALUE_NONE, 'Switches off debug mode.'),
            ])
        ;
        $this->application->expects($this->any())
            ->method('getKernel')
            ->willReturn($this->kernel)
        ;
        $this->application->expects($this->once())
            ->method('getHelperSet')
            ->willReturn($this->helperSet)
        ;
        $this->kernel->expects($this->any())
            ->method('getContainer')
            ->willReturn($this->container)
        ;

        /** @var Application $application */
        $application = $this->application;

        $pdoMock = $this->createConfiguration();
        $this->command = new InitSessionPdoCommand($pdoMock);
        $this->command->setApplication($application);
    }

    /**
     * @throws
     */
    public function testTableIsCreated(): void
    {
        $returnCode = $this->command->run(new ArrayInput([]), new NullOutput());
        $this->assertEquals(0, $returnCode);
    }

    /**
     * @throws
     */
    public function testTableIsAlreadyCreated(): void
    {
        $ex = new \PDOException('Table aready exist');
        $ref = new \ReflectionClass($ex);
        $pCode = $ref->getProperty('code');
        $pCode->setAccessible(true);
        $pCode->setValue($ex, '42S01');

        /** @var ContainerInterface $container */
        $container = $this->container;
        /** @var \PHPUnit_Framework_MockObject_MockObject $pdoMock */
        $pdoMock = $container->get('fxp_session.handler.pdo');
        $pdoMock->expects($this->any())
            ->method('createTable')
            ->willThrowException($ex)
        ;

        $returnCode = $this->command->run(new ArrayInput([]), new NullOutput());
        $this->assertEquals(0, $returnCode);
    }

    /**
     * @throws
     */
    public function testPdoAnotherException(): void
    {
        $this->expectException(\PDOException::class);
        $this->expectExceptionMessage('PDO exception');

        /** @var ContainerInterface $container */
        $container = $this->container;
        /** @var \PHPUnit_Framework_MockObject_MockObject $pdoMock */
        $pdoMock = $container->get('fxp_session.handler.pdo');
        $pdoMock->expects($this->any())
            ->method('createTable')
            ->willThrowException(new \PDOException('PDO exception'))
        ;

        $this->command->run(new ArrayInput([]), new NullOutput());
    }

    /**
     * @return PdoSessionHandler
     */
    protected function createConfiguration(): PdoSessionHandler
    {
        /** @var PdoSessionHandler $pdoMock */
        $pdoMock = $this->getMockBuilder(PdoSessionHandler::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->container->expects($this->any())
            ->method('get')
            ->willReturnCallback(static function ($p) use ($pdoMock) {
                return 'fxp_session.handler.pdo' === $p ? $pdoMock : null;
            })
        ;

        $this->container->expects($this->any())
            ->method('has')
            ->willReturnCallback(static function ($p) {
                return 'fxp_session.handler.pdo' === $p;
            })
        ;

        return $pdoMock;
    }
}
