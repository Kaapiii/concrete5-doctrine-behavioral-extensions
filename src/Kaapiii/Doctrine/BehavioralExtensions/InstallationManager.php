<?php

namespace Kaapiii\Doctrine\BehavioralExtensions;

\Concrete\Core\Database\DatabaseStructureManager;
\Concrete\Core\Support\Facade\Config;
\Doctrine\Common\Annotations\AnnotationReader;
\Doctrine\Common\Annotations\CachedReader;
\Doctrine\Common\Cache\ArrayCache;
\Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
\Doctrine\ORM\EntityManager;
\Doctrine\ORM\EntityManagerInterface;
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
        $this->registerPackageVendorAutoload();
        
        $em = $this->getEntityManagerForInstallation();
        $this->installBehavioralTables($em);
        $this->generateProxyClasses($em);
        
    }
     
    public function uninstallComponents()
    {
        
    }
    
    /**
     * Get EntityManager which contains only the gedmo mapping information
     * 
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManagerForInstallation()
    {
        //$this->registerPackageVendorAutoload();

        // Create new temporary EntityManager which contains only the
        // mapping informations of the Doctrine Behavioral Extension. 
        // It's used only during the installation.
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
        
        return $em;
    }

    /**
     * Create tables according to the entity managers metadata
     */
    protected function installBehavioralTables(EntityManagerInterface $em)
    {
        // Create tables
        $structure = new DatabaseStructureManager($em);
        $structure->installDatabase();
    }

    /**
     * Generate proxies
     * 
     * @param \Doctrine\ORM\EntityManager $em
     */
    protected function generateProxyClasses(EntityManagerInterface $em)
    {
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        $em->getProxyFactory()->generateProxyClasses($metadata, $em->getConfiguration()->getProxyDir());
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
