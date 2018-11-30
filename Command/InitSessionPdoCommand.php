<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SessionBundle\Command;

use Fxp\Bundle\SessionBundle\Exception\InvalidConfigurationException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command initializes the session table in database.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
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
        ->setHelp(<<<'EOT'
The <info>init:session:pdo</info> command initializes the PDO Session.
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->getContainer()->has('fxp_session.handler.pdo')) {
            throw new InvalidConfigurationException("The PDO Handler must be enabled in the config 'fxp_session.pdo.enabled'");
        }

        try {
            $handler = $this->getContainer()->get('fxp_session.handler.pdo');
            $handler->createTable();
            $output->writeln(['', '  The table for PDO session is created.']);
        } catch (\PDOException $ex) {
            // Mysql and PostgreSQL already table exist code
            if (!\in_array($ex->getCode(), ['42S01', '42P07'])) {
                throw $ex;
            }

            $output->writeln(['', '  The table for PDO session is already exists.']);
        }
    }
}
