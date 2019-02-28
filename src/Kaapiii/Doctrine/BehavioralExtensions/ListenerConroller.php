<?php

namespace Kaapiii\Doctrine\BehavioralExtensions;

use Concrete\Core\Localization\Localization;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\User\User;
use Concrete\Core\Http\Request;
use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\Application;
use Concrete\Core\Config\Repository\Liaison;
use Concrete\Core\Localization\Locale\Service;
use Gedmo\DoctrineExtensions;
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

    const DEFAULT_TRANSLITERATOR = '\Kaapiii\Doctrine\BehavioralExtensions\Translatable\Transliterator';
    const DEFAULT_TRANSLITERATOR_METHOD = 'replaceSpecialSigns';

    /**
     * @var $app
     */
    protected $app;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

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
     *
     * @var \Concrete\Core\User\User
     */
    protected $user;

    /**
     * Constructor
     *
     * @param ApplicationAwareInterface $app
     * @param Liaison $config
     */
    public function __construct(Application $app, Liaison $config)
    {
        $this->setApplication($app);
        $this->config = $config;
        $this->user = new User();
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
     * Register Doctrine2 behavioral extensions
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function registerDoctrineBehavioralExtensions()
    {
        $this->em = $this->app->make('Doctrine\ORM\EntityManager');
        $this->evm = $this->em->getEventManager();
        $this->cachedAnnotationReader = $this->app->make('orm/cachedAnnotationReader');
        $driverChain = $this->em->getConfiguration()->getMetadataDriverImpl();

        DoctrineExtensions::registerMappingIntoDriverChainORM($driverChain, $this->cachedAnnotationReader);

        $this->registerSortable();
        $this->registerSluggable();
        $this->registerTree();
        $this->registerBlamable();
        $this->registerTimestampable();
        $this->registerLoggable();

        // Register the listener the first time. This prevents an Exception being thrown for custom registered routes
        // (like custom API routes to custom entities) that don't trigger the 'on_locale_load' event, but triggers
        // somewhere in the request life cycle a query which uses the translatable mapping annotations.
        //
        // Example: Route "api/v1/{language}/products/" fetches all products. This route will never
        // trigger the 'on_locale_load' event in concrete, but in order to get all product in a specific language
        // the translatable listener need to be loaded.
        $this->registerTranslatable();

        // Needs to be triggered after the locale was loaded a second time so the listener is configured with the correct
        // language
        $class = $this;
        \Events::addListener('on_locale_load', function() use ($class){
            $class->registerTranslatable();
        });
    }

    /**
     * Register sortable listener
     */
    protected function registerSortable()
    {
        // Sortable
        if ($this->config->get('settings.sortable.active')) {
            $sortableListener = new SortableListener();
            $sortableListener->setAnnotationReader($this->cachedAnnotationReader);
            $this->evm->addEventSubscriber($sortableListener);
        }
    }

    /**
     * Register sluggable listener
     */
    protected function registerSluggable()
    {
        // Sluggable
        if ($this->config->get('settings.sluggable.active')) {
            $sluggableListener = new SluggableListener();
            $sluggableListener->setAnnotationReader($this->cachedAnnotationReader);
            // Register custom Sluggifiers (Replace Special Characters)
            if (class_exists($this->config->get('settings.sluggable.transliterator')) && method_exists($this->config->get('settings.sluggable.transliterator'), $this->config->get('settings.sluggable.transliteratorMethod'))) {
                $callable = array($this->config->get('settings.sluggable.transliterator'),$this->config->get('settings.sluggable.transliteratorMethod'));
            } else {
                $callable = array(self::DEFAULT_TRANSLITERATOR, self::DEFAULT_TRANSLITERATOR_METHOD);
            }

            $sluggableListener->setTransliterator($callable);
            $this->evm->addEventSubscriber($sluggableListener);
        }
    }

    /**
     * Register tree listener
     */
    protected function registerTree()
    {
        // Tree
        if ($this->config->get('settings.tree.active')) {
            $treeListener = new TreeListener();
            $treeListener->setAnnotationReader($this->cachedAnnotationReader);
            $this->evm->addEventSubscriber($treeListener);
        }
    }

    /**
     * Register timestampable listener
     */
    protected function registerTimestampable()
    {
        // Timestampable
        if ($this->config->get('settings.timestampable.active')) {
            $timestampableListener = new TimestampableListener();
            $timestampableListener->setAnnotationReader($this->cachedAnnotationReader);
            $this->evm->addEventSubscriber($timestampableListener);
        }
    }

    /**
     * Register blameable listener
     */
    protected function registerBlamable()
    {
        // Blameable
        if ($this->config->get('settings.blameable.active') && is_object($this->user)) {
            $blameableListener = new BlameableListener();
            $blameableListener->setAnnotationReader($this->cachedAnnotationReader);
            if ($this->user) {
                $blameableListener->setUserValue($this->user->getUserID());
            }
            $this->evm->addEventSubscriber($blameableListener);
        }
    }

    /**
     * Get the site default locale
     *
     * @return mixed
     */
    protected function getDefaultLocale(){
        /* @var $localeService \Concrete\Core\Localization\Locale\Service */
        $localeService = $this->app->make(Service::class, ['entityManager' => $this->em]);
        $siteLocalEntity = $localeService->getDefaultLocale();
        $defaultLocale = $siteLocalEntity->getLanguage();

        return $defaultLocale;
    }

    /**
     * Register translatable listener
     */
    protected function registerTranslatable()
    {
        if ($this->config->get('settings.translatable.active')) {
            $defaultLocale = $this->getDefaultLocale();
            if (!empty($defaultLocale)) {
                // Translatable
                $translatableListener = new TranslatableListener();
                $translatableListener->setAnnotationReader($this->cachedAnnotationReader);
                $translatableListener->setDefaultLocale($defaultLocale);
                $translatableListener->setTranslationFallback(false);

                $ms = Section::getCurrentSection();
                if (is_object($ms)) {
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

    /**
     * Register loggable listener
     */
    protected function registerLoggable()
    {
        // Loggable
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

    /**
     * Return the site config repository
     *
     * @return \Illuminate\Config\Repository
     */
    protected function getSiteConfig()
    {
        $site = $this->app->make('site')->getActiveSiteForEditing();
        return $site->getConfigRepository();
    }
}
