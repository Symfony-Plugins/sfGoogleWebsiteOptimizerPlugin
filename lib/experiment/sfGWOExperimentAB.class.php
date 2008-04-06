<?php

/**
 * Logic specific to A/B experiments.
 * 
 * @package     sfGoogleWebsiteOptimizerPlugin
 * @subpackage  experiment
 * @author      Kris Wallsmith <kris [dot] wallsmith [at] gmail [dot] com>
 * @version     SVN: $Id$
 */
class sfGWOExperimentAB extends sfGWOExperiment
{
  /**
   * @see sfGWOExperiment
   */
  public function insertContent($response)
  {
    $page = $this->parameterHolder->get('page', null, 'connected');
    $method = 'insert'.ucwords($page).'PageContent';
    if (!method_exists($this, $method))
    {
      throw new sfException(sprintf('Unrecognized page matched, "%s"', $page));
    }
    
    $this->$method($response);
  }
  
  /**
   * Insert content for the original page.
   * 
   * @param   sfResponse $response
   */
  protected function insertOriginalPageContent($response)
  {
    $topContent = 
    
<<<HTML
<script>
function utmx_section(){}function utmx(){}
(function(){var k='%s',d=document,l=d.location,c=d.cookie;function f(n){
if(c){var i=c.indexOf(n+'=');if(i>-1){var j=c.indexOf(';',i);return c.substring(i+n.
length+1,j<0?c.length:j)}}}var x=f('__utmx'),xx=f('__utmxx'),h=l.hash;
d.write('<sc'+'ript src="'+
'http'+(l.protocol=='https:'?'s://ssl':'://www')+'.google-analytics.com'
+'/siteopt.js?v=1&utmxkey='+k+'&utmx='+(x?x:'')+'&utmxx='+(xx?xx:'')+'&utmxtime='
+new Date().valueOf()+(h?'&utmxhash='+escape(h.substr(1)):'')+
'" type="text/javascript" charset="utf-8"></sc'+'ript>')})();
</script><script>utmx("url",'A/B');</script>
HTML;
    
    $topContent = sprintf($topContent, $this->key);
    $this->doInsert($response, $topContent, self::POSITION_TOP);
    
    $bottomContent = 

<<<HTML
<script>
if(typeof(urchinTracker)!='function')document.write('<sc'+'ript src="'+
'http'+(document.location.protocol=='https:'?'s://ssl':'://www')+
'.google-analytics.com/urchin.js'+'"></sc'+'ript>')
</script>
<script>
_uacct = '%s';
urchinTracker("/%s/test");
</script>
HTML;
    
    $bottomContent = sprintf($bottomContent, $this->uacct, $this->key);
    $this->doInsert($response, $bottomContent, self::POSITION_BOTTOM);
  }
  
  /**
   * Insert content for a variation page.
   * 
   * @param   sfResponse $response
   */
  protected function getVariationPageContent($response)
  {
    $content = 
    
<<<HTML
<script>
if(typeof(urchinTracker)!='function')document.write('<sc'+'ript src="'+
'http'+(document.location.protocol=='https:'?'s://ssl':'://www')+
'.google-analytics.com/urchin.js'+'"></sc'+'ript>')
</script>
<script>
_uacct = '%s';
urchinTracker("/%s/test");
</script>
HTML;
    
    $content = sprintf($content, $this->uacct, $this->key);
    $this->doInsert($response, $content, self::POSITION_BOTTOM);
  }
  
  /**
   * Insert content for the conversion page.
   * 
   * @param   sfResponse $response
   */
  protected function getConversionPageContent($response, $key)
  {
    $content = 
    
<<<HTML
<script>
if(typeof(urchinTracker)!='function')document.write('<sc'+'ript src="'+
'http'+(document.location.protocol=='https:'?'s://ssl':'://www')+
'.google-analytics.com/urchin.js'+'"></sc'+'ript>')
</script>
<script>
_uacct = '%s';
urchinTracker("/%s/goal");
</script>
HTML;
    
    $content = sprintf($content, $this->uacct, $this->key);
    $this->doInsert($response, $content, self::POSITION_BOTTOM);
  }
  
}