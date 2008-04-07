<?php

require dirname(__FILE__).'/../../bootstrap/unit.php';
require dirname(__FILE__).'/../../../lib/helper/GWOHelper.php';

require $sf_symfony_lib_dir.'/config/sfConfig.class.php';
require $sf_symfony_lib_dir.'/exception/sfException.class.php';
require $sf_symfony_lib_dir.'/exception/sfViewException.class.php';

$t = new lime_test(3, new lime_output_color);

$t->diag('gwo_section()');
$t->is(gwo_section('foobar'), '<script>utmx_section("foobar")</script>', 'return value ok');
try
{
  gwo_section('123456789012345678901234567890');
  $t->fail('section name > 20 chars did not thrown exception');
}
catch (Exception $e)
{
  $t->pass('section name > 20 chars threw excpetion');
}

$t->diag('gwo_section_end()');
$t->is(gwo_section_end(), '<script>document.write(\'</nosc\'+\'ript>\')</script>', 'return value ok');
