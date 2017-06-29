<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

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
                    <input type="checkbox" name="eneable_sluggable" value="1" <?= $config->get('settings.sluggable.active') ? 'checked' : ''; ?> />
                    <?= t('Activate') ?>
                </label> 
            </div>
        </div>
        <div class="form-group">
            <label class="control-label">
                <?= t('Add a custom Transliterator class, which handles the conversion of special characters') ?>
            </label>
            <?php
            $translieratorValue = $config->get('settings.sluggable.transliterator') ?
                    $config->get('settings.sluggable.transliterator') :
                    '\Kaapiii\Doctrine\BehavioralExtensions\Translatable\Transliterator';
            ?>
            <input class="form-control" type="text" name="custom_sluggable" value="<?= $translieratorValue; ?>"  />
            
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
            <i class="fa fa-question-circle launch-tooltip" data-placement="right" title="<?= t('Stores the output of blocks which support block caching') ?>"></i>
        </legend>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="eneable_timestampable" value="1" <?= $config->get('settings.timestampable.active') ? 'checked' : ''; ?> />
                    <?= t('Activate') ?>
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
            <i class="fa fa-question-circle launch-tooltip" data-placement="right" title="<?= t('Stores the output of blocks which support block caching') ?>"></i>
        </legend>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="eneable_blameable" value="1" <?= $config->get('settings.blameable.active') ? 'checked' : ''; ?> />
                    <?= t('Activate') ?>
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
            <i class="fa fa-question-circle launch-tooltip" data-placement="right" title="<?= t('Stores the output of blocks which support block caching') ?>"></i>
        </legend>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="eneable_sortable" value="1" <?= $config->get('settings.sortable.active') ? 'checked' : ''; ?> />
                    <?= t('Activate') ?>
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
            <i class="fa fa-question-circle launch-tooltip" data-placement="right" title="<?= t('Stores the output of blocks which support block caching') ?>"></i>
        </legend>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="eneable_tree" value="1" <?= $config->get('settings.tree.active') ? 'checked' : ''; ?> />
                    <?= t('Activate') ?>
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
            <i class="fa fa-question-circle launch-tooltip" data-placement="right" title="<?= t('Stores the output of blocks which support block caching') ?>"></i>
        </legend>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="eneable_loggable" value="1" <?= $config->get('settings.loggable.active') ? 'checked' : ''; ?> />
                    <?= t('Activate') ?>
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
            <i class="fa fa-question-circle launch-tooltip" data-placement="right" title="<?= t('Add translatable support to entities') ?>"></i>
        </legend>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="eneable_translatable" value="1" <?= $config->get('settings.translatable.active') ? 'checked' : ''; ?> />
                    <?= t('Activate') ?>
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


    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <button class="pull-right btn btn-primary" type="submit" ><?= t('Save') ?></button>
        </div>
    </div>
</form>
