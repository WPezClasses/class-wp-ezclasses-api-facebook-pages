<?php
/** 
 * The ezWay to make your own Facebook Page widget.
 *
 * The arc of the intention here is to bypass the usual js based Facebook Page widget (which can be a page load hog), 
 * in order to gain greater control over display / layout, as well as use a WP transient to further reduce overhead.
 *
 * PHP version 5.3
 *
 * LICENSE: TODO
 *
 * @package WPezClasses
 * @author Mark Simchock <mark.simchock@alchemyunited.com>
 * @since 0.5.1
 * @license TODO
 */
 
/**
 * == Change Log ==
 *
 * -- 0.5.1 - Thur 2 April 2015
 * --- FIXED: Had some properties as private that should have been less so (i.e., protected)
 *
 * -- 0.5.0 - Mon 16 March 2015
 * --- Pop the champagne!
 */
 
/**
 * == TODO == 
 *
 *
 */

// No WP? Die! Now!!
if (!defined('ABSPATH')) {
	header( 'HTTP/1.0 403 Forbidden' );
    die();
}

if ( ! class_exists('Class_WP_ezClasses_API_Facebook_Pages') ) {
	class Class_WP_ezClasses_API_Facebook_Pages extends Class_WP_ezClasses_Master_Singleton{
  
    private $_version;
	private $_url;
	private	$_path;
	private $_path_parent;
	private $_basename;
	private $_file;
	
	protected $_page_name;
	protected $_retries;
	protected $_pause;
  
    protected $_arr_init;
	  
	public function __construct() {
	  parent::__construct();
	}
		
	/**
	 *
	 */
	public function ez__construct($arr_args = ''){
	
	  $this->setup();
	  
	  $this->fb_pages_todo();

	  $arr_init_defaults = $this->init_defaults();
	  
	  $this->_arr_init = WPezHelpers::ez_array_merge(array($arr_init_defaults, $arr_args));
	
	}
	
	/**
	 * 
	 */
	protected function setup(){
	
	  $this->_version = '0.5.0';
	  $this->_url = plugin_dir_url( __FILE__ );
	  $this->_path = plugin_dir_path( __FILE__ );
	  $this->_path_parent = dirname($this->_path);
	  $this->_basename = plugin_basename( __FILE__ );
	  $this->_file = __FILE__ ;
	}
	
	/**
	 *
	 */
	protected function init_defaults(){
	
	  $arr_defaults = array(
	  
	  	'active'			 					=> true,
		'active_true'							=> true,	// use the active true "filtering"
		'filters'								=> false, 	// currently NA
		'arr_arg_validation'					=> false, 	// currently NA
		);
	
	  return $arr_defaults;
	}
	
	/**
	 *
	 */
	protected function fb_pages_todo(){
	
		$this->_page_name = 'facebook';  // TODO
		$this->_page_id = false;  		// if this is not false, we'll cut to the chase and use it instead of the name. the page_id can be found under the about \ page info
		$this->_retries = 3;
		$this->_pause = 500000; 		// 500ms - currently not in use

	}
	
	/**
	 * gets the page's widget and parses it
	 */
	public function page_widget(){
	
		$arr_return = array();
		
		$str_get_by = $this->_page_name;
		if ( $this->_page_id !== false ){
			$str_get_by = $this->_page_id;
		}
		
		// get page info from graph
		$arr_page_data = json_decode(file_get_contents('http://graph.facebook.com/' . $str_get_by), true);
		
		if ( empty($arr_page_data['id']) ){
		  // invalid fanpage name
          return array('error' => 'The FB Page name ' . $str_get_by . ' is not valid.');
		}
		// we're good! stash the page's properties
		$arr_return['page']['profile'] = $arr_page_data;
		
		$arr_page_img = json_decode(file_get_contents('http://graph.facebook.com/' . $arr_page_data['id'] . '/picture?redirect=false'), true);
		$page_img = '';
		if ( isset($arr_page_img['data']['url']) ){
			$str_page_img = $arr_page_img['data']['url'];
		}
		// stash the page's sqr image
		$arr_return['page']['src_sqr'] = $str_page_img;
		
		$url = 'http://www.facebook.com/plugins/fan.php?connections=100&id=' . $arr_page_data['id'];
		$context = stream_context_create(array('http' => array('header' => 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:22.0) Gecko/20100101 Firefox/22.0')));
		
		$str_widget_html = false;
		for($try = 0; $try < $this->_retries; $try++){
		
			$str_widget_html = file_get_contents($url, false, $context);
			
			if ( $str_widget_html !== false ){
				break;
			}
		}
		if ( $str_widget_html === false ){
			return array('error' => 'Unable to retrieve data from Facebook.');
		}
				
		
		// the followers list is a <li> list
		$regex_imgs =  '(<li([^>]+)>(.+?)</li>)';
		preg_match_all($regex_imgs, $str_widget_html, $arr_followers_lis);
		
		$arr_imgs = array();
		$arr_hrefs = array();
		$arr_followers = array();
		$arr_srcs = array();
		
		$regex_href = '( href=\"[^\"]*\")';
		$regex_img =  '(<img[^>]*>)';
		$regex_alt = '( alt=\"[^\"]*\")';
		$regex_src = '( src=\"[^\"]*\")';
		
		// this is where the follower parsing magic happens. 
		foreach ( $arr_followers_lis[0] as $key => $str_li ){
				
			preg_match_all($regex_img, $str_li, $arr_the_img);
			
			// we gotta have an image in order to do all the other parsing
			if ( isset($arr_the_img[0][0]) ){
			
				$str_img = $arr_the_img[0][0];
				
				// store the whole image tag
			   	$arr_imgs[$key] = $str_img;
				
				// back out to the wrapping li to get the followers' link to profile a href
				// Note: not all followers have link tos
				preg_match_all($regex_href, $str_li, $arr_the_href);	
				$str_href = '';
				if ( isset($arr_the_href[0][0]) ){
					$str_href = $arr_the_href[0][0];
					$str_href = str_replace('href="', '', $str_href  );
					$str_href = str_replace('"', '', $str_href  );
				}
				$arr_hrefs[$key] = $str_href;
			
				// alt= aka followers' name
				preg_match_all($regex_alt, $str_img, $arr_alt);
				$str_alt = '';
				if ( isset($arr_alt[0][0]) ){
					$str_alt = $arr_alt[0][0];
					$str_alt = str_replace('alt="', '', $str_alt  );
					$str_alt = str_replace('"', '', $str_alt  );
				}
				$arr_followers[$key] = $str_alt;
				
				// the img src
				preg_match_all($regex_src, $str_img, $arr_src);
				$str_src = '';
				if ( isset($arr_src[0][0]) ) {
					$str_src = $arr_src[0][0];
					$str_src = str_replace('src="', '', $str_src );
					$str_src = str_replace('"', '', $str_src  );
				}
				$arr_srcs[$key] = $str_src;   
			}   
		}

		$arr_return['followers']['imgs'] = $arr_imgs;
		$arr_return['followers']['srcs'] = $arr_srcs;
		$arr_return['followers']['names'] = $arr_followers;
		$arr_return['followers']['a_hrefs'] = $arr_hrefs;
		
		// usleep($this->_pause);	
		return $arr_return;
	 }
	}
}