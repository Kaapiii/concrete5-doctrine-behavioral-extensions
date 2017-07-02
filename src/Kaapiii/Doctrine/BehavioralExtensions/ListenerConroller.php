<?php

namespace Kaapiii\Doctrine\BehavioralExtensions;

use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Config\Repository\Liaison;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventManager;
use Gedmo\Blameable\BlameableListener;
use Gedmo\Loggable\LoggableListener;
use Gedmo\Sluggable\SluggableListener;
use Gedmo\Sortable\SortableListener;
use Gedmo\Translatable\TranslatableListener;
use Gedmo\Tree\TreeListener;
use Gedmo\Timestampable\TimestampableListener;

/**
 * ListenerConroller
 *
 * @author Markus Liechti <markus@liechti.io>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class ListenerConroller implements ApplicationAwareInterface
{
    
    use ApplicationAwareTrait;
    
    /**
     * @var $app
     */
    protected $app;
    
    /**
     * @var Doctrine\Common\EventManager
     */
    protected $evm;
    
    /**
     * @var Doctrine\Common\Annotations\CachedReader 
     */
    protected $cachedAnnotaionReader;
    
    /**
     * @var Concrete\Core\Config\Repository\Liaison 
     */
    protected $config;
    
    /**
     *
     * @var type 
     */
    protected $user;
    
    /**
     * Constructor
     * 
     * @param ApplicationAwareInterface $app
     * @param Liaison $config
     */
    public function __construct(ApplicationAwareInterface $app, $config)
    {   
        
        $this->setApplication($app);
        
        $this->evm = $this->app->make('Doctrine\ORM\EntityManager');;
        $this->cachedAnnotationReader = $this->app->make('orm/cachedAnnotationReader');;
        $this->config = $config;
        $this->user = $user;
    }

    /**
     * Register Doctrine2 behavioral extensions
     *
     * @param EventManager $evm
     * @param Reader $cachedAnnotationReader
     */
    public function registerDoctrineBehavioralExtensions()
    {


    }

    protected function registerSluggable()
    {
        // Sluggable
        if ($this->config->get('settings.sluggable.active')) {
            $sluggableListener = new SluggableListener();
            $sluggableListener->setAnnotationReader($this->cachedAnnotationReader);
            // Register custom Sluggifiers (Replace Special Characters)
            if ($this->config->get('settings.sluggable.transliterator')) {
                $callable = $this->config->get('settings.sluggable.transliterator');
            } else {
                $callable = array('\Kaapiii\Doctrine\BehavioralExtensions\Translatable\Transliterator', 'replaceSecialSigns');
            }

            $sluggableListener->setTransliterator($callable);
            $this->evm->addEventSubscriber($sluggableListener);
        }
    }

    protected function registerTree()
    {
        if ($this->config->get('settings.tree.active')) {
            $treeListener = new TreeListener();
            $treeListener->setAnnotationReader($this->cachedAnnotationReader);
            $this->evm->addEventSubscriber($treeListener);
        }
    }

    protected function registerTimestampable()
    {
        if ($this->config->get('settings.timestampable.active')) {
            $timestampableListener = new TimestampableListener();
            $timestampableListener->setAnnotationReader($this->cachedAnnotationReader);
            $this->evm->addEventSubscriber($timestampableListener);
        }
    }

    protected function registerBlameable()
    {
        if ($this->config->get('settings.blameable.active') && is_object($this->user)) {
            $blameableListener = new BlameableListener();
            $blameableListener->setAnnotationReader($this->cachedAnnotationReader);
            if ($this->user) {
                $blameableListener->setUserValue($this->user->getUserID());
            }
            $this->evm->addEventSubscriber($blameableListener);
        }
    }

    protected function registerTranslatable()
    {
        if ($this->config->get('settings.translatable.active')) {
            $defaultSourceLocale = $this->getSiteConfig()->get('multilingual.default_source_locale'); // -> example "de_DE"

            $defaultLocale = substr($defaultSourceLocale, 0, 2);
            if (!empty($defaultLocale)) {
                // Translatable
                $translatableListener = new TranslatableListener();
                $translatableListener->setAnnotationReader($this->cachedAnnotationReader);
                $translatableListener->setDefaultLocale($defaultLocale);
                $translatableListener->setTranslationFallback(false);

                $ms = Section::getCurrentSection();
                if (is_object($ms)) {
                    // Check if the current language of the loaded page
                    $language = $ms->getLanguage();
                    $translatableListener->setTranslatableLocale($language);
                } else {
                    // Get default section
                    $msd = Section::getDefaultSection();
                    $requestLocale = Request::request('locale');
                    if (is_object($msd)) {
                        $translatableListener->setTranslatableLocale($defaultLocale);
                    } elseif (!empty($requestLocale)) {
                        // Check if locale in request is set. This ist needed for API calls with ajax
                        $translatableListener->setTranslatableLocale($requestLocale);
                    } else {
                        $fallbackLanguage = substr(Localization::BASE_LOCALE, 0, 2);
                        $translatableListener->setTranslatableLocale($fallbackLanguage);
                    }
                }
                $this->evm->addEventSubscriber($translatableListener);
            }
        }
    }

    protected function registerLoggable()
    {
        if ($this->config->get('settings.loggable.active')) {
            $loggableListener = new LoggableListener;
            $loggableListener->setAnnotationReader($this->cachedAnnotationReader);
            if ($this->user) {
                // if not the user is not logged in, a empty user object is retured.
                $username = $this->user->getUserName() === NULL ? '' : $this->user->getUserName();
                $loggableListener->setUsername($username);
            }
            $this->evm->addEventSubscriber($loggableListener);
        }
    }

    protected function registerSortable()
    {
        if ($this->config->get('settings.sortable.active')) {
            $sortableListener = new SortableListener();
            $sortableListener->setAnnotationReader($this->cachedAnnotationReader);
            $this->evm->addEventSubscriber($sortableListener);
        }
    }
       
    protected function getSiteConfig(){
        $site = $this->app->make('site')->getActiveSiteForEditing();
        return $site->getConfigRepository();
    }

}
