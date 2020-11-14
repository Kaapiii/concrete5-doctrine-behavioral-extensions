<?php

namespace Concrete\Package\Concrete5DoctrineBehavioralExtensions\Controller\SinglePage\Dashboard\System;

use Concrete\Core\Package\Package;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Page\Controller\DashboardSitePageController;
use Concrete\Core\Page\Page;
use Concrete\Core\Permission\Key\Key;
use Concrete\Core\Routing\Redirect;
use Doctrine\Common\EventManager;
use ReflectionClass;
use ReflectionException;

/**
 * Behavioral settings controller
 *
 * @author Markus Liechti <markus@liechti.io>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class DoctrineBehavioralExtensions extends DashboardSitePageController{
    
    /**
     * @var Package
     */
    private $package;

    /**
     * Constructor
     * 
     * @param Page $c
     */
    public function __construct(Page $c) {
        parent::__construct($c);
    }

    public function on_start()
    {
        parent::on_start();
        $this->package = $this->app->make(PackageService::class)->getClass('concrete5_doctrine_behavioral_extensions');
    }

    /**
     * Show settings page
     */
    public function view()
    {
        $canInstallPackages = true;

        $key = Key::getByHandle('install_packages');
        if (!$key->validate()) {
            $this->error->add(t('You do not have permission to edit this package settings. In order to alter the settings you need to have permission to install add-ons.'));
            $canInstallPackages = false;
        }

        $package = $this->app->make(PackageService::class)->getClass('concrete5_doctrine_behavioral_extensions');
        $config = $package->getConfig();

        $this->set('config', $config);

        $connection = $this->app->make('Concrete\Core\Database\Connection\Connection');
        $evm = $connection->getEventManager();
        
        $multilingualConfig = $this->getSite()->getConfigRepository()->get('multilingual.default_source_locale');
        
        $packagePath = $package->getPackagePath();
        $ormEventsElementPath = $packagePath.'/single_pages/dashboard/system/doctrine_behavioral_extensions/elements/ormEvents.php';
        
        $listenersPerBehavoir = $this->getListeners($evm);
        $this->set('listenersPerBehavoir', $listenersPerBehavoir);
        $this->set('ormEventsElementPath', $ormEventsElementPath);
        $this->set('defaultLanguageFalse', $multilingualConfig ? $multilingualConfig : false);
        $this->set('canInstallPackages', $canInstallPackages);
    }
    
    /**
     * update settings
     */
    public function updateSettings(){
        
        if ($this->token->validate('behavioral')) {
            if ($this->getRequest()->isPost()) {
                $isSluggableActive = $this->post('enable_sluggable') == 1 ? true : false;
                $transliterator = $this->post('transliterator');
                $transliteratorMethod = $this->post('transliterator_method');
                
                $isTimestampableActrive = $this->post('enable_timestampable') == 1 ? true : false;
                $isBlameableActive = $this->post('enable_blameable') == 1 ? true : false;
                $isSortableActive = $this->post('enable_sortable') == 1 ? true : false;
                $isTreeActive = $this->post('enable_tree') == 1 ? true : false;
                $isLoggableActive = $this->post('enable_loggable') == 1 ? true : false;
                $isTranslatableActive = $this->post('enable_translatable') == 1 ? true : false;
                $isDeletableActive = $this->post('enable_softDeletable') == 1 ? true : false;
                
                $config = $this->package->getConfig();
                
                $config->save('settings.sluggable.active', $isSluggableActive);
                $config->save('settings.sluggable.transliterator', $transliterator);
                $config->save('settings.sluggable.transliteratorMethod', $transliteratorMethod);
                
                $config->save('settings.timestampable.active', $isTimestampableActrive);
                $config->save('settings.blameable.active', $isBlameableActive);
                $config->save('settings.sortable.active', $isSortableActive);
                $config->save('settings.tree.active', $isTreeActive);
                $config->save('settings.loggable.active', $isLoggableActive);
                $config->save('settings.translatable.active', $isTranslatableActive);
                $config->save('settings.softDeletable.active', $isDeletableActive);

            }
        } else {
            $this->set('error', array($this->token->getErrorMessage()));
        }
        Redirect::to('/dashboard/system/doctrine_behavioral_extensions')->send();
    }
    
    /**
     * Get ORM listeners for each registered behavior
     * 
     * @param EventManager $evm
     *
     * @return array    contains all registered behaviors and their Doctrine2
     *                  ORM listeners
     *
     * @throws ReflectionException
     */
    public function getListeners($evm){
        $listeners = $evm->getListeners();
        
        $listenersPerBehavior = array();
        
        foreach($listeners as $event => $classes){
            if(count($classes)){
                foreach($classes as $class){
                    $reflection = new ReflectionClass($class);
                    $className = $reflection->getShortName();
                    $listenersPerBehavior[$className][] = $event;
                }
            }
        }
        return $listenersPerBehavior;
    }
}
