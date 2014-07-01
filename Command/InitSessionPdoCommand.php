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
        $dsn = $this->getContainer()->getParameter('sonatra_session.pdo.dsn');

        if (null === $this->getContainer()->getParameter('sonatra_session.pdo.dsn')) {
            throw new InvalidConfigurationException("You didn't fulfilled the 'session.pdo_dsn' parameter under the sonatra_sessions section in your app config");
        }

        $driver = substr($dsn, 0, strpos($dsn, ':'));

        if (!in_array($driver, array('pgsql', 'mysql'))) {
            throw new InvalidConfigurationException(sprintf('Only DSN driver "pgsql" or "mysql" are supported ("%s" currently)', $driver));
        }

        $pdoOptions = $this->getContainer()->getParameter('sonatra_session.pdo.db_options');

        $db_table = $pdoOptions['db_table'];
        $db_id_col = $pdoOptions['db_id_col'];
        $db_data_col = $pdoOptions['db_data_col'];
        $db_time_col = $pdoOptions['db_time_col'];

        if ('pgsql' === $driver) {
            $tableSql = "CREATE TABLE $db_table (
                $db_id_col character varying(255) NOT NULL,
                $db_data_col text NOT NULL,
                $db_time_col integer NOT NULL,
                CONSTRAINT session_pkey PRIMARY KEY ($db_id_col)
            );";

        } else {
            $tableSql = "CREATE TABLE `$db_table` (
                    `$db_id_col` varchar(255) NOT NULL,
                    `$db_data_col` text NOT NULL,
                    `$db_time_col` int(11) NOT NULL,
                            PRIMARY KEY (`$db_id_col`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        }

        $pdo = $this->getContainer()->get('sonatra_session.pdo');

        try {
            $pdo->prepare($tableSql)->execute();
            $output->writeln(array('', "  Table <info>$db_table</info> for PDO session is created."));

        } catch (\PDOException $ex) {
            // Mysql and PostgreSQL already table exist code
            if (!in_array($ex->getCode(), array('42S01', '42P07'))) {
                throw $ex;
            }

            $output->writeln(array('', "  Table <info>$db_table</info> for PDO session is already exists."));
        }
    }
}
