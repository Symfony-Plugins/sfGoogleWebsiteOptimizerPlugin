<?php

class sfGoogleWebsiteOptimizerFilter extends sfFilter
{
  public function execute($filterChain)
  {
    $request  = $this->context->getRequest();
    $response = $this->context->getResponse();
    
    $filterChain->execute();
    
    // connect to each active experiment
    $prefix = 'app_sf_google_website_optimizer_plugin_';
    if (sfConfig::get($prefix.'enabled', false))
    {
      foreach (sfConfig::get($prefix.'experiments', array()) as $name => $param)
      {
        $param = array_merge(array('enabled' => false, 'type' => null, 'key' => null, 'uacct' => null), $param);
        if ($param['enabled'])
        {
          switch ($param['type'])
          {
            case 'ab':
            $class = sfConfig::get($prefix.'ab_experiment_class', 'sfGoogleWebsiteOptimizerABExperiment');
            break;
            case 'multi':
            $class = sfConfig::get($prefix.'multi_experiment_class', 'sfGoogleWebsiteOptimizerMultiExperiment');
            break;
            default:
            throw new sfConfigurationException(sprintf('The experiment "%s" must have a type of either "ab" or "multi", not "%s"', $name, $param['type']));
          }
          
          unset($param['enabled'], $param['type']);
          
          $experiment = new $class($name, $param);
          if ($experiment->connect($request))
          {
            $experiment->insertContent($response);
          }
        }
      }
    }
  }
  
}
