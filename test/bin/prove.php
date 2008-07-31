<?php

require_once dirname(__FILE__).'/../bootstrap/unit.php';
require_once $sf_symfony_lib_dir.'/vendor/lime/lime.php';

$h = new lime_harness(new lime_output_color);
$h->base_dir = dirname(__FILE__).'/../';
$h->register_glob($h->base_dir.'/unit/*/*Test.php');
$h->register_glob($h->base_dir.'/functional/*Test.php');
$h->run();
