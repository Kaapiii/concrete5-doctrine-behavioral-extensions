<?php

namespace Concrete\Package\Concrete5DoctrineBehavioralExtensions;

use Concrete\Core\Package\Package;
use Kaapiii\Doctrine\BehavioralExtensions\ListenerConroller;
use Kaapiii\Doctrine\BehavioralExtensions\InstallationManager;

/**
 * Package controller
 *
 * @author Markus Liechti <markus@liechti.io>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Controller extends Package
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
        $this->registerPackageVendorAutoload();
        $installationManager = new InstallationManager($this->app);
        $installationManager->installComponents();
        $pkg = parent::install();
        \Concrete\Core\Page\Single::add('/dashboard/system/doctrine_behavioral_extensions',$pkg);
    }

    public function on_start()
    {   
        $this->registerPackageVendorAutoload();
        $listenerCtrl = new ListenerConroller($this->app, $this->getFileConfig());
        $listenerCtrl->registerDoctrineBehavioralExtensions();
    }
    
    public function uninstall()
    {   
        $this->registerPackageVendorAutoload();
        $installationManager = new InstallationManager($this->app);
        $installationManager->uninstallComponents();
        parent::uninstall();
    }
     
    /**
     * Register the autoloading
     * Note: By wrapping the autoloader include call in a file_exists 
     * function, the package installation will also work by adding it 
     * to the projects composer.json
     */
    protected function registerPackageVendorAutoload(){
        if(file_exists($this->getPackagePath() . '/vendor/autoload.php')){
            require $this->getPackagePath() . '/vendor/autoload.php';
        }
    }
}
