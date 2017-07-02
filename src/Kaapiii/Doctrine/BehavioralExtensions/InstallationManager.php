<?php

namespace Kaapiii\Doctrine\BehavioralExtensions;

\Concrete\Core\Database\DatabaseStructureManager;
\Concrete\Core\Support\Facade\Config;
\Doctrine\Common\Annotations\AnnotationReader;
\Doctrine\Common\Annotations\CachedReader;
\Doctrine\Common\Cache\ArrayCache;
\Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
\Doctrine\ORM\EntityManager;
\Doctrine\ORM\Tools\Setup;
\Gedmo\DoctrineExtensions;

/**
 * Behavioral settings controller
 *
 * @author Markus Liechti <markus@liechti.io>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class InstallationManager
{

    public function installComponents()
    {
        
    }

    /**
     * 
     */
    protected function installBehavioralTables()
    {

        $this->registerPackageVendorAutoload();

        // Create new EntityManager which contains only the
        //mapping informations of the Doctrine Behavioral Extension
        $annotationReader = new AnnotationReader();
        $cachedAnnotationReader = new CachedReader($annotationReader, new \Doctrine\Common\Cache\ArrayCache());
        $driverChain = new MappingDriverChain();
        DoctrineExtensions::registerMappingIntoDriverChainORM($driverChain, $cachedAnnotationReader);

        $connection = $this->app->make('Concrete\Core\Database\Connection\Connection');
        $config = Setup::createConfiguration(
                        Config::get('concrete.cache.doctrine_dev_mode'), Config::get('database.proxy_classes'), new ArrayCache()
        );
        $config->setMetadataDriverImpl($driverChain);
        $em = EntityManager::create($connection, $config);

        // Create tables
        $structure = new DatabaseStructureManager($em);
        $structure->installDatabase();

        // Create proxies
        $this->generateProxyClasses($em);
    }

    /**
     * Generate proxies
     * 
     * @param \Doctrine\ORM\EntityManager $em
     */
    protected function generateProxyClasses($em)
    {
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        $em->getProxyFactory()->generateProxyClasses($metadata, $em->getConfiguration()->getProxyDir());
    }

    public function uninstallComponents()
    {
        
    }

    /**
     * Delete related tables if so is selected during the deinstallation
     */
    protected function deleteTables()
    {
        
    }
    
    /**
     * Delete related proxies
     */
    protected function deleteProxies()
    {
        
    }

}
