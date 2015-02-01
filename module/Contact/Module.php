<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Contact;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Contact\Logger' => function ($sm) {
                        $log = new \Zend\Log\Logger();
                        $writer = new \Zend\Log\Writer\Stream('data/logs/contacts');
                        $log->addWriter($writer);
                        return $log;
                    },
                'Contact\Model\ContactsTable' => function ($sm) {
                        $tableGateway = $sm->get('ContactsTableGateway');
                        $logger = $sm->get('Contact\Logger');
                        $table = new Model\ContactsTable($tableGateway, $logger);
                        return $table;
                    },
                'ContactsTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Contact\Model\Contact());
                        return new TableGateway('contacts', $dbAdapter, null, $resultSetPrototype);
                    },
            )
        );
    }
}
