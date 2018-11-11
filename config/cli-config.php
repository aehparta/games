<?php

/*
 * This is for doctrine.
 */

require_once __DIR__ . '/../vendor/autoload.php';
$kernel = kernel::getInstance();
$kernel->load(__DIR__);
$em = get_entity_manager();
if (!$em) {
    throw new Exception('doctrine not configured, cannot fetch entity manager');
} else if (method_exists('Doctrine\ORM\Tools\Console\ConsoleRunner', 'createHelperSet')) {
    return Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($em);
} else {
    $helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
        'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
        'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em),
    ));
}
