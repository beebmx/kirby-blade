<?php snippet('layout', slots: true) ?>
About: <?= $page->title() ?>
<?= snippet('greeting', ['name' => 'Blade'], return: true) ?>
<?php snippet('plain', ['label' => 'php-snippet']) ?>
<?php endsnippet() ?>
