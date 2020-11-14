<?php use Gedmo\SoftDeleteable\SoftDeleteableListener;
use Kaapiii\Doctrine\BehavioralExtensions\ListenerController;

defined('C5_EXECUTE') or die("Access Denied."); ?>

<?php if($canInstallPackages): ?>
<form method="post" class="ccm-dashboard-content-form" action="<?= $controller->action('updateSettings') ?>">
    <?= $this->controller->token->output('behavioral') ?>

    <fieldset>
        <legend>
            <?= t('Sluggable') ?> 
            <i class="fa fa-question-circle launch-tooltip" data-placement="right" title="<?= t('Stores the output of blocks which support block caching') ?>"></i>
        </legend>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="enable_sluggable" value="1" <?= $config->get('settings.sluggable.active') ? 'checked' : ''; ?> />
                    <?= t('Activate') ?>
                </label> 
            </div>
        </div>
        <div class="form-group">
            <label class="control-label">
                <?= t('Add a custom Transliterator class, which handles the conversion of special characters for slugs') ?>
            </label>
            <?php
            $translieratorValue = $config->get('settings.sluggable.transliterator') ?
                $config->get('settings.sluggable.transliterator') :
                ListenerController::DEFAULT_TRANSLITERATOR;
            $translieratorMethodValue = $config->get('settings.sluggable.transliteratorMethod') ?
                $config->get('settings.sluggable.transliteratorMethod') :
                ListenerController::DEFAULT_TRANSLITERATOR_METHOD;
            ?>
            <input class="form-control" placeholder="<?= t('Transliterator class (Full namespace)');?>" type="text" name="transliterator" value="<?= $translieratorValue; ?>"  /><br>

            <label class="control-label">
                <?= t('Specify the method of the class inserted above, which will handle the string conversion.') ?>
            </label>
            <input class="form-control" placeholder="<?= t('Transliterator method');?>" type="text" name="transliterator_method" value="<?= $translieratorMethodValue; ?>"  />

            <?php
            $classExists = class_exists($config->get('settings.sluggable.transliterator'));
            $methodExists = method_exists($config->get('settings.sluggable.transliterator'), $config->get('settings.sluggable.transliteratorMethod'));

            if(!$classExists || !$methodExists):
                ?>
                <br>
                <br>
                <div class="alert alert-danger" role="alert">
                    <?php
                    if($classExists): ?>
                    <?= t('Class "%s" does not exist.', $config->get('settings.sluggable.transliterator'));?>
                    <?php elseif(!$methodExists): ?>
                    <?= t('The method "%s" does not exist in the class "%s".', $config->get('settings.sluggable.transliteratorMethod'), $config->get('settings.sluggable.transliterator'));?>
                    <?php endif; ?>
                    <br>
                    <br>
                    <?= t('The Sluggable extension will fall back to the default Transliterator.'); ?>
                </div>
            <?php endif;?>

            <?php 
            if(array_key_exists('SluggableListener', $listenersPerBehavoir) && count($listenersPerBehavoir['SluggableListener']) > 1){
                $eventListener = $listenersPerBehavoir['SluggableListener'];
                require $ormEventsElementPath;
            }
            ?>
        </div>
    </fieldset>
    <fieldset>
        <legend>
            <?= t('Timestampable') ?>
        </legend>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="enable_timestampable" value="1" <?= $config->get('settings.timestampable.active') ? 'checked' : ''; ?> />
                    <?= t('Activate') ?> <i class="fa fa-question-circle launch-tooltip" data-placement="right" title="<?= t('Stores the output of blocks which support block caching') ?>"></i>
                </label>
            </div>
            <?php 
            if(array_key_exists('TimestampableListener', $listenersPerBehavoir) && count($listenersPerBehavoir['TimestampableListener']) > 1){
                $eventListener = $listenersPerBehavoir['TimestampableListener'];
                require $ormEventsElementPath;
            }
            ?>
        </div>
    </fieldset>
    <fieldset>
        <legend>
            <?= t('Blameable') ?>
        </legend>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="enable_blameable" value="1" <?= $config->get('settings.blameable.active') ? 'checked' : ''; ?> />
                    <?= t('Activate') ?> <i class="fa fa-question-circle launch-tooltip" data-placement="right" title="<?= t('Stores the output of blocks which support block caching') ?>"></i>
                </label>
            </div>
            <?php 
            if(array_key_exists('BlameableListener', $listenersPerBehavoir) && count($listenersPerBehavoir['BlameableListener']) > 1){
                $eventListener = $listenersPerBehavoir['BlameableListener'];
                require $ormEventsElementPath;
            }
            ?>
        </div>
    </fieldset>
    <fieldset>
        <legend>
            <?= t('Sortable') ?>
        </legend>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="enable_sortable" value="1" <?= $config->get('settings.sortable.active') ? 'checked' : ''; ?> />
                    <?= t('Activate') ?> <i class="fa fa-question-circle launch-tooltip" data-placement="right" title="<?= t('Stores the output of blocks which support block caching') ?>"></i>
                </label>
            </div>
            <?php 
            if(array_key_exists('SortableListener', $listenersPerBehavoir) && count($listenersPerBehavoir['SortableListener']) > 1){
                $eventListener = $listenersPerBehavoir['SortableListener'];
                require $ormEventsElementPath;
            }
            ?>
        </div>
    </fieldset>
    <fieldset>
        <legend>
            <?= t('Tree') ?>
        </legend>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="enable_tree" value="1" <?= $config->get('settings.tree.active') ? 'checked' : ''; ?> />
                    <?= t('Activate') ?> <i class="fa fa-question-circle launch-tooltip" data-placement="right" title="<?= t('Stores the output of blocks which support block caching') ?>"></i>
                </label>
            </div>
            <?php 
            if(array_key_exists('TreeListener', $listenersPerBehavoir) && count($listenersPerBehavoir['TreeListener']) > 1){
                $eventListener = $listenersPerBehavoir['TreeListener'];
                require $ormEventsElementPath;
            }
            ?>
        </div>
    </fieldset>
    <fieldset>
        <legend>
            <?= t('Loggable') ?>
        </legend>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="enable_loggable" value="1" <?= $config->get('settings.loggable.active') ? 'checked' : ''; ?> />
                    <?= t('Activate') ?> <i class="fa fa-question-circle launch-tooltip" data-placement="right" title="<?= t('Stores the output of blocks which support block caching') ?>"></i>
                </label>
            </div>
            <?php
            if(array_key_exists('LoggableListener', $listenersPerBehavoir) && count($listenersPerBehavoir['LoggableListener']) > 1){
                $eventListener = $listenersPerBehavoir['LoggableListener'];
                require $ormEventsElementPath;
            }
            ?>
        </div>
    </fieldset>
    <fieldset>
        <legend>
            <?= t('Translatable') ?> 

        </legend>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="enable_translatable" value="1" <?= $config->get('settings.translatable.active') ? 'checked' : ''; ?> />
                    <?= t('Activate') ?> <i class="fa fa-question-circle launch-tooltip" data-placement="right" title="<?= t('Add translatable support to entities') ?>"></i>
                </label>
            </div>
            <?php

            // Information
            if(!$defaultLanguageFalse):?>
                <div class="alert alert-danger" role="alert"><?= t('In order to use the Translatable behavior the default locale for the site has to be set. Please set the locale here:'); ?>   <?= t('Multilingual Settings'); ?></div>
            <?php endif;
            // Show the registered ORM events for this event listener
            if(array_key_exists('TranslatableListener', $listenersPerBehavoir) && count($listenersPerBehavoir['TranslatableListener']) > 1){
                $eventListener = $listenersPerBehavoir['TranslatableListener'];
                require $ormEventsElementPath;
            }
            ?>
        </div>
    </fieldset>
    <fieldset>
        <legend>
            <?= t('SoftDeletable') ?>
        </legend>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="enable_softDeletable" value="1" <?= $config->get('settings.softDeletable.active') ? 'checked' : ''; ?> />
                    <?= t('Activate') ?> <i class="fa fa-question-circle launch-tooltip" data-placement="right" title="<?= t('Add SoftDeletable support to entities') ?>"></i>
                </label>
            </div>
            <?php
            if(array_key_exists('SoftDeleteableListener', $listenersPerBehavoir) && count($listenersPerBehavoir['SoftDeleteableListener']) > 1){
                $eventListener = $listenersPerBehavoir['SoftDeleteableListener'];
                require $ormEventsElementPath;
            }
            ?>
        </div>
    </fieldset>


    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <button class="pull-right btn btn-primary" type="submit" ><?= t('Save') ?></button>
        </div>
    </div>
</form>
<?php endif; ?>
