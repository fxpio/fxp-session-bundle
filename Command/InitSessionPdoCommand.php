<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SessionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sonatra\Bundle\SessionBundle\Exception\InvalidConfigurationException;

/**
 * This command initializes the session table in database.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class InitSessionPdoCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('init:session:pdo')
        ->setDescription('Initializes the PDO session storage')
        ->setHelp(<<<EOT
The <info>init:session:pdo</info> command initializes the PDO Session.
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->getContainer()->has('sonatra_session.handler.pdo')) {
            throw new InvalidConfigurationException("The PDO Handler must be enabled in the config 'sonatra_session.pdo.enabled'");
        }

        try {
            $handler = $this->getContainer()->get('sonatra_session.handler.pdo');
            $handler->createTable();
            $output->writeln(array('', "  The table for PDO session is created."));
        } catch (\PDOException $ex) {
            // Mysql and PostgreSQL already table exist code
            if (!in_array($ex->getCode(), array('42S01', '42P07'))) {
                throw $ex;
            }

            $output->writeln(array('', "  The table for PDO session is already exists."));
        }
    }
}
