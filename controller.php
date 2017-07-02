<?php

namespace Concrete\Package\Concrete5DoctrineBehavioralExtensions;

use Concrete\Core\Localization\Localization;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\User\User;
use Concrete\Core\Http\Request;
use Concrete\Core\Support\Facade\Events;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventManager;
use Gedmo\DoctrineExtensions;
use Gedmo\Blameable\BlameableListener;
use Gedmo\Loggable\LoggableListener;
use Gedmo\Sluggable\SluggableListener;
use Gedmo\Sortable\SortableListener;
use Gedmo\Translatable\TranslatableListener;
use Gedmo\Tree\TreeListener;
use Gedmo\Timestampable\TimestampableListener;

/**
 * Package controller
 *
 * @author Markus Liechti <markus@liechti.io>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Controller extends \Concrete\Core\Package\Package
{   
    
    const CUSTOM_NAMESPACE = '\Kaapiii\Doctrine\BehavioralExtensions';
    
    protected $pkgHandle          = 'concrete5_doctrine_behavioral_extensions';
    protected $appVersionRequired = '8.0.0';
    protected $pkgVersion         = '0.5.0';
    
    /**
     * @var \Doctrine\ORM\EntityManager 
     */
    protected $em;
    
    /**
     * @var \Doctrine\Common\EventManager 
     */
    protected $evm;
    
    /**
     * @var \Doctrine\Common\Annotations\CachedReader 
     */
    protected $cachedAnnotationReader;
    
    /**
     * @var \Concrete\Core\Config\Repository\Liaison
     */
    protected $config;
    
    /**
     * @var \Concrete\Core\User\User 
     */
    protected $user;
    
    /**
     * Register the custom namespace
     * 
     * @var array
     */
    protected $pkgAutoloaderRegistries = array(
        'src/Kaapiii/Doctrine/BehavioralExtensions' => self::CUSTOM_NAMESPACE,
    );

    
    public function getPackageDescription()
    {
        return t('Package add support for Doctrine2 behavioral extensions aka (Gedmo Extensions)');
    }

    public function getPackageName()
    {
        return t('Doctrine2 behavioral extensions');
    }
    
    public function install()
    {
        $this->installBehavioralTables();
        $pkg = parent::install();
        \Concrete\Core\Page\Single::add('/dashboard/system/doctrine_behavioral_extensions',$pkg);
    }

    public function on_start()
    {
        $this->registerDoctrineBehavioralExtensions();
    }
    
    public function uninstall()
    {
        $this->registerDoctrineBehavioralExtensions();
        parent::uninstall();
    }
    
    /**
     * Register Doctrine2 behavioral extensions
     *
     * @param EventManager $evm
     * @param Reader $cachedAnnotationReader
     */
    public function registerDoctrineBehavioralExtensions()
    {   
        $this->registerPackageVendorAutoload();
        
        $this->em = $this->app->make('Doctrine\ORM\EntityManager');
        $this->evm = $this->em->getEventManager();
        $this->cachedAnnotationReader = $this->app->make('orm/cachedAnnotationReader');
        $this->config = $this->getFileConfig();
        $this->user = new User();
        $driverChain = $this->em->getConfiguration()->getMetadataDriverImpl();

        DoctrineExtensions::registerMappingIntoDriverChainORM($driverChain, $this->cachedAnnotationReader);
        $this->registerSortable();
        $this->registerSluggable();
        $this->registerTree();
        $this->registerBlamable();
        $this->registerTimestampable();
        $this->registerTranslatable();
        $this->registerLoggable();   
    }
    
    /**
     * Register the autoloading
     * Note: By wrapping the autoloader include call in a file_exists 
     * function, the package installation will also work by adding it 
     * to the projects composer.json
     */
    protected function registerPackageVendorAutoload(){
        // Register the autoloading
        // Note: By wrapping the autoloader include call in a file_exists 
        // function, the package installation will also work by adding it to
        // the projects composer.json
        if(file_exists($this->getPackagePath() . '/vendor/autoload.php')){
            require $this->getPackagePath() . '/vendor/autoload.php';
        }
    }
    
    /**
     * Install tables
     */
    protected function installBehavioralTables(){
        
        $this->registerPackageVendorAutoload();
        
        $annotationReader = new \Doctrine\Common\Annotations\AnnotationReader();
        $cachedAnnotationReader = new \Doctrine\Common\Annotations\CachedReader($annotationReader, new \Doctrine\Common\Cache\ArrayCache());
        $driverChain = new \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain();
        \Gedmo\DoctrineExtensions::registerMappingIntoDriverChainORM($driverChain, $cachedAnnotationReader);
        
        $connection = $this->app->make('Concrete\Core\Database\Connection\Connection');
        $config = \Doctrine\ORM\Tools\Setup::createConfiguration(
            \Concrete\Core\Support\Facade\Config::get('concrete.cache.doctrine_dev_mode'),
            \Concrete\Core\Support\Facade\Config::get('database.proxy_classes'),
            new \Doctrine\Common\Cache\ArrayCache()
        );
        $config->setMetadataDriverImpl($driverChain);
        $em = \Doctrine\ORM\EntityManager::create($connection, $config);
        
        $structure = new \Concrete\Core\Database\DatabaseStructureManager($em);
        $structure->installDatabase();

        // Create or update entity proxies
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        $em->getProxyFactory()->generateProxyClasses($metadata, $em->getConfiguration()->getProxyDir());
    }
    
    /**
     * Register sortable listener
     */
    protected function registerSortable(){
        // Sortable
        if($this->config->get('settings.sortable.active')){
            $sortableListener = new SortableListener();
            $sortableListener->setAnnotationReader($this->cachedAnnotationReader);
            $this->evm->addEventSubscriber($sortableListener);
        }
    }
    
    /**
     * Register sluggable listener
     */
    protected function registerSluggable(){
        // Sluggable
        if($this->config->get('settings.sluggable.active')){
            $sluggableListener = new SluggableListener();
            $sluggableListener->setAnnotationReader($this->cachedAnnotationReader);
            // Register custom Sluggifiers (Replace Special Characters)
            if($this->config->get('settings.sluggable.transliterator')){
                $callable = $this->config->get('settings.sluggable.transliterator');
            }else{
                $callable = array('\Kaapiii\Doctrine\BehavioralExtensions\Translatable\Transliterator', 'replaceSecialSigns');
            }

            $sluggableListener->setTransliterator($callable);
            $this->evm->addEventSubscriber($sluggableListener);
        }
    }
    
    /**
     * Register tree listener
     */
    protected function registerTree(){
        // Tree
        if($this->config->get('settings.tree.active')){
            $treeListener = new TreeListener();
            $treeListener->setAnnotationReader($this->cachedAnnotationReader);
            $this->evm->addEventSubscriber($treeListener);
        }
    }
    
    /**
     * Register timestampable listener
     */
    protected function registerTimestampable(){
        // Timestampable
        if($this->config->get('settings.timestampable.active')){
            $timestampableListener = new TimestampableListener();
            $timestampableListener->setAnnotationReader($this->cachedAnnotationReader);
            $this->evm->addEventSubscriber($timestampableListener);
        }
    }
    
    /**
     * Register blameable listener
     */
    protected function registerBlamable(){
        // Blameable
        if($this->config->get('settings.blameable.active') && is_object($this->user)){
            $blameableListener = new BlameableListener();
            $blameableListener->setAnnotationReader($this->cachedAnnotationReader);
            if($this->user){
                $blameableListener->setUserValue($this->user->getUserID());
            }
            $this->evm->addEventSubscriber($blameableListener);
        }
    }
    
    /**
     * Register translatable listener
     */
    protected function registerTranslatable()
    {
        if($this->config->get('settings.translatable.active')){
            $defaultSourceLocale = $this->getSiteConfig()->get('multilingual.default_source_locale'); // -> example "de_DE"

            $defaultLocale = substr($defaultSourceLocale, 0, 2);
            if(!empty($defaultLocale)){
                // Translatable
                $translatableListener = new TranslatableListener();
                $translatableListener->setAnnotationReader($this->cachedAnnotationReader);
                $translatableListener->setDefaultLocale($defaultLocale);
                $translatableListener->setTranslationFallback(false);

                $ms = Section::getCurrentSection();
                if(is_object($ms)){
                    $language = $ms->getLanguage();
                    $translatableListener->setTranslatableLocale($language);
                }else{
                    // Get default section
                    $msd = Section::getDefaultSection();
                    $requestLocale = Request::request('locale');
                    if(is_object($msd)){
                        $translatableListener->setTranslatableLocale($defaultLocale);
                    }elseif (!empty($requestLocale)){
                        // Check if locale in request is set. This ist needed for API calls with ajax
                        $translatableListener->setTranslatableLocale($requestLocale);
                    }else{
                        $fallbackLanguage = substr(Localization::BASE_LOCALE, 0, 2);
                        $translatableListener->setTranslatableLocale($fallbackLanguage);
                    }
                }
                $this->evm->addEventSubscriber($translatableListener);
            }
        }
    }
    
    /**
     * Register loggable listener
     */
    protected function registerLoggable(){
        // Loggable
        if($this->config->get('settings.loggable.active')){
            $loggableListener = new LoggableListener;
            $loggableListener->setAnnotationReader($this->cachedAnnotationReader);
            if($this->user){
                // if not the user is not logged in, a empty user object is retured.
                $username = $this->user->getUserName() === NULL ? '' : $this->user->getUserName();
                $loggableListener->setUsername($username);
            }
            $this->evm->addEventSubscriber($loggableListener);
        }
    }

    protected function getSiteConfig(){
        $site = $this->app->make('site')->getActiveSiteForEditing();
        return $site->getConfigRepository();
    }
}
