<?php

namespace Concrete\Package\Concrete5DoctrineBehavioralExtensions;

use Concrete\Core\Package\Package;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Page\Single;
use Doctrine\ORM\ORMException;
use Kaapiii\Doctrine\BehavioralExtensions\ListenerController;
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

    /**
     * @var string
     */
    protected $pkgHandle  = 'concrete5_doctrine_behavioral_extensions';

    /**
     * @var string
     */
    protected $appVersionRequired = '8.5.0';

    /**
     * @var string
     */
    protected $pkgVersion         = '2.0.0';

    /**
     * Register the custom namespace
     * 
     * @var array
     */
    protected $pkgAutoloaderRegistries = array(
        'src/Kaapiii/Doctrine/BehavioralExtensions' => self::CUSTOM_NAMESPACE,
    );

    /**
     * @return string
     */
    public function getPackageDescription()
    {
        return t('Package add support for Doctrine2 behavioral extensions aka (Gedmo Extensions)');
    }

    /**
     * @return string
     */
    public function getPackageName()
    {
        return t('Doctrine2 behavioral extensions');
    }

    /**
     * @return \Concrete\Core\Entity\Package|void
     */
    public function install()
    {
        $this->registerPackageVendorAutoload();
        $installationManager = new InstallationManager($this->app);
        $installationManager->installComponents();
        $pkg = parent::install();
        Single::add('/dashboard/system/doctrine_behavioral_extensions', $pkg);
        $this->installConfig();
    }

    /**
     * @throws ORMException
     */
    public function on_start()
    {
        $this->registerPackageVendorAutoload();
        $listenerCtrl = new ListenerController($this->app, $this->getConfig());
        $listenerCtrl->registerDoctrineBehavioralExtensions();
    }

    /**
     * Upgrade logic
     */
    public function upgrade()
    {
        parent::upgrade();

        /** @var \Concrete\Core\Entity\Package $packageEntity */
        $packageEntity = $this->app->make(PackageService::class)->getByHandle($this->pkgHandle);
        $version = $packageEntity->getPackageVersion();

        /**
         * Only Upgrade if installed package version was <= 1.0.2
         */
        if (version_compare($version, '1.0.2', '>')) {
            $this->switchFromFileConfigToDbConfig();
        }

        /**
         * Only run if upgrading to version 2.0.0 from <=1.1.0
         */
        if (version_compare($version, '1.1.0', '>')) {
            $this->updateActivateSoftDeletable();
        }
    }

    /**
     * Uninstall logic
     */
    public function uninstall()
    {
        $this->registerPackageVendorAutoload();
        $installationManager = new InstallationManager($this->app);
        $installationManager->uninstallComponents();
        parent::uninstall();
    }

    /**
     * Register the autoloader
     * Note: By wrapping the autoloader include call in a file_exists 
     * function, the package installation will also work by adding it 
     * to the projects composer.json
     */
    protected function registerPackageVendorAutoload()
    {
        if (file_exists($this->getPackagePath().'/vendor/autoload.php')) {
            require $this->getPackagePath().'/vendor/autoload.php';
        }
    }

    /**
     * Set the package config
     */
    protected function installConfig()
    {
        $config = $this->getConfig();
        $config->save('settings.sluggable.active', true);
        $config->save('settings.sluggable.transliterator', ListenerController::DEFAULT_TRANSLITERATOR);
        $config->save('settings.sluggable.transliteratorMethod', ListenerController::DEFAULT_TRANSLITERATOR_METHOD);

        $config->save('settings.timestampable.active', true);
        $config->save('settings.blameable.active', true);
        $config->save('settings.sortable.active', true);
        $config->save('settings.tree.active', true);
        $config->save('settings.loggable.active', true);
        $config->save('settings.translatable.active', true);
        $config->save('settings.softDeletable.active', true);
    }

    /**
     * Switch from file config to database config
     */
    private function switchFromFileConfigToDbConfig()
    {
        $fileConfig = $this->getFileConfig();

        // Only run if file config exists
        if (!is_object($fileConfig)) {
            return;
        }

        $sluggable = $fileConfig->get('settings.sluggable.active');
        $sluggableTranslit = $fileConfig->get('settings.sluggable.transliterator');
        $sluggableTranslitMethod = $fileConfig->get('settings.sluggable.transliteratorMethod');
        $timestampable = $fileConfig->get('settings.timestampable.active');
        $blameable = $fileConfig->get('settings.blameable.active');
        $sortable = $fileConfig->get('settings.sortable.active');
        $tree = $fileConfig->get('settings.tree.active');
        $loggable = $fileConfig->get('settings.loggable.active');
        $translatable = $fileConfig->get('settings.translatable.active');

        $config = $this->getConfig();
        if (!is_null($sluggable)){
            $config->save('settings.sluggable.active', $sluggable);
        }
        if (!is_null($sluggableTranslit)){
            $config->save('settings.sluggable.transliterator', $sluggableTranslit);
        }
        if (!is_null($sluggableTranslitMethod)){
            $config->save('settings.sluggable.transliteratorMethod', $sluggableTranslitMethod);
        }
        if (!is_null($timestampable)){
            $config->save('settings.timestampable.active', $timestampable);
        }
        if (!is_null($blameable)){
            $config->save('settings.blameable.active', $blameable);
        }
        if (!is_null($sortable)){
            $config->save('settings.sortable.active', $sortable);
        }
        if (!is_null($tree)){
            $config->save('settings.tree.active', $tree);
        }
        if (!is_null($loggable)){
            $config->save('settings.loggable.active', $loggable);
        }
        if (!is_null($translatable)){
            $config->save('settings.translatable.active', $translatable);
        }

        $this->removeConfigFile();
    }

    public function updateActivateSoftDeletable()
    {
        $config = $this->getConfig();
        $config->save('settings.softDeletable.active', 1);
    }

    /**
     * Remove package config file
     */
    public function removeConfigFile()
    {
        $this->app->make('config')->clearNamespace($this->getPackageHandle());
    }
}
