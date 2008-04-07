<?php

require dirname(__FILE__).'/../../bootstrap/unit.php';
require dirname(__FILE__).'/../../../lib/experiment/sfGWOExperiment.class.php';

require $sf_symfony_lib_dir.'/util/sfParameterHolder.class.php';
require $sf_symfony_lib_dir.'/util/sfToolkit.class.php';
require $sf_symfony_lib_dir.'/config/sfConfig.class.php';
require $sf_symfony_lib_dir.'/controller/sfRouting.class.php';
require $sf_symfony_lib_dir.'/request/sfRequest.class.php';
require $sf_symfony_lib_dir.'/request/sfWebRequest.class.php';
require $sf_symfony_lib_dir.'/response/sfResponse.class.php';
require $sf_symfony_lib_dir.'/response/sfWebResponse.class.php';
require $sf_symfony_lib_dir.'/exception/sfException.class.php';
require $sf_symfony_lib_dir.'/exception/sfConfigurationException.class.php';

$t = new lime_test(17, new lime_output_color);

// extend the abstract class
class sfGWOExperimentTest extends sfGWOExperiment
{
  public function insertTest1PageContent($response)
  {
  }
  
  public function insertTest2PageContent($response)
  {
    $control = $this->getControlScript($this->key);
    $this->doInsert($response, $control, self::POSITION_TOP);
    
    $tracker = $this->getTrackerScript($this->key, $this->uacct, 'test');
    $this->doInsert($response, $tracker, self::POSITION_BOTTOM);
  }
  
}

// make believe
class sfContext
{
  public $request = null;
  
  public function getRequest()
  {
    return $this->request;
  }
}
$context = new sfContext;

$test_name  = 'experiment_name';
$test_page1 = array('module' => 'test1_module', 'action' => 'test1_action');
$test_page2 = array('module' => 'test2_module', 'action' => 'test2_action', 'my_param' => null);
$test_param = array(
  'key'   => '123123123',
  'uacct' => 'XX-XXXXX-X',
  // one indexed, one associative
  'pages' => array(
    'test1' => $test_page1, 
    'test2' => array($test_page2)), 
);
$experiment = new sfGWOExperimentTest($test_name, $test_param);

$t->diag('ctor/initialize()');
$t->isa_ok($experiment, 'sfGWOExperimentTest', 'instantiation ok');
$t->is($experiment->getName(), $test_name, 'name ok');
$t->is($experiment->getKey(), $test_param['key'], 'key ok');
$t->is($experiment->getUacct(), $test_param['uacct'], 'uacct ok');
$t->is_deeply($experiment->getParameterHolder()->getAll('pages'), $test_param['pages'], 'pages ok');

$t->diag('connect()');

$request = new sfWebRequest;
$request->initialize($context);
$context->request = $request;

// test associative array of page params in config
$request->setParameter('module', $test_page1['module'].'oops');
$request->setParameter('action', $test_page1['action']);
$t->ok(!$experiment->connect($request), 'assoc no connection ok');

$request->setParameter('module', $test_page1['module']);
$t->ok($experiment->connect($request), 'assoc connection ok');
$t->is_deeply(
  $experiment->getParameterHolder()->getAll('connection'), 
  array_merge(array('page' => 'test1'), $test_page1), 
  'assoc connection param stored ok');

// test indexed array of pages in config
$request->setParameter('module', $test_page2['module']);
$request->setParameter('action', $test_page2['action'].'oops');
$request->setParameter('my_param', 'oops again');
$t->ok(!$experiment->connect($request), 'indexed no connection ok');

$request->setParameter('action', $test_page2['action']);
$t->ok(!$experiment->connect($request), 'indexed no connection null test ok');

$request->getParameterHolder()->remove('my_param');
$t->ok($experiment->connect($request), 'indexed connection ok');
$t->is_deeply(
  $experiment->getParameterHolder()->getAll('connection'), 
  array_merge(array('page' => 'test2'), $test_page2), 
  'indexed connection param stored ok');

$t->diag('insertContent()');

$response = new sfWebResponse;
$response->initialize($context);
$response->setContent('<html><head></head><body class=""><p>sfLoremIpsum</p></body></html>');

$experiment->insertContent($response);
$content = $response->getContent();

$t->like($content, '#<body class="">\n<!-- control script -->#', 'insert top ok');
$t->like($content, '#<!-- tracker script end -->\n</body>#', 'insert bottom ok');
$t->like($content, '#'.$experiment->getKey().'#', 'key inserted ok');
$t->like($content, '#'.$experiment->getUacct().'#', 'uacct inserted ok');
$t->like($content, '#/'.$experiment->getKey().'/test#', 'tracker param ok');
