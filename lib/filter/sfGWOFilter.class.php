<?php

/**
 * Insert experiments into responses when request parameters are matched.
 * 
 * @package     sfGoogleWebsiteOptimizerPlugin
 * @subpackage  filter
 * @author      Kris Wallsmith <kris [dot] wallsmith [at] gmail [dot] com>
 * @version     SVN: $Id$
 */
class sfGWOFilter extends sfFilter
{
  /**
   * Insert appropriate experiment code.
   * 
   * @throws  sfConfigurationException
   * 
   * @param   sfFilterChain $filterChain
   */
  public function execute($filterChain)
  {
    $request  = $this->context->getRequest();
    $response = $this->context->getResponse();
    
    $filterChain->execute();
    
    // connect to each active experiment
    $prefix = 'app_sf_gwo_plugin_';
    if (sfConfig::get($prefix.'enabled', false))
    {
      foreach (sfConfig::get($prefix.'experiments', array()) as $name => $param)
      {
        // merge default with configured parameters
        $param = array_merge(array(
          'enabled' => false, 
          'type'    => null, 
          'key'     => null, 
          'uacct'   => sfConfig::get($prefix.'uacct'), 
          'pages'   => array()), $param);
        if ($param['enabled'])
        {
          // determine experiment class
          $classes = sfConfig::get($prefix.'classes', array());
          $classes = array_merge(array(
            'ab'    => 'sfGWOExperimentAB', 
            'multi' => 'sfGWOExperimentMulti'), $classes);
          
          if (isset($classes[$param['type']]))
          {
            $class = $classes[$param['type']];
            $experiment = new $class($name, $param);
            if ($experiment->connect($request))
            {
              $experiment->insertContent($response);
            }
          }
          else
          {
            throw new sfConfigurationException(sprintf('The experiment type "%s" was not found.', $param['type']));
          }
        }
      }
    }
  }
  
}
