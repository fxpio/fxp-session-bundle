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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

/**
 * This command initializes the session table in database.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class InitSessionPdoCommand extends Command
{
    private $handler;

    public function __construct(PdoSessionHandler $pdoHandler)
    {
        parent::__construct();

        $this->handler = $pdoHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('init:session:pdo')
            ->setDescription('Initializes the PDO session storage')
            ->setHelp(
            <<<'EOT'
The <info>init:session:pdo</info> command initializes the PDO Session.
EOT
        )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        try {
            $this->handler->createTable();
            $output->writeln(['', '  The table for PDO session is created.']);
        } catch (\PDOException $ex) {
            // Mysql and PostgreSQL already table exist code
            if (!\in_array($ex->getCode(), ['42S01', '42P07'], true)) {
                throw $ex;
            }

            $output->writeln(['', '  The table for PDO session is already exists.']);
        }
    }
}
