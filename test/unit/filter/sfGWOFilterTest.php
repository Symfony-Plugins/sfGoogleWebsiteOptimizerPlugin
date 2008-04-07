<?php

require dirname(__FILE__).'/../../bootstrap/unit.php';

require $sf_symfony_lib_dir.'/filter/sfFilter.class.php';
require dirname(__FILE__).'/../../../lib/filter/sfGWOFilter.class.php';

$t = new lime_test(1, new lime_output_color);
$t->ok(class_exists('sfGWOFilter'), 'class exists');
