<?php
namespace NyroDev\UtilityBundle\Services;

class MainService extends AbstractService {
	
	
    /**
     * Generates a URL from the given parameters, allowing extra parameters (like comma)
     *
     * @param  string  $name       The name of the route
     * @param  mixed   $parameters An array of parameters
     * @param  Boolean $absolute   Whether to generate an absolute URL
     * @return string The generated URL
     */
	public function generateUrl($name, $parameters = array(), $absolute = false) {
		if ($name == '#')
			return '#';
		return str_replace('%2C', ',', $this->get('router')->generate($name, $parameters, $absolute));
	}
	
	public function getFullUrl($path) {
		$request = $this->get('request');
		if ($path[0] != '/')
			$path = '/'.$path;
		return $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath().$path;
	}
	
	/**
	 * Set a templating slot
	 *
	 * @param string $name Slot name
	 * @param mixed $value Slot value
	 */
	public function setSlot($name, $value) {
		$this->get('templating.engine.php')->get('slots')->set($name, $value);
	}
	
	/**
	 * Get parametred analytics UA
	 * 
	 * @return string
	 */
	public function getAnalyticsUA() {
		return $this->getParameter('analyticsUA');
	}
	
	/**
	 * Get tracking code for analytics if parametered
	 *
	 * @return string
	 */
	public function getTrackingAnalytics() {
		$ret = null;
		$ua = $this->getAnalyticsUA();
		if ($ua) {
			$ret = "<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
  ga('create', '".$ua."', 'routedescommunes.com');
  ga('send', 'pageview');
</script>";
		}
		return $ret;
	}
	
	/**
	 * Urlify a string
	 * 
	 * @param string $text
	 * @return string
	 */
	public function urlify($text) {
		$text = str_replace(
			array('ß' , 'æ',  'Æ',  'Œ', 'œ', '¼',   '½',   '¾',   '‰',   '™', '&'),
			array('ss', 'ae', 'AE', 'OE', 'oe', '1/4', '1/2', '3/4', '0/00', 'TM', '_'),
			$text);
		$from = "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøðÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüŠšÝŸÿÑñÐÞþ()[]~¤$&%*@ç§¶!¡†‡?¿;,.#:/\\^¨€¢£¥{}|¦+÷×±<>«»“”„\"‘’' ˜–—…©®¹²³°";
		$to   = 'AAAAAAaaaaaaOOOOOOoooooooEEEEeeeeCcIIIIiiiiUUUUuuuuSsYYyNnDPp           cS        ---     EcPY        __________------CR123-';
		return strtolower(trim(str_replace(
			array(' ', '-----', '----', '---', '--'),
			'-',
			strtr(utf8_decode($text), utf8_decode($from), utf8_decode($to))), '-'));
	}
	
	/**
	 * Get a random string
	 *
	 * @param int $len String length
	 * @param string $ignore Characters to exclude
	 * @return string
	 */
	public function randomStr($len = 20, $ignore = null) {
		$source = 'abcdefghikjlmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		if (!is_null($ignore)) {
			$tmp = array();
			for($i=0;$i<strlen($ignore);$i++)
				$tmp[] = $ignore[$i];
			$source = str_replace($tmp, '', $source);
		}
		$$len = abs(intval($len));
		$n = strlen($source)-1;
		$ret = '';
		for($i = 0; $i < $$len; $i++)
			$ret.= $source{rand(0, $n)};
		return $ret;
	}
	
	public function isPost() {
		return $this->get('request')->isMethod('POST');
	}
	
	public function getNewUniqRandomKey(\Doctrine\ORM\EntityRepository $repository, $field, $length) {
		$entity = true;
		while ($entity) {
			$random = $this->randomStr($length);
			$entity = $repository->findOneBy(array($field=>$random));
		}
		return $random;
	}
	
	/**
	 * Returns true if the request is Ajax.
	 * It works if your JavaScript library set an X-Requested-With HTTP header.
	 * It is known to work with Prototype, Mootools, jQuery.
	 *
	 * @return boolean
	 */
	public function isAjax() {
		return $this->get('request')->isXmlHttpRequest();
	}
	
	protected $isExternalAgent;
	
	/**
	 * Return true if the request comes from an external agent (like facebook external hit)
	 *
	 * @return boolean
	 */
	public function isExternalAgent() {
		if (is_null($this->isExternalAgent)) {
			$this->isExternalAgent = false;
			if (isset($_SERVER['HTTP_USER_AGENT'])) {
				$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
				if (strpos($ua, 'facebookexternalhit') !== false || preg_match('~(bot|crawl|external|snippet)~i', $ua))
					$this->isExternalAgent = true;
			}
		}
		return $this->isExternalAgent;
	}
	
	/**
	 * Indicates if the PHP limits have been increased
	 *
	 * @var boolean
	 */
	protected $increasedPhpLimits = false;
	
	/**
	 * Increase PHP Limits
	 */
	public function increasePhpLimits() {
		if (!$this->increasedPhpLimits) {
			$this->increasedPhpLimits = true;
			@set_time_limit(0);
			@ini_set('memory_limit', '-1');
		}
	}

	public function getPager($route, $routePrm, $total, $page, $nbPerPage) {
		return new \NyroDev\UtilityBundle\Utility\Pager($this, $route, $routePrm, $total, $page, $nbPerPage);
	}
	
	/**
	 * Get the file extension
	 *
	 * @param string $file The filename
	 * @return null|string The extension
	 */
	public function getExt($file) {
		return pathinfo($file, PATHINFO_EXTENSION);
	}
	
	public function getUniqFileName($dir, $name) {
		$name = mb_strtolower($name);
		$ext = $this->getExt($name);
		$pos = strpos($name, $ext);
		if ($pos > 0)
			$name = substr($name, 0, $pos);
		$name = $this->urlify($name);

		$nameF = $name.'.'.$ext;
		$i = 2;
		while(file_exists($dir.'/'.$nameF)) {
			$nameF = $name.'-'.$i.'.'.$ext;
			$i++;
		}
		return $nameF;
	}
	
	/**
	 * Get a human file size
	 *
	 * @param string $file The file path
	 * @return string The human size
	 */
	public function humanFileSize($file) {
		$size = filesize($file);
		$mod = 1024;
		$units = explode(' ', 'B KB MB GB TB PB');
		for ($i = 0; $size > $mod; $i++) {
			$size /= $mod;
		}
		return round($size, 2).' '.$units[$i];
	}
	
	protected $html2textLoaded = false;
	
	/**
	 * Transform HTML content into text
	 *
	 * @param string $html HTML text
	 * @return string Text
	 */
	public function html2text($html) {
		if (!$this->html2textLoaded) {
			require(dirname(__FILE__).'/../Utility/Html2Text.php');
			$this->html2textLoaded = true;
		}
		$html2text = new \Html2Text\Html2Text($html);
		return $html2text->get_text();
	}

	
	public function joinRows($rows, $separator = ', ') {
		$ret = array();
		foreach($rows as $r)
			$ret[] = $r.'';
		return implode($separator, $ret);
	}

}

