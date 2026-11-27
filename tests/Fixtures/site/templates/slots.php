<?php snippet('slotted', ['title' => 'Default slot'], slots: true) ?>
<p>From PHP</p>
<?php endsnippet() ?>
<?php snippet('slotted', ['title' => 'Named slots'], slots: true) ?>
<?php slot() ?><p>Named default</p><?php endslot() ?>
<?php slot('footer') ?>Footer<?php endslot() ?>
<?php endsnippet() ?>
