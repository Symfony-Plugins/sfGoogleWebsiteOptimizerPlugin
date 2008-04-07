<?php

require dirname(__FILE__).'/../../bootstrap/unit.php';
require dirname(__FILE__).'/../../../lib/experiment/sfGWOExperiment.class.php';
require dirname(__FILE__).'/../../../lib/experiment/sfGWOExperimentAB.class.php';

require $sf_symfony_lib_dir.'/util/sfParameterHolder.class.php';
require $sf_symfony_lib_dir.'/util/sfToolkit.class.php';
require $sf_symfony_lib_dir.'/config/sfConfig.class.php';
require $sf_symfony_lib_dir.'/controller/sfRouting.class.php';
require $sf_symfony_lib_dir.'/request/sfRequest.class.php';
require $sf_symfony_lib_dir.'/request/sfWebRequest.class.php';
require $sf_symfony_lib_dir.'/response/sfResponse.class.php';
require $sf_symfony_lib_dir.'/response/sfWebResponse.class.php';

$t = new lime_test(15, new lime_output_color);

// make believe
class sfContext
{
  public $request  = null;
  
  public function getRequest()
  {
    return $this->request;
  }
}
$context = new sfContext;
$request = new sfWebRequest;
$request->initialize($context);
$context->request = $request;
$response = new sfWebResponse;
$response->initialize($context);
$response->setContent('<html><head></head><body class=""><p>sfLoremIpsum</p></body></html>');

$test_name  = 'experiment_name';
$original   = array('module' => 'original_module', 'action' => 'original_action');
$variation  = array('module' => 'variation_module', 'action' => 'variation_action');
$conversion = array('module' => 'conversion_module', 'action' => 'conversion_action');
$test_param = array(
  'key'   => '123123123',
  'uacct' => 'XX-XXXXX-X',
  'pages' => array(
    'original'    => $original,
    'variation'   => array($variation),
    'conversion'  => $conversion,
  ),
);
$experiment = new sfGWOExperimentAB($test_name, $test_param);
$t->isa_ok($experiment, 'sfGWOExperimentAB', 'instantiation ok');

$t->diag('original page');
$request->setParameter('module', $original['module']);
$request->setParameter('action', $original['action']);
$t->ok($experiment->connect($request), 'connection ok');
$t->is($experiment->getParameterHolder()->get('page', null, 'connected'), 'original', 'connected to original');

$origResponse = clone $response;
$experiment->insertContent($origResponse);
$content = $origResponse->getContent();
$t->like($content, '#<!-- control script -->#', 'control script inserted');
$t->like($content, '#<script>utmx\("url", \'A/B\'\)</script>#', 'ab function inserted');
$t->like($content, '#<!-- tracker script -->#', 'tracker script inserted');
$t->like($content, '#/test#', 'track as test');

$t->diag('variation page');
$request->setParameter('module', $variation['module']);
$request->setParameter('action', $variation['action']);
$t->ok($experiment->connect($request), 'connection ok');
$t->is($experiment->getParameterHolder()->get('page', null, 'connected'), 'variation', 'connected to variation');

$varResponse = clone $response;
$experiment->insertContent($varResponse);
$content = $varResponse->getContent();
$t->like($content, '#<!-- tracker script -->#', 'tracker script inserted');
$t->like($content, '#/test#', 'track as test');

$t->diag('conversion page');
$request->setParameter('module', $conversion['module']);
$request->setParameter('action', $conversion['action']);
$t->ok($experiment->connect($request), 'connection ok');
$t->is($experiment->getParameterHolder()->get('page', null, 'connected'), 'conversion', 'connected to conversion');

$convResponse = clone $response;
$experiment->insertContent($convResponse);
$content = $convResponse->getContent();
$t->like($content, '#<!-- tracker script -->#', 'tracker script inserted');
$t->like($content, '#/goal#', 'track as goal');
