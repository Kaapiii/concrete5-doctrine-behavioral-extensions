<?php

namespace Concrete\Package\Concrete5DoctrineBehavioralExtensions;

use Concrete\Core\Localization\Localization;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\User\User;
use Concrete\Core\Http\Request;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventManager;
use Gedmo\Blameable\BlameableListener;
use Gedmo\Loggable\LoggableListener;
use Gedmo\Sluggable\SluggableListener;
use Gedmo\Sortable\SortableListener;
use Gedmo\Translatable\TranslatableListener;
use Gedmo\Tree\TreeListener;
use Gedmo\Timestampable\TimestampableListener;


class Controller extends \Concrete\Core\Package\Package
{   
    
    const CUSTOM_NAMESPACE = '\Kaapiii\Doctrine\BehavioralExtensions';
    
    protected $pkgHandle          = 'concrete5_doctrine_behavioral_extensions';
    protected $appVersionRequired = '8.0.0';
    protected $pkgVersion         = '0.2.0';
    
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
        $pkg = parent::install();
         \Concrete\Core\Page\Single::add('/dashboard/system/doctrine_behavioral_extensions',$pkg);
    }

    public function on_start()
    {
        // register the autoloading
        require $this->getPackagePath() . '/vendor/autoload.php';

//        \Events::addListener('on_entity_manager_configure', function($event) {
//            
//            
//            // event contains the following Objects connection (database connection Object), configuration (ORM config Object), eventManager (ORM Event Manager)
//            $config = $event->getArgument('configuration');
//            $connection = $event->getArgument('connection');
//            $evm = $event->getArgument('eventManager');
//            
//            $cachedAnnotationReader = $this->app->make('orm/cachedAnnotationReader');
//
//            //$this->registerDoctrineBehavioralExtensions($evm, $cachedAnnotationReader);
//        });
        
        $em = $this->app->make('Doctrine\ORM\EntityManager');
        $evm = $em->getEventManager();
        $cachedAnnotationReader = $this->app->make('orm/cachedAnnotationReader');

        $this->registerDoctrineBehavioralExtensions($evm, $cachedAnnotationReader);
    }
    
    /**
     * Register Doctrine2 behavioral extensions
     *
     * @param EventManager $evm
     * @param Reader $cachedAnnotationReader
     */
    public function registerDoctrineBehavioralExtensions(EventManager $evm, Reader $cachedAnnotationReader)
    {
        
        $config = $this->getFileConfig();
        $user = new User();
        
        // Sluggable
        if($config->get('settings.sluggable.active')){
            $sluggableListener = new SluggableListener();
            $sluggableListener->setAnnotationReader($cachedAnnotationReader);
            // Register custom Sluggifiers (Replace Special Characters)
            if($config->get('settings.sluggable.transliterator')){
                $callable = $config->get('settings.sluggable.transliterator');
            }else{
                $callable = array('\Kaapiii\Doctrine\BehavioralExtensions\Translatable\Transliterator', 'replaceSecialSigns');
            }
            
            $sluggableListener->setTransliterator($callable);
            $evm->addEventSubscriber($sluggableListener);
        }
        
        // Tree
        if($config->get('settings.tree.active')){
            $treeListener = new TreeListener();
            $treeListener->setAnnotationReader($cachedAnnotationReader);
            $evm->addEventSubscriber($treeListener);
        }
        
        // Timestampable
        if($config->get('settings.timestampable.active')){
            $timestampableListener = new TimestampableListener();
            $timestampableListener->setAnnotationReader($cachedAnnotationReader);
            $evm->addEventSubscriber($timestampableListener);
        }
        
        // Blameable
        if($config->get('settings.blameable.active') && is_object($user)){
            $blameableListener = new BlameableListener();
            $blameableListener->setAnnotationReader($cachedAnnotationReader);
            if($user){
                $blameableListener->setUserValue($user->getUserID());
            }
            $evm->addEventSubscriber($blameableListener);
        }

        /**
         * @todo -> check if we want add the locale or just go with the language
         */
        // Translatable
        if($config->get('settings.translatable.active')){
            $defaultLocale = $this->getSiteConfig()->get('multilingual.default_source_locale'); // -> example "de_DE"
            
            // @todo - create setting
            $defaultLocale = substr($defaultLocale, 0, 2);
//            var_dump($defaultLocale);
//            die('ups');
            if(!empty($defaultLocale)){
                // Translatable
                $translatableListener = new TranslatableListener();
                $translatableListener->setAnnotationReader($cachedAnnotationReader);
                //$translatableListener->setTranslatableLocale($locale);
                $translatableListener->setDefaultLocale($defaultLocale);
                $translatableListener->setTranslationFallback(false);
                //$translatableListener->setPersistDefaultLocaleTranslation(false);

                $ms = Section::getCurrentSection();
                if(is_object($ms)){
                    //$locale = $ms->getLocale();
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
                $evm->addEventSubscriber($translatableListener);
            }
        }
        
        // Loggable
        if($config->get('settings.loggable.active')){
            $loggableListener = new LoggableListener;
            $loggableListener->setAnnotationReader($cachedAnnotationReader);
            if($user){
                // if not the user is not logged in, a empty user object is retured.
                $username = $user->getUserName() === NULL ? '' : $user->getUserName();
                $loggableListener->setUsername($username);
            }
            $evm->addEventSubscriber($loggableListener);
        }
        
        // Sortable
        if($config->get('settings.sortable.active')){
            $sortableListener = new SortableListener();
            $sortableListener->setAnnotationReader($cachedAnnotationReader);
            $evm->addEventSubscriber($sortableListener);
        }      
    }
    
    protected function getSiteConfig(){
        $site = $this->app->make('site')->getActiveSiteForEditing();
        return $site->getConfigRepository();
    }
}