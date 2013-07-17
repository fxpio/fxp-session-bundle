<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SessionBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Sonatra\Bundle\SessionBundle\Command\InitSessionPdoCommand;

/**
 * Tests case for InitSessionPdoCommand.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class InitSessionPdoCommandTest extends \PHPUnit_Framework_TestCase
{
    private $application;
    private $definition;
    private $kernel;
    private $container;
    private $command;

    /**
     * @return null
     */
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
        $this->kernel = $this->getMock('Symfony\\Component\\HttpKernel\\KernelInterface');
        $this->helperSet = $this->getMock('Symfony\\Component\\Console\\Helper\\HelperSet');
        $this->container = $this->getMock('Symfony\\Component\\DependencyInjection\\ContainerInterface');

        $this->application->expects($this->any())
            ->method('getDefinition')
            ->will($this->returnValue($this->definition));
        $this->definition->expects($this->any())
            ->method('getArguments')
            ->will($this->returnValue(array()));
        $this->definition->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue(array(
                new InputOption('--verbose', '-v', InputOption::VALUE_NONE, 'Increase verbosity of messages.'),
                new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'),
                new InputOption('--no-debug', null, InputOption::VALUE_NONE, 'Switches off debug mode.'),
        )));
        $this->application->expects($this->any())
            ->method('getKernel')
            ->will($this->returnValue($this->kernel));
        $this->application->expects($this->once())
            ->method('getHelperSet')
            ->will($this->returnValue($this->helperSet));
        $this->kernel->expects($this->any())
            ->method('getContainer')
            ->will($this->returnValue($this->container));

        $pdoMock = $this->getMockBuilder('Sonatra\Bundle\SessionBundle\Tests\Command\Fixtures\PDOMock')
            ->setMethods(array('prepare'))
            ->getMock();

        $this->container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function($p) use ($pdoMock) {
                if ($p === 'sonatra_session.pdo') {
                    return $pdoMock;
                }
            })
        );

        $this->command = new InitSessionPdoCommand();
        $this->command->setApplication($this->application);
    }

    public function testPdoExceptionNotParameter()
    {
        $this->createConfiguration(null);

        $this->setExpectedException('\Exception', 'You didn\'t fulfilled the \'session.pdo_dsn\' parameter under the sonatra_sessions section in your app config');
        $this->command->run(new ArrayInput(array()), new NullOutput());
    }

    public function testPdoExceptionWrongDsn()
    {
        $this->createConfiguration('dsn:');

        $pdoMock = $this->container->get('sonatra_session.pdo');

        $statmentMock = $this->getMockBuilder('\PDOStatement')
            ->setMethods(array('execute'))
            ->getMock();

        $statmentMock->expects($this->any())
            ->method('execute')
            ->will($this->returnCallback(function($p) {
                throw new \PDOException('Error pdo message for wrong dsn');
            }));

        $pdoMock->expects($this->any())
            ->method('prepare')
            ->will($this->returnValue($statmentMock)
        );

        $this->setExpectedException('\PDOException', 'Error pdo message for wrong dsn');
        $this->command->run(new ArrayInput(array()), new NullOutput());
    }

    public function testTableIsCreated()
    {
        $this->createConfiguration('dsn:');

        $pdoMock = $this->container->get('sonatra_session.pdo');

        $statmentMock = $this->getMockBuilder('\PDOStatement')
            ->setMethods(array('execute'))
            ->getMock();

        $statmentMock->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(true));

        $pdoMock->expects($this->any())
            ->method('prepare')
            ->will($this->returnValue($statmentMock)
        );

        $returnCode = $this->command->run(new ArrayInput(array()), new NullOutput());
        $this->assertEquals(0, $returnCode);
    }

    public function testTableIsAlreadyCreated()
    {
        $this->createConfiguration('dsn:');

        $pdoMock = $this->container->get('sonatra_session.pdo');

        $statmentMock = $this->getMockBuilder('\PDOStatement')
            ->setMethods(array('execute'))
            ->getMock();

        $statmentMock->expects($this->any())
            ->method('execute')
            ->will($this->returnCallback(function($p) {
                $ex = new \PDOException('Table aready exist');
                $ref = new \ReflectionClass($ex);
                $pCode = $ref->getProperty('code');
                $pCode->setAccessible(true);
                $pCode->setValue($ex, '42S01');

                throw $ex;
            }));

        $pdoMock->expects($this->any())
            ->method('prepare')
            ->will($this->returnValue($statmentMock)
        );

        $returnCode = $this->command->run(new ArrayInput(array()), new NullOutput());
        $this->assertEquals(0, $returnCode);
    }

    protected function createConfiguration($dsn)
    {
        $this->container->expects($this->any())
            ->method('getParameter')
            ->will($this->returnCallback(function($p) use ($dsn) {
                switch ($p) {
                    case 'sonatra_session.pdo.dsn':
                        return $dsn;

                    case 'sonatra_session.pdo.db_options':
                        return array(
                        'db_table'    => 'session',
                        'db_id_col'   => 'session_id',
                        'db_data_col' => 'session_value',
                        'db_time_col' => 'session_time',
                        );

                    default:
                        throw new \RuntimeException(sprintf('Unknown parameter "%s".', $p));
                }
            })
        );
    }
}
