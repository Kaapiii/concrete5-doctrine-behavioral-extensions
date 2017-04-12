<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<br/>
<p><?= t('Registered for'); ?>: 
    <?php
     if(count($eventListener)):
         foreach($eventListener as $event): ?>
            <span class="label label-primary"><?= $event; ?></span>
         <?php endforeach;
     endif; ?>
</p>
