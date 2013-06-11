<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_doctrine2\extensions\command;

use lithium\util\String;
use lithium\core\Libraries;
use lithium\util\Inflector;
use lithium\core\ClassNotFoundException;

/**
 * The `doctrine` integrates Doctrine console commands in to Lithium
 *
 * `li3 doctrine`
 *
 */
class Doctrine extends \lithium\console\Command {

    public function run($args = array()) {

        $plugin_path = dirname(dirname(dirname(__FILE__)));
        $root_path = dirname(dirname(dirname($plugin_path)));

        $path = $plugin_path . '/_source/doctrine2/';
        set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, array(
            $path . 'lib'
        )));

        require_once LITHIUM_APP_PATH . '/config/bootstrap/libraries.php';
        require_once LITHIUM_APP_PATH . '/config/bootstrap/connections.php';

        $connection = \lithium\data\Connections::get('default');

        $loader = new \Doctrine\Common\ClassLoader('Doctrine\DBAL\Migrations', $plugin_path . '/_source/migrations/lib');
        $loader->register();

        \Doctrine\ORM\Tools\Setup::registerAutoloadGit($plugin_path . '/_source/doctrine2');

        $em = $connection->getEntityManager();

        $helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
            'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
            'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em),
            'dialog' => new \Symfony\Component\Console\Helper\DialogHelper()
        ));


        $cli = new \Symfony\Component\Console\Application('Doctrine Command Line Interface', \Doctrine\ORM\Version::VERSION);
        $cli->setCatchExceptions(true);
        $cli->setHelperSet($helperSet);

        \Doctrine\ORM\Tools\Console\ConsoleRunner::addCommands($cli);

        $cli->addCommands(array(
            new \Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand(),
            new \Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand(),
            new \Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand(),
            new \Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand(),
            new \Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand(),
            new \Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand()
        ));

        $cli->run(new \Symfony\Component\Console\Input\ArrayInput(func_get_args()));
    }
}