<?php

namespace Kaapiii\Doctrine\BehavioralExtensions;

use Concrete\Core\Application\Application;
use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Database\DatabaseStructureManager;
use Concrete\Core\Support\Facade\Config;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Common\Proxy\ProxyGenerator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use Gedmo\DoctrineExtensions;

/**
 * Behavioral settings controller
 *
 * @author Markus Liechti <markus@liechti.io>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class InstallationManager implements ApplicationAwareInterface
{
    
    /**
     * @var $app
     */
    protected $app;
    
    /**
     * Constructor
     * 
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->setApplication($application);
    }
    
    /**
     * Set application
     * 
     * @param Application $application
     */
    public function setApplication(Application $application)
    {
        $this->app = $application;
    }

    /**
     * Install/create related components
     *
     * @throws ORMException
     */
    public function installComponents()
    {   
        $em = $this->getEntityManagerForInstallation();
        $this->installBehavioralTables($em);
        $this->generateProxyClasses($em);
    }

    /**
     * Uninstall related components
     *
     * @throws ORMException
     */
    public function uninstallComponents()
    {
        $em = $this->getEntityManagerForInstallation();
        $this->deleteProxies($em);
    }

    /**
     * Get EntityManager which contains only the gedmo mapping information
     *
     * @return EntityManager
     *
     * @throws ORMException
     */
    protected function getEntityManagerForInstallation()
    {
        // Create new temporary EntityManager which contains only the
        // mapping information of the Doctrine Behavioral Extension.
        // It's used only for the table creation during the package installation.
        $annotationReader = new AnnotationReader();
        $cachedAnnotationReader = new CachedReader($annotationReader, new ArrayCache());
        $driverChain = new MappingDriverChain();
        DoctrineExtensions::registerMappingIntoDriverChainORM($driverChain, $cachedAnnotationReader);

        $connection = $this->app->make('Concrete\Core\Database\Connection\Connection');
        $config = Setup::createConfiguration(
            Config::get('concrete.cache.doctrine_dev_mode'), Config::get('database.proxy_classes'), new ArrayCache()
        );
        $config->setMetadataDriverImpl($driverChain);
        return EntityManager::create($connection, $config);
    }

    /**
     * Create tables according to the entity managers metadata
     *
     * @param EntityManagerInterface $em
     */
    protected function installBehavioralTables(EntityManagerInterface $em)
    {
        // Create tables
        $structure = new DatabaseStructureManager($em);
        $structure->installDatabase();
    }

    /**
     * Generate proxies according to the entity managers metadata
     *
     * @param EntityManagerInterface $em
     */
    protected function generateProxyClasses(EntityManagerInterface $em)
    {
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        $em->getProxyFactory()->generateProxyClasses($metadata, $em->getConfiguration()->getProxyDir());
    }

    /**
     * Delete package related proxies
     * 
     * @param EntityManagerInterface $em
     */
    protected function deleteProxies(EntityManagerInterface $em)
    {
        $config = $em->getConfiguration();
        $proxyGenerator = new ProxyGenerator($config->getProxyDir(), $config->getProxyNamespace());

        $classes = $em->getMetadataFactory()->getAllMetadata();
        foreach ($classes as $class) {
            $proxyFileName = $proxyGenerator->getProxyFileName($class->getName(), $config->getProxyDir());
            if (file_exists($proxyFileName)) {
                @unlink($proxyFileName);
            }
        }
    }
}
