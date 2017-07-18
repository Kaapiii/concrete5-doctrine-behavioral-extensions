<?php

namespace Concrete\Package\Concrete5DoctrineBehavioralExtensions\Controller\SinglePage\Dashboard\System;

use Concrete\Core\Package\Package;
use TaskPermission;

/**
 * Behavioral settings controller
 *
 * @author Markus Liechti <markus@liechti.io>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class DoctrineBehavioralExtensions extends \Concrete\Core\Page\Controller\DashboardSitePageController{
    
    /**
     * @var Concrete\Core\Package\Package 
     */
    private $package;
    
    
    /**
     * Constructor
     * 
     * @param \Concrete\Core\Page\Page $c
     */
    public function __construct(\Concrete\Core\Page\Page $c) {
        parent::__construct($c);
        $this->package = Package::getByHandle('concrete5_doctrine_behavioral_extensions');
    }
    
    /**
     * Show settings page
     */
    public function view(){
        
        $tp = new TaskPermission();
        $canInstallPackages = $tp->canInstallPackages();
        if (!$canInstallPackages){
            $this->error->add(t('You do not have permission to edit this package settings. In order to alter the settings you need to have permission to install add-ons.'));
        }
        
        $package = Package::getByHandle('concrete5_doctrine_behavioral_extensions');
        $config = $package->getFileConfig();
        
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
            if ($this->isPost()) {
                $isSluggableActive = $this->post('eneable_sluggable') == 1 ? true : false;
                $transliterator = $this->post('transliterator');
                $transliteratorMethod = $this->post('transliterator_method');
                
                $isTimestampableActrive = $this->post('eneable_timestampable') == 1 ? true : false;
                $isBlameableActive = $this->post('eneable_blameable') == 1 ? true : false;
                $isSortableActive = $this->post('eneable_sortable') == 1 ? true : false;
                $isTreeActive = $this->post('eneable_tree') == 1 ? true : false;
                $isLoggableActive = $this->post('eneable_loggable') == 1 ? true : false;
                $isTranslatableActive = $this->post('eneable_translatable') == 1 ? true : false;
                
                $config = $this->package->getFileConfig();
                
                $config->save('settings.sluggable.active', $isSluggableActive);
                $config->save('settings.sluggable.transliterator', $transliterator);
                $config->save('settings.sluggable.transliteratorMethod', $transliteratorMethod);
                
                $config->save('settings.timestampable.active', $isTimestampableActrive);
                $config->save('settings.blameable.active', $isBlameableActive);
                $config->save('settings.sortable.active', $isSortableActive);
                $config->save('settings.tree.active', $isTreeActive);
                $config->save('settings.loggable.active', $isLoggableActive);
                $config->save('settings.translatable.active', $isTranslatableActive);
                
            }
        } else {
            $this->set('error', array($this->token->getErrorMessage()));
        }
        $this->redirect('/dashboard/system/doctrine_behavioral_extensions');
    }
    
    /**
     * Get ORM listeners for each registerd behavior
     * 
     * @param \Doctrine\Common\EventManager $evm
     * @return array    contains all registerd behavoirs and their Doctrine2
     *                  ORM listeners
     */
    public function getListeners($evm){
        $listeners = $evm->getListeners();
        
        $listenersPerBehavior = array();
        
        foreach($listeners as $event => $classes){
            if(count($classes)){
                foreach($classes as $class){
                    $reflection = new \ReflectionClass($class);
                    $className = $reflection->getShortName();
                    $listenersPerBehavior[$className][] = $event;
                }
            }
        }
        return $listenersPerBehavior;
    }
}
