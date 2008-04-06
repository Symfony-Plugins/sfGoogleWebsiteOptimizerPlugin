<?php

/**
 * Insertion methods for A/B experiments.
 * 
 * @package     sfGoogleWebsiteOptimizerPlugin
 * @subpackage  experiment
 * @author      Kris Wallsmith <kris [dot] wallsmith [at] gmail [dot] com>
 * @version     SVN: $Id$
 */
class sfGWOExperimentAB extends sfGWOExperiment
{
  /**
   * Insert content for the original page.
   * 
   * @param   sfResponse $response
   */
  protected function insertOriginalPageContent($response)
  {
    $control  = $this->getControlScript($this->key);
    $control .= '<script>utmx("url", \'A/B\')</script>';
    $this->doInsert($response, $control, self::POSITION_TOP);
    
    $tracker = $this->getTrackerScript($this->uacct, $this->key, 'test');
    $this->doInsert($response, $tracker, self::POSITION_BOTTOM);
  }
  
  /**
   * Insert content for a variation page.
   * 
   * @param   sfResponse $response
   */
  protected function insertVariationPageContent($response)
  {
    $tracker = $this->getTrackerScript($this->uacct, $this->key, 'test');
    $this->doInsert($response, $tracker, self::POSITION_BOTTOM);
  }
  
  /**
   * Insert content for the conversion page.
   * 
   * @param   sfResponse $response
   */
  protected function insertConversionPageContent($response)
  {
    $tracker = $this->getTrackerScript($this->uacct, $this->key, 'goal');
    $this->doInsert($response, $tracker, self::POSITION_BOTTOM);
  }
  
}
