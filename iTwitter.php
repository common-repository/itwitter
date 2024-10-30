<?php
/*
Plugin Name: iTwitter
Version: 0.04 (30-07-2009)
Plugin URI: http://itex.name/itwitter
Description: Twitter plugin for Wordpress. A useful plugin for those who uses Twitter. Twitter announse for Wordpress and other.
Author: Itex
Author URI: http://itex.name/
*/

/*
Copyright 2007-2009  Itex (web : http://itex.name/)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


class itex_twitter
{
	var $version = '0.04';
	var $error = '';

	var $sidebar = array();
	var $footer = '';
	var $beforecontent = '';
	var $aftercontent = '';

	var $debuglog = '';
	var $memory_get_usage = 0; //start memory_get_usage
	var $get_num_queries = 0; //start get_num_queries
	//var $replacecontent = 0;
	var $cache = array();
	/**
   	* constructor, function __construct()  in php4 not working
   	*
   	*/
	function itex_twitter()
	{
		if (substr(phpversion(),0,1) == 4) $this->php4(); //fix php4 bugs
		add_action('widgets_init', array(&$this, 'itex_t_init'));
		//add_action("widgets_init", array(&$this, 'itex_t_widget_init'));
		add_action('admin_menu', array(&$this, 'itex_t_menu'));
		add_action('wp_footer', array(&$this, 'itex_t_footer'));
		//$this->document_root = ($_SERVER['DOCUMENT_ROOT'] != str_replace($_SERVER["SCRIPT_NAME"],'',$_SERVER["SCRIPT_FILENAME"]))?(str_replace($_SERVER["SCRIPT_NAME"],'',$_SERVER["SCRIPT_FILENAME"])):($_SERVER['DOCUMENT_ROOT']);
		if (get_option('itex_t_post2twitter_enable')) add_action('publish_post', array(&$this, 'itex_t_post2twitter'), 99);
		if (get_option('itex_t_replace_links_enable')) 
		{
			add_filter('the_content', array(&$this, 'itex_t_replace_links'));
			add_filter('the_excerpt', array(&$this, 'itex_t_replace_links'));
			add_filter('comment_text', array(&$this, 'itex_t_replace_links'));
			
		}
		if (!get_option('itex_t_install_date'))
		{
			update_option('itex_t_install_date',time());
		}
		//die('123213123');
		
	}

	/**
   	* php4 support
   	*
   	*/
	function php4()
	{
		if (!function_exists('file_put_contents'))
		{
			function file_put_contents($filename, $data)
			{
				$f = @fopen($filename, 'w');
				if (!$f) return false;
				else
				{
					$bytes = fwrite($f, $data);
					fclose($f);
					return $bytes;
				}
			}
		}
		$this->itex_debug('Used php4');
	}

	/**
   	*  Russian lang support
   	*
   	*/
	function lang_ru()
	{
		global $l10n;
		$locale = get_locale();
		if ($locale == 'ru_RU')
		{
			$domain = 'iTwitter';
			if (isset($l10n[$domain])) return;
			///ru_RU lang .mo file
			$input = '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';
			$input = gzuncompress(base64_decode($input));
			include_once(ABSPATH . WPINC . '/streams.php');
			include_once(ABSPATH . WPINC . '/gettext.php');
			$inputReader = new StringReader($input);
			$l10n[$domain] = new gettext_reader($inputReader);
			$this->itex_debug('Used Ru language');
		}
	}

	/**
   	* Debug collector
   	*
   	*/
	function itex_debug($text='')
	{
		$this->debuglog .= "\r\n".$text."\r\n";
	}

	/**
   	* plugin init function 
   	*
   	* @return  bool	
   	*/
	function itex_t_init()
	{
		if ( function_exists('memory_get_usage') ) $this->memory_get_usage = memory_get_usage();
		if ( function_exists('get_num_queries') ) $this->get_num_queries = get_num_queries();
		$this->itex_debug('REQUEST_URI = '.$_SERVER['REQUEST_URI']);
		
		$this->itex_t_widget_init();
		
		$this->itex_t_init_last_tweets();
		
		if (strlen($this->footer)) add_action('wp_footer', array(&$this, 'itex_t_footer'));
		
		if ((strlen($this->beforecontent)) || (strlen($this->aftercontent)) )
		{
			$this->itex_debug('strlenbeforecontent = '.strlen($this->beforecontent));
			$this->itex_debug('strlenaftercontent = '.strlen($this->aftercontent));
			add_filter('the_content', array(&$this, 'itex_t_replace'));
			add_filter('the_excerpt', array(&$this, 'itex_t_replace'));
		}
		
		if ( function_exists('memory_get_usage') ) $this->itex_debug("memory start/end/dif ".$this->memory_get_usage.'/'.memory_get_usage().'/'.(memory_get_usage()-$this->memory_get_usage));
		if ( function_exists('get_num_queries') ) $this->itex_debug("get_num_queries start/end/dif ".intval($this->get_num_queries).'/'.intval(get_num_queries()).'/'.(intval(get_num_queries())-intval($this->get_num_queries)));
		return 1;
	}

	/**
   	* Last tweets init
   	*
   	* @return  bool
   	*/
	function itex_t_init_last_tweets()
	{
		if (!get_option('itex_t_last_tweets_enable')) return 0;

		$users = get_option('itex_t_last_tweets_users');
		if (empty($users)) return;
		$users = explode(',',$users);
		include_once(ABSPATH . WPINC . '/rss.php');
		$script = '';
		foreach ($users as $v)
		{
			$v = trim($v);
//			if (!function_exists('_response_to_rss'))
//			{
//				include_once(ABSPATH . WPINC . '/rss.php');
//			}
			//$messages = fetch_rss('http://twitter.com/statuses/user_timeline/'.$v.'.rss');
			//print_r($messages);die();
			$messages = $this->GetSourceFromUrl('http://twitter.com/statuses/user_timeline/'.$v.'.rss');
			//print_r($messages);die();
			
			$rss = new MagpieRSS($messages);
			// if RSS parsed successfully
			if ( $rss and !$rss->ERROR) 
			{
				if (isset($rss->items[0])) 
				{
					$k = $rss->items[0]['summary']; 
					//$script .= '<li><a href="'.$rss->items[0]['link'].'" rel="nofollow" target="_blank" title="'.$k.'">'.$k.'</a></li>';
					//$date = strtotime($rss->items[0]['pubdate']);
					$d = intval(strtotime($rss->items[0]['pubdate']));
					$ret[$d][] = '<li><a href="'.$rss->items[0]['link'].'" rel="nofollow" target="_blank" title="'.$k.'">'.$k.'</a></li>';
					$d1[$d] = $d;
					//$script .= '<li>'.$k.'</li>';
				}
				
				//print_r($rss);die();
			}
			else $this->error .= __('Error fetching last tweet for user ', 'iMoney').$v.' '." (" . $rss->ERROR . ")";
			
			//$messages = _response_to_rss($messages);
			//print_r($messages);die();
		}
		if (count($d1))
		{	
			ksort($d1);
		
			//сортировка по дате
			//print_r($users);die();
			foreach ($d1 as $v)
			{
				foreach ($ret[$v] as $b)
				{
					$script = $b.$script;
				
				}
			}
		}
		//print_r($script);die();
		
		
		$script = '<ul>'.$script.'</ul>';
		//print_r($script);die();
		///die('213123123');
		$pos = get_option('itex_t_last_tweets_pos');
		switch ($pos)
		{
			case 'sidebar':
			{
				$this->sidebar['last_tweets'] = '<div style="clear:right;">'.$script.'</div>';
				break;
			}
			case 'footer':
			{
				$this->footer .= '<p style="float:left;">'.$script.'</p>';
				break;
			}
			case 'beforecontent':
			{
				$this->beforecontent .= '<p style="float:left;">'.$script.'</p>';
				break;
			}
			case 'aftercontent':
			{
				$this->aftercontent .= '<p style="float:left;">'.$script.'</p>';
				break;
			}
			default: {}
		}

		return 1;
	}

	/**
   	* Footer output
   	*
   	*/
	function itex_t_footer()
	{
		echo $this->footer;
//		if (get_option('itex_t_php_enable') && get_option('itex_t_php_footer_enable'))
//		{
//			$code = get_option('itex_t_php_footer');
//			if (strlen($code)>1) eval($code);
//		}

		if (get_option('itex_t_global_debugenable'))
		{
			//echo 'is_user_logged_in'.intval(is_user_logged_in()).'_'.intval(get_option('itex_t_global_debugenable_forall'));//die();
			//echo 'reqweqweqweqweqwe';//die();
			if ((intval(is_user_logged_in())) || intval(get_option('itex_t_global_debugenable_forall')))
			{
				echo '<!--- iTwitterDebugLogStart'.$this->debuglog.' iTwitterDebugLogEnd --->';
				echo '<!--- iTwitterDebugErrorsStart'.$this->error.' iTwitterDebugErrorsEnd --->';
			}
		}
	}

	/**
   	* Content links and before-after content links
   	*
   	* @param   string   $content   input text
   	* @return  string	$content   outpu text
   	*/
	function itex_t_replace($content)
	{
		

		if ((strlen($this->beforecontent)) || (strlen($this->aftercontent)))
		{
			if (get_option('itex_t_global_debugenable'))
			{

				$content = '<!---check_beforecontent-->'.$this->beforecontent.$content.'<!---check_aftercontent-->'.$this->aftercontent;
			}
			else $content = $this->beforecontent.$content.$this->aftercontent;
			$this->beforecontent=$this->aftercontent='';
			$this->itex_debug('twitter in content worked');
		}
		else $this->itex_debug('beforecontent and aftercontent is empty');
		
		return $content;
	}

	/**
   	* 
   	*
   	* @param   string   $domnod   $text
   	* @return  string	$text
   	*/
	function itex_t_widget_init()
	{
		if (function_exists('register_sidebar_widget')) register_sidebar_widget('itwitter', array(&$this, 'itex_t_widget'));
		if (function_exists('register_widget_control')) register_widget_control('itwitter', array(&$this, 'itex_t_widget_control'), 300, 200 );
	}

	
	/**
   	* widget
   	*
   	* @param   array   $args   arguments for widget
    */
	function itex_t_widget($args)
	{
		extract($args, EXTR_SKIP);
		$title = get_option("itex_t_widget_title");
		//$title = empty($title) ? urlencode('<a href="http://itex.name" title="iTwitter">iTwitter</a>') :$title;
		$itex = array('<a href="http://itex.name/itwitter" title="iTwitter">iTwitter</a>','<a href="http://itex.name/" title="itex">itex</a>');
		if (empty($title))
		{
			$title = $itex[rand(0,count($itex)-1)];
			update_option("itex_t_widget_title", $title);
		}

		if (count($this->sidebar))
		{
			foreach ($this->sidebar as $k => $v)
			{
				echo $before_widget.$before_title .  $title . $after_title.
				'<ul><li>'.$v.'</li></ul>'.$after_widget;
				$this->itex_debug('widget init '.$k);
			}
		}
	}

	/**
   	*  Links widget control
   	*
   	* @param   string   $domnod   $text
   	*/
	function itex_t_widget_control()
	{
		$title = get_option("itex_t_widget_title");
		$itex = array('<a href="http://itex.name/itwitter" title="iTwitter">iTwitter</a>','<a href="http://itex.name/" title="itex">itex</a>');
		$title = empty($title) ? $itex[rand(0,count($itex)-1)] :$title;
		if ($_POST['itex_t_widget_Submit'])
		{
			//$title = htmlspecialchars($_POST['itex_t_widget_title']);
			$title = stripslashes($_POST['itex_t_widget_title']);
			update_option("itex_t_widget_title", $title);
		}
		echo '
  			<p>
    			<label for="itex_t_widget">'.__('Widget Title: ', 'iTwitter').'</label>
    			<textarea name="itex_t_widget_title" id="itex_t_widget" rows="1" cols="20">'.$title.'</textarea>
    			<input type="hidden" id="" name="itex_t_widget_Submit" value="1" />
  			</p>';
		//print_r($this->debuglog);//die();
	}
	
	/**
   	*  post2twitter
   	*
   	* 
   	*/
	function itex_t_post2twitter($post_id) 
	{
		//'publish_post' action
		//if (!get_option('itex_t_post2twitter_enable')) return ;
		if (!get_option('itex_t_post2twitter_enable') || $post_id == 0 || get_post_meta($post_id, 'itex_t_post2twitter_ready', true) == '1') return;
		$post = get_post($post_id);
		if ($post->post_status != 'publish') return;
		$url = get_permalink($post_id);
		$url = $this->itex_t_shorturls($url);
		$title = $post->post_title;
		$excerpt = $post->post_excerpt;
		$template = base64_decode(get_option('itex_t_post2twitter_template'));
		//$template = '%title% %excerpt% %url%';
		
		$tweet = str_replace(array('%title%','%excerpt%','%url%'), array($title,$excerpt,$url), $template);
		//print_r($tweet);
		//echo strlen($tweet);
		$maxlenght = 140;
		$c = 0;
		while (strlen($tweet)>$maxlenght) 
		{
			$need = strlen($tweet)-$maxlenght;
			if (!empty($excerpt))
			{
				$excerpt = substr($excerpt,0,strlen($excerpt-$need));
			}
			else 
			{
				$title = substr($title,0,strlen($title-$need));
			}
			$tweet = str_replace(array('%title%','%excerpt%','%url%'), array($title,$excerpt,$url), $template);
			$c++;
			if ($c>20) break;
		}
		//echo strlen($tweet);
		if ($this->itex_t_post2twitter_post($tweet))
		{
			//die('1111222222222');
			add_post_meta($post_id, 'itex_t_post2twitter_ready', '1', true);
			return;
		}
		return;
		//print_r($tweet);
 	    //die();
		//$url = get_permalink($post_id);
		//print_r($url);
		//die();
		print_r($post);
		die();
		add_post_meta($post_id, 'itex_t_post2twitter_ready', '1', true);
		
		///$aktt->do_blog_post_tweet($post_id);
	}

	/**
   	*  post2twitter post
   	*
   	* 
   	*/
	function itex_t_post2twitter_post($tweet) 
	{
		//print_r($tweet);
//		$data = "";
//    	$boundary = "---------------------".substr(md5(rand(0,32000)), 0, 10);
//    	$data .= "--$boundary\n";
//        $data .= "Content-Disposition: form-data; name=\"status\"\n\n".urlencode($tweet)."\n";
//        $data .= "--$boundary\n";
//        $data .= "Content-Disposition: form-data; name=\"source\"\n\niTwitter\n";
//        $data .= "--$boundary\n";
   
    	$user = base64_decode(get_option('itex_t_twitter_username'));
 		$pass = base64_decode(get_option('itex_t_twitter_userpass'));
    	$params = array('http' => array(
           'method' => 'POST',
           //'header' => 'Authorization: Basic '.base64_encode($user.":".$pass)."\r\n".'Content-Type: multipart/form-data; boundary='.$boundary,
           //'header' => 'Content-Type: multipart/form-data; boundary='.$boundary,
           
           //'content' => $data,
           'header' => 'Authorization: Basic '.base64_encode($user.":".$pass)."\r\n",
          
           'content' => 'status='.urlencode($tweet)
        ));
		
        //print_r($params);die();
   		$ctx = stream_context_create($params);
  		$fp = fopen('http://twitter.com/statuses/update.json', 'rb', false, $ctx);
  		if (!$fp) {
      		//throw new Exception("Problem with $url, $php_errormsg");
      		return false;
   		}
 
   		$response = @stream_get_contents($fp);
   		if ($response === false) {
      		//throw new Exception("Problem reading data from $url, $php_errormsg");
      		return false;
   		}	 
		
   		return true;
	}
	
	/**
   	*  shorturls
   	*
   	* 
   	*/
	function itex_t_shorturls($url) 
	{
		//print_r($url);
		//echo file_get_contents('http://tinyurl.com/api-create.php?url='.urlencode($url));
		//echo "\r\n\n\n\n\n";
		///$text = file_get_contents('http://bit.ly/?url='.urlencode($url));
		
		//print_r($url);
		//die();
		$itex_t_shorturls_service = get_option('itex_t_shorturls_service');
		if ($itex_t_shorturls_service == 'random')
		{
			$arr = array('tinyurl','bitly');
			$itex_t_shorturls_service = $arr[array_rand($arr)];
		}
		switch ($itex_t_shorturls_service)
		{
			case 'tinyurl':
			{
					$url = $this->GetSourceFromUrl('http://tinyurl.com/api-create.php?url='.urlencode($url));
					break;
			}
			case 'bitly':
			{
					$text = $this->GetSourceFromUrl('http://bit.ly/?url='.urlencode($url));
					$preg = '@"shortUrl": "(.*?)",@si';
					preg_match_all($preg,$text,$q);
					if (isset($q[1][0])) $url = $q[1][0];
					break;
			}
			case 'disabled':
			{
					
			}
			default:
		}
		return $url;
		//die($url);
	}

	/**
   	*  replace 
   	*
   	* 
   	*/
	function itex_t_replace_links($content) 
	{
		//$itex_t_shorturls_service = get_option('itex_t_replace_links_enable');
		if (!get_option('itex_t_replace_links_enable')) return $content;
		
		$preg	= '/([^a-zA-Z0-9])\@([a-zA-Z0-9_]+)/';
		$replace	= '\1@<a href="http://twitter.com/\2" rel="nofollow" target="_blank" title="\2 in Twitter">\2</a>\3';
		$content = preg_replace($preg,$replace,$content);
		$preg = '/(^|\s)#(\w+)/';
		$replace = '\1#<a href="http://search.twitter.com/search?q=%23\2" rel="nofollow" target="_blank" title="\2 in Twitter">\2</a>';
		$content = preg_replace($preg,$replace,$content);
		return $content;
	}

	/**
   	* Add admin menu to options
   	*
   	* @param   string   $domnod   $text
   	* @return  string	$text
   	*/
	function itex_t_menu()
	{
		if (is_admin()) add_options_page('iTwitter', 'iTwitter', 10, basename(__FILE__), array(&$this, 'itex_t_admin'));
		//die('1222222222');
	}

	/**
   	* Admin menu
   	*
   	*/
	function itex_t_admin()
	{
		if (!is_admin()) return 0;
		$this->lang_ru();
		$this->itex_t_admin_css();
		// Output the options page
		?>
		<div class="wrap">
		
			<form method="post">
			<h2><?php echo __('iTwitter Options', 'iTwitter');?></h2>
			<?php
			if (strlen($this->error))
			{
				echo '
				<div style="margin:10px auto; border:3px #f00 solid; background-color:#fdd; color:#000; padding:10px; text-align:center;">
					'.$this->error.'
				</div>';
				
			}
			if (isset($_POST['info_update']))
			{
				echo '<div style="margin:10px auto; border:3px  #55ff00 solid; background-color:#afa; padding:10px; text-align:center;">
				<a href="http://itex.name/go.php?http://itex.name/donation">'.__('Create and maintain a plugin take lot\'s of time. If you enjoy this plugin, do a Donation.', 'iTwitter').'</div>';
			}
			
			?>		
			
			
			
			                       
       			<!-- Main -->
        		
        			<?php 
        			?>
        		<ul style="text-align: center;font-weight: bold;font-size: 14px;">
        			<li style="display: inline;"><a href="#itex_global" onclick='document.getElementById("itex_global").style.display="";'>Global</a></li>
        			<li style="display: inline;"><a href="#itex_shorturls" onclick='document.getElementById("itex_shorturls").style.display="";'>Shorturls</a></li>
        			<li style="display: inline;"><a href="#itex_post2twitter" onclick='document.getElementById("itex_post2twitter").style.display="";'>Post2Twitter</a></li>
        			<li style="display: inline;"><a href="#itex_replace" onclick='document.getElementById("itex_replace").style.display="";'>Replace</a></li>
        			<li style="display: inline;"><a href="#itex_last_tweets" onclick='document.getElementById("itex_last_tweets").style.display="";'>LastTweets</a></li>
        			
        		</ul>
        		<p class="submit">
				<input type='submit' name='info_update' value='<?php echo __('Save Changes', 'iTwitter'); ?>' />
				</p>
				
        		<h3><a href="#itex_global" name="itex_global" onclick='document.getElementById("itex_global").style.display="";'>Global</a></h3>
       	 		<div id="itex_global"><?php $this->itex_t_admin_global(); ?></div>
       	 		<h3><a href="#itex_shorturls" name="itex_shorturls" onclick='document.getElementById("itex_shorturls").style.display="";'>ShortUrls</a></h3>
       	 		<div id="itex_shorturls"><?php $this->itex_t_admin_shorturls(); ?></div>
        		<h3><a href="#itex_post2twitter" name="itex_post2twitter" onclick='document.getElementById("itex_post2twitter").style.display="";'>Post2Twitter</a></h3>
       	 		<div id="itex_post2twitter"><?php $this->itex_t_admin_post2twitter(); ?></div>
        		<h3><a href="#itex_replace" name="itex_replace" onclick='document.getElementById("itex_replace").style.display="";'>Replace</a></h3>
       	 		<div id="itex_replace"><?php $this->itex_t_admin_replace(); ?></div>
        		<h3><a href="#itex_last_tweets" name="itex_last_tweets" onclick='document.getElementById("itex_last_tweets").style.display="";'>Last tweets</a></h3>
       	 		<div id="itex_last_tweets"><?php $this->itex_t_admin_last_tweets(); ?></div>
        		
       	 		
       	 		
       	 		<?php 
       	 		if(!get_option('itex_t_global_collapse')){ ?>
       	 		<script type="text/javascript">
       	 		document.getElementById("itex_shorturls").style.display="none";
       	 		document.getElementById("itex_post2twitter").style.display="none";
       	 		document.getElementById("itex_replace").style.display="none";
       	 		document.getElementById("itex_last_tweets").style.display="none";
       	 		</script>	
       	 		<?php } ?>
			</div>
			
			<p class="submit">
				<input type='submit' name='info_update' value='<?php echo __('Save Changes', 'iTwitter'); ?>' />
			</p>
			
			<ul style="text-align: center;font-weight: bold;font-size: 14px;">
        			<li style="display: inline;"><a href="#itex_global" onclick='document.getElementById("itex_global").style.display="";'>Global</a></li>
        			<li style="display: inline;"><a href="#itex_shorturls" onclick='document.getElementById("itex_shorturls").style.display="";'>Shorturls</a></li>
        			<li style="display: inline;"><a href="#itex_post2twitter" onclick='document.getElementById("itex_post2twitter").style.display="";'>Post2Twitter</a></li>
        			<li style="display: inline;"><a href="#itex_replace" onclick='document.getElementById("itex_replace").style.display="";'>Replace</a></li>
        			<li style="display: inline;"><a href="#itex_last_tweets" onclick='document.getElementById("itex_last_tweets").style.display="";'>LastTweets</a></li>
        	</ul>
        		
			<p align="center">
				<?php echo __("Powered by ",'iTwitter')."<a href='http://itex.name' title='iTex iTwitter'>iTex iTwitter</a> ".__("Version:",'iTwitter').$this->version; ?>
			</p>				
			</form>
		
		</div>
		<?php
	}

	/**
   	* Css fo admin menu
   	*
   	*/
	function itex_t_admin_css()
	{
		?>
		<style type='text/css'>
			#edit_tabs li {            
				list-style-type: none;
				float: left;       
				margin: 2px 5px 0 0;           
				padding-left: 15px;  
				text-align: center;
			}                        

			#edit_tabs li a {           
				display: block;                            
				font-size: 85%;                               
				font-family: "Lucida Grande", "Verdana";
				font-weight: bold;                          
				float: left;                                       
				color: #999;
				border-bottom: none;
				padding: 2px 15px 2px 0;	
				width: auto !important;
				width: 50px;        
				min-width: 50px;                                                     
				text-shadow: white 0 1px 0;  
			}               

			#edit_sections .section {
				background: url('images/bg_tab_section.gif') no-repeat top left;
				padding-left: 10px;
				padding-top: 15px;
				height: auto !important;
				height: 200px;       
				min-height: 200px;
				display: none;
			}              

			#edit_sections .section ul {
				padding-left: 10px;
				width: 500px;
			}

			#edit_sections .current {
				display: block;
			}                   

			#edit_sections .section .section_warn {
				background: #FFFFE0;
				border: 1px solid #EBEBA9;
				padding: 8px;
				float: right;
				width: 300px;
				font-size: 11px;
			}       
		</style>
		<?php
	}

	/**
   	* Global section admin menu
   	*
   	*/
	function itex_t_admin_global()
	{
		if (isset($_POST['info_update']))
		{
			
			if (isset($_POST['itex_t_twitter_username']))
			{
				update_option('itex_t_twitter_username', base64_encode($_POST['itex_t_twitter_username']));
			}
			if (isset($_POST['itex_t_twitter_userpass']))
			{
				update_option('itex_t_twitter_userpass', base64_encode($_POST['itex_t_twitter_userpass']));
			}
			
			if (isset($_POST['itex_t_cache_enable']))
			{
				update_option('itex_t_cache_enable', intval($_POST['itex_t_cache_enable']));
			}
			if (isset($_POST['itex_t_cache_time']))
			{
				update_option('itex_t_cache_time', intval($_POST['itex_t_cache_time']));
			}
			if (isset($_POST['itex_t_cache_where']))
			{
				update_option('itex_t_cache_where', intval($_POST['itex_t_cache_where']));
			}
			if (isset($_POST['itex_t_cache_file']))
			{
				update_option('itex_t_cache_file', trim($_POST['itex_t_cache_file']));
			}
			if (isset($_POST['']))
			{
				update_option('', ($_POST['']));
			}
			if (isset($_POST['']))
			{
				update_option('', ($_POST['']));
			}
			
			if (isset($_POST['global_debugenable']))
			{
				update_option('itex_t_global_debugenable', intval($_POST['global_debugenable']));
			}

			if (isset($_POST['global_debugenable_forall']))
			{
				update_option('itex_t_global_debugenable_forall', intval($_POST['global_debugenable_forall']));
			}

			
			if (isset($_POST['global_collapse']))
			{
				update_option('itex_t_global_collapse', !intval($_POST['global_collapse']));
			}

			if ((isset($_POST['global_widget'])) || (isset($_POST['global_widget'])))
			{
				$s_w = wp_get_sidebars_widgets();
				$ex = 0;
				if (count($s_w['sidebar-1'])) foreach ($s_w['sidebar-1'] as $k => $v)
				{
					if ($v == 'itwitter')
					{
						$ex = 1;
						if (!$_POST['global_widget']) unset($s_w['sidebar-1'][$k]);
					}
				}
				if (!$ex && $_POST['global_widget']) $s_w['sidebar-1'][] = 'itwitter';
				wp_set_sidebars_widgets( $s_w );
			}


			echo "<div class='updated fade'><p><strong>Settings saved.</strong></p></div>";
		}

		?>
		<table class="form-table" cellspacing="2" cellpadding="5" width="100%">
						
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for=""><?php echo __('Twitter account:', 'iTwitter'); ?></label>
					</th>
					<td>
					<?php
						echo "<input type='text' size='100' ";
						echo "name='itex_t_twitter_username' ";
						echo "value='".base64_decode(get_option('itex_t_twitter_username'))."' />\n";
						echo '<label for="">'.__('Twitter username', 'iTwitter').'</label>';
						echo "<br/>\n";
						
						echo "<input type='password' size='100' ";
						echo "name='itex_t_twitter_userpass' ";
						echo "value='".base64_decode(get_option('itex_t_twitter_userpass'))."' />\n";
						echo '<label for="">'.__('Twitter password', 'iTwitter').'</label>';
						echo "<br/>\n";
					?>
					</td>
				</tr>
				
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for=""><?php echo __('Cache:', 'iTwitter'); ?></label>
					</th>
					<td>
						<?php
						echo "<select name='itex_t_cache_enable' id='itex_t_cache_enable'>\n";
						echo "<option value='1'";

						if(get_option('itex_t_cache_enable')) echo " selected='selected'";
						echo ">".__("Enabled", 'iTwitter')."</option>\n";
						echo "<option value='0'";
						if(!get_option('itex_t_cache_enable')) echo" selected='selected'";
						echo ">".__("Disabled", 'iTwitter')."</option>\n";
						echo "</select>\n";
						echo '<label for="">'.__('Enable cache', 'iTwitter').'.</label>';
						echo "<br/>";
						
						echo "<input type='text' size='20' ";
						echo "name='itex_t_cache_time'";
						echo "value='";
						$itex_t_cache_time = trim(get_option('itex_t_cache_time'));
						if (empty($itex_t_cache_time) || $itex_t_cache_time < 600) $itex_t_cache_time = 3600;
						echo $itex_t_cache_time;
						echo "' />\n";
						echo '<label for="">'.__('Cache time must be > 600. 60 sec.*10 = 600sec.=10min', 'iTwitter').'</label>';
						echo "<br/>\n";
						
						echo "<select name='itex_t_cache_where' id='itex_t_cache_where'>\n";
						echo "<option value='1'";
						if(get_option('itex_t_cache_where')) echo " selected='selected'";
						echo ">".__("File", 'iTwitter')."</option>\n";
						echo "<option value='0'";
						if(!get_option('itex_t_cache_where')) echo" selected='selected'";
						echo ">".__("Base", 'iTwitter')."</option>\n";
						echo "</select>\n";
						echo '<label for="">'.__('Where to cache', 'iTwitter').'.</label>';
						echo "<br/>";
						
						echo "<input type='text' size='100' ";
						echo "name='itex_t_cache_file'";
						echo "value='";
						$itex_t_cache_file = trim(get_option('itex_t_cache_file'));
						if (empty($itex_t_cache_file) || strlen($itex_t_cache_file) < 10) 
						{
							$itex_t_cache_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'iTwitterCacheFile.txt';
						}
						echo $itex_t_cache_file;
						echo "' />\n";
						echo '<label for="">'.__('Cache file adress. If store cache in file. Dir must be writeable', 'iTwitter').'</label>';
						echo "<br/>\n";
						?>
					</td>
				</tr>
				
				
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for=""><?php echo __('Global debug:', 'iTwitter'); ?></label>
					</th>
					<td>
						<?php
						echo "<select name='global_debugenable' id='global_debugenable'>\n";
						echo "<option value='1'";

						if(get_option('itex_t_global_debugenable')) echo " selected='selected'";
						echo ">".__("Enabled", 'iTwitter')."</option>\n";

						echo "<option value='0'";
						if(!get_option('itex_t_global_debugenable')) echo" selected='selected'";
						echo ">".__("Disabled", 'iTwitter')."</option>\n";
						echo "</select>\n";

						echo '<label for="">'.__('Debug log in footer. For see debug user must register', 'iTwitter').'.</label>';

						echo "<br/>";

						echo "<select name='global_debugenable_forall' id='global_debugenable_forall'>\n";
						echo "<option value='1'";

						if(get_option('itex_t_global_debugenable_forall')) echo " selected='selected'";
						echo ">".__("Enabled", 'iTwitter')."</option>\n";

						echo "<option value='0'";
						if(!get_option('itex_t_global_debugenable_forall')) echo" selected='selected'";
						echo ">".__("Disabled", 'iTwitter')."</option>\n";
						echo "</select>\n";

						echo '<label for="">'.__('Debug log in footer for all, who open the site. Dont leave this parameter switched Enabled for a long time, because in this case it will disclose your private data', 'iTwitter').'.</label>';

						?>
					</td>
				</tr>
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for=""><?php echo __('Widgets settings:', 'iTwitter'); ?></label>
					</th>
					<td>
						<?php
						$ws = wp_get_sidebars_widgets();


						echo "<select name='global_widget' id='global_widget'>\n";
						echo "<option value='0'";
						if (count($ws['sidebar-1'])) if(!in_array('itwitter',$ws['sidebar-1'])) echo" selected='selected'";
						echo ">".__("Disabled", 'iTwitter')."</option>\n";

						echo "<option value='1'";
						if (count($ws['sidebar-1'])) if (in_array('itwitter',$ws['sidebar-1'])) echo " selected='selected'";
						echo ">".__('Active','iTwitter')."</option>\n";

						echo "</select>\n";

						echo '<label for="">'.__('Widget Active', 'iTwitter').'</label>';

						echo "<br/>\n";

						?>
					</td>
				</tr>
				
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for=""><?php echo __('Collapse headlines settings:', 'iTwitter'); ?></label>
					</th>
					<td>
						<?php
						echo "<select name='global_collapse' id='global_collapse'>\n";
						echo "<option value='1'";

						if(!get_option('itex_t_global_collapse')) echo " selected='selected'";
						echo ">".__("Enabled", 'iTwitter')."</option>\n";

						echo "<option value='0'";
						if(get_option('itex_t_global_collapse')) echo" selected='selected'";
						echo ">".__("Disabled", 'iTwitter')."</option>\n";
						echo "</select>\n";


						?>
					</td>
				</tr>
				
			</table>
			<?php
	}


	/**
   	* ShortUrls section admin menu
   	*
   	*/
	function itex_t_admin_shorturls()
	{
		if (isset($_POST['info_update']))
		{
			if (isset($_POST['itex_t_shorturls_service']))
			{
				update_option('itex_t_shorturls_service', trim($_POST['itex_t_shorturls_service']));
			}
			wp_cache_flush();
			echo "<div class='updated fade'><p><strong>Settings saved.</strong></p></div>";
		}
		?>
		<table class="form-table" cellspacing="2" cellpadding="5" width="100%">
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for=""><?php echo __('Short urls service:', 'iTwitter');?></label>
					</th>
					<td>
						<?php
						$itex_t_shorturls_service = get_option('itex_t_shorturls_service');
						if (strlen($itex_t_shorturls_service) < 2) 
						{
							//$arr = array('tinyurl','bitly');
							//$k = array_rand($arr);
							//$itex_t_shorturls_service = $arr[array_rand($arr)];
							$itex_t_shorturls_service = 'random';
						}
						echo "<select name='itex_t_shorturls_service' id='itex_t_shorturls_service'>\n";
						
						echo "<option value='disabled'";
						if ($itex_t_shorturls_service == 'disabled') echo " selected='selected'";
						echo ">".__("Disabled", 'iTwitter')."</option>\n";
						
						echo "<option value='random'";
						if ($itex_t_shorturls_service == 'random') echo " selected='selected'";
						echo ">".__("Random", 'iTwitter')."</option>\n";
						
						
						echo "<option value='tinyurl'";
						if ($itex_t_shorturls_service == 'tinyurl') echo " selected='selected'";
						echo ">".__("TinyUrl", 'iTwitter')."</option>\n";

						echo "<option value='bitly'";
						if ($itex_t_shorturls_service == 'bitly') echo " selected='selected'";
						echo ">".__("Bitly", 'iTwitter')."</option>\n";

						echo "</select>\n";

						echo '<label for="">'.__("Service for tiny urls", 'iTwitter').'</label>';
						echo "<br/>\n";
						?>
					</td>
				</tr>
				
				
				
			</table>
			<?php
	}

/**
   	* Post2Twitter section admin menu
   	*
   	*/
	function itex_t_admin_post2twitter()
	{
		if (isset($_POST['info_update']))
		{
			if (isset($_POST['itex_t_post2twitter_enable']))
			{
				update_option('itex_t_post2twitter_enable', intval($_POST['itex_t_post2twitter_enable']));
			}
			if (isset($_POST['itex_t_post2twitter_template']))
			{
				update_option('itex_t_post2twitter_template', base64_encode($_POST['itex_t_post2twitter_template']));
			}
			
			if (isset($_POST['itex_t_shorturls_service']))
			{
				update_option('itex_t_shorturls_service', trim($_POST['itex_t_shorturls_service']));
			}
			wp_cache_flush();
			echo "<div class='updated fade'><p><strong>Settings saved.</strong></p></div>";
		}
		?>
		<table class="form-table" cellspacing="2" cellpadding="5" width="100%">
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for=""><?php echo __('post2twitter:', 'iTwitter');?></label>
					</th>
					<td>
						<?php
						echo "<select name='itex_t_post2twitter_enable' id='itex_t_post2twitter_enable'>\n";
						echo "<option value='1'";

						if(get_option('itex_t_post2twitter_enable')) echo " selected='selected'";
						echo ">".__("Enabled", 'iTwitter')."</option>\n";

						echo "<option value='0'";
						if(!get_option('itex_t_post2twitter_enable')) echo" selected='selected'";
						echo ">".__("Disabled", 'iTwitter')."</option>\n";
						echo "</select>\n";

						echo '<label for="">'.__("Working", 'iTwitter').'</label>';
						echo "<br/>\n";
						
						
						?>
					</td>
					
				</tr>
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for=""><?php echo __('Template:', 'iTwitter');?></label>
					</th>
					<td>
						<?php
						echo "<input type='text' size='100' ";
						echo "name='itex_t_post2twitter_template' ";
						$itex_t_post2twitter_template = base64_decode(get_option('itex_t_post2twitter_template'));
						if (strlen($itex_t_post2twitter_template) <2) 
						{
							$itex_t_post2twitter_template = '%title% %excerpt% %url%';
						}
						echo "value='".$itex_t_post2twitter_template."' />\n";
						echo '<label for="">'.__('Twitter post template, for example "New blogpost: %title% %excerpt% %url%". max post lenght will be 140 symbols. If tweet will be to big, first skip description, then skip title', 'iTwitter').'</label>';
						echo "<br/>\n";
						
						
						//$this->itex_t_post2twitter(6610);
						
						?>
					</td>
					
				</tr>
				
				
			</table>
			<?php
	}

	/**
   	* Replace #tagname or @username in blog posts section admin menu
   	*
   	*/
	function itex_t_admin_replace()
	{
		if (isset($_POST['info_update']))
		{
			if (isset($_POST['itex_t_replace_links_enable']))
			{
				update_option('itex_t_replace_links_enable', intval($_POST['itex_t_replace_links_enable']));
			}
			//wp_cache_flush();
			echo "<div class='updated fade'><p><strong>Settings saved.</strong></p></div>";
		}
		?>
		<table class="form-table" cellspacing="2" cellpadding="5" width="100%">
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for=""><?php echo __('Replace:', 'iTwitter');?></label>
					</th>
					<td>
						<?php
						echo "<select name='itex_t_replace_links_enable' id='itex_t_replace_links_enable'>\n";
						echo "<option value='1'";

						if(get_option('itex_t_replace_links_enable')) echo " selected='selected'";
						echo ">".__("Enabled", 'iTwitter')."</option>\n";

						echo "<option value='0'";
						if(!get_option('itex_t_replace_links_enable')) echo" selected='selected'";
						echo ">".__("Disabled", 'iTwitter')."</option>\n";
						echo "</select>\n";

						echo '<label for="">'.__("Replace #tagname or @username in blog posts and comments to twitter links", 'iTwitter').'</label>';
						echo "<br/>\n";
						
						?>
					</td>
				</tr>
				
				
				
			</table>
			<?php
	}
	
	/**
   	* Replace #tagname or @username in blog posts section admin menu
   	*
   	*/
	function itex_t_admin_last_tweets()
	{
		if (isset($_POST['info_update']))
		{
			if (isset($_POST['itex_t_last_tweets_enable']))
			{
				update_option('itex_t_last_tweets_enable', intval($_POST['itex_t_last_tweets_enable']));
			}
			if (isset($_POST['itex_t_last_tweets_users']))
			{
				update_option('itex_t_last_tweets_users', trim($_POST['itex_t_last_tweets_users']));
			}
			if (isset($_POST['itex_t_last_tweets_pos']))
			{
				update_option('itex_t_last_tweets_pos', trim($_POST['itex_t_last_tweets_pos']));
			}
			//wp_cache_flush();
			echo "<div class='updated fade'><p><strong>Settings saved.</strong></p></div>";
		}
		?>
		<table class="form-table" cellspacing="2" cellpadding="5" width="100%">
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for=""><?php echo __('Last tweets:', 'iTwitter');?></label>
					</th>
					<td>
						<?php
						echo "<select name='itex_t_last_tweets_enable' id='itex_t_last_tweets_enable'>\n";
						echo "<option value='1'";

						if(get_option('itex_t_last_tweets_enable')) echo " selected='selected'";
						echo ">".__("Enabled", 'iTwitter')."</option>\n";

						echo "<option value='0'";
						if(!get_option('itex_t_last_tweets_enable')) echo" selected='selected'";
						echo ">".__("Disabled", 'iTwitter')."</option>\n";
						echo "</select>\n";

						echo '<label for="">'.__("Working", 'iTwitter').'</label>';
						echo "<br/>\n";
						
						echo "<input type='text' size='50' ";
						echo "name='itex_t_last_tweets_users'";
						echo "id='itex_t_last_tweets_users' ";
						$users = get_option('itex_t_last_tweets_users');
						$user = base64_decode(get_option('itex_t_twitter_username'));
						if (!$users) 
						{
							
							if (!empty($user)) $users = $users.',itexname';
						}
						echo "value='".$users."' />\n";
						?>
						<p style="margin: 5px 10px;"><?php echo __('Enter twitter users separated by commas. Like ', 'iMoney').'"'.(!empty($user)?$user:'Your-Tweet-Name').',itexname"';?></p>
						
						<?php
						
						echo "<select name='itex_t_last_tweets_pos' id='itex_t_last_tweets_pos'>\n";
						
						$pos = array( 'footer', 'sidebar', 'beforecontent','aftercontent');
						foreach ( $pos as $k)
						{
							echo "<option value='".$k."'";
							if(get_option('itex_t_last_tweets_pos') == $k) echo " selected='selected'";
							echo ">".$k."</option>\n";
						}
						echo "</select>\n";
						echo '<label for="">'.__('Block position', 'iMoney').'</label>';
						echo "<br/>\n";
						
						?>
					</td>
				</tr>
				
				
				
			</table>
			<?php
			
			//$this->itex_t_init_last_tweets();
	}

	
	
	/**
   	* Html section admin menu
   	*
   	*/
	function itex_t_admin_html()
	{
		if (isset($_POST['info_update']))
		{
			if (isset($_POST['html_enable']))
			{
				update_option('itex_t_html_enable', intval($_POST['html_enable']));
			}
			if (isset($_POST['html_footer']))
			{
				update_option('itex_t_html_footer', $_POST['html_footer']);
			}
			if (isset($_POST['html_footer_enable']))
			{
				update_option('itex_t_html_footer_enable', $_POST['html_footer_enable']);
			}
			if (isset($_POST['html_beforecontent']))
			{
				update_option('itex_t_html_beforecontent', $_POST['html_beforecontent']);
			}
			if (isset($_POST['html_beforecontent_enable']))
			{
				update_option('itex_t_html_beforecontent_enable', $_POST['html_beforecontent_enable']);
			}
			if (isset($_POST['html_aftercontent']))
			{
				update_option('itex_t_html_aftercontent', $_POST['html_aftercontent']);
			}
			if (isset($_POST['html_aftercontent_enable']))
			{
				update_option('itex_t_html_aftercontent_enable', $_POST['html_aftercontent_enable']);
			}

			if (isset($_POST['html_sidebar']))
			{
				update_option('itex_t_html_sidebar', $_POST['html_sidebar']);
			}
			if (isset($_POST['html_sidebar_enable']))
			{
				update_option('itex_t_html_sidebar_enable', $_POST['html_sidebar_enable']);
				//				$s_w = wp_get_sidebars_widgets();
				//				$ex = 0;
				//				if (count($s_w['sidebar-1'])) foreach ($s_w['sidebar-1'] as $k => $v)
				//				{
				//					if ($v == 'imoney_html')
				//					{
				//						$ex = 1;
				//						if (!$_POST['html_sidebar_enable']) unset($s_w['sidebar-1'][$k]);
				//					}
				//				}
				//				if (!$ex && ($_POST['html_sidebar_enable'])) $s_w['sidebar-1'][] = 'imoney_html';
				//				wp_set_sidebars_widgets( $s_w );
			}
			wp_cache_flush();
			echo "<div class='updated fade'><p><strong>Settings saved.</strong></p></div>";
		}
		?>
		<table class="form-table" cellspacing="2" cellpadding="5" width="100%">
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for=""><?php echo __('Html inserts:', 'iTwitter');?></label>
					</th>
					<td>
						<?php
						echo "<select name='html_enable' id='html_enable'>\n";
						echo "<option value='1'";

						if(get_option('itex_t_html_enable')) echo " selected='selected'";
						echo ">".__("Enabled", 'iTwitter')."</option>\n";

						echo "<option value='0'";
						if(!get_option('itex_t_html_enable')) echo" selected='selected'";
						echo ">".__("Disabled", 'iTwitter')."</option>\n";
						echo "</select>\n";

						echo '<label for="">'.__("Working", 'iTwitter').'</label>';
						echo "<br/>\n";
						?>
					</td>
				</tr>
				
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for=""><?php echo __('Footer:', 'iTwitter');?></label>
					</th>
					<td>
						<?php
						echo "<textarea rows='5' cols='80'";
						echo "name='html_footer'";
						echo "id='html_footer'>";
						echo stripslashes(get_option('itex_t_html_footer'))."</textarea>\n";
						?>
						<p style="margin: 5px 10px;"><?php echo __('Enter your html in this box.', 'iTwitter');?></p>
						
						<?php
						echo "<select name='html_footer_enable' id='html_footer_enable'>\n";
						echo "<option value='1'";

						if(get_option('itex_t_html_footer_enable')) echo " selected='selected'";
						echo ">".__("Enabled", 'iTwitter')."</option>\n";

						echo "<option value='0'";
						if(!get_option('itex_t_html_footer_enable')) echo" selected='selected'";
						echo ">".__("Disabled", 'iTwitter')."</option>\n";
						echo "</select>\n";

						echo '<label for="">'.__("Working", 'iTwitter').'</label>';
						echo "<br/>\n";
						?>
					</td>
				</tr>
				
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for=""><?php echo __('Before Content:', 'iTwitter');?></label>
					</th>
					<td>
						<?php
						echo "<textarea rows='5' cols='80'";
						echo "name='html_beforecontent'";
						echo "id='html_beforecontent'>";
						echo stripslashes(get_option('itex_t_html_beforecontent'))."</textarea>\n";
						?>
						<p style="margin: 5px 10px;"><?php echo __('Enter your html in this box.', 'iTwitter');?></p>
						
						<?php
						echo "<select name='html_beforecontent_enable' id='html_beforecontent_enable'>\n";
						echo "<option value='1'";

						if(get_option('itex_t_html_beforecontent_enable')) echo " selected='selected'";
						echo ">".__("Enabled", 'iTwitter')."</option>\n";

						echo "<option value='0'";
						if(!get_option('itex_t_html_beforecontent_enable')) echo" selected='selected'";
						echo ">".__("Disabled", 'iTwitter')."</option>\n";
						echo "</select>\n";

						echo '<label for="">'.__("Working", 'iTwitter').'</label>';
						echo "<br/>\n";
						?>
					</td>
				</tr>
				
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for=""><?php echo __('After Content:', 'iTwitter');?></label>
					</th>
					<td>
						<?php
						echo "<textarea rows='5' cols='80'";
						echo "name='html_aftercontent'";
						echo "id='html_aftercontent'>";
						echo stripslashes(get_option('itex_t_html_aftercontent'))."</textarea>\n";
						?>
						<p style="margin: 5px 10px;"><?php echo __('Enter your html in this box.', 'iTwitter');?></p>
						
						<?php
						echo "<select name='html_aftercontent_enable' id='html_aftercontent_enable'>\n";
						echo "<option value='1'";

						if(get_option('itex_t_html_aftercontent_enable')) echo " selected='selected'";
						echo ">".__("Enabled", 'iTwitter')."</option>\n";

						echo "<option value='0'";
						if(!get_option('itex_t_html_aftercontent_enable')) echo" selected='selected'";
						echo ">".__("Disabled", 'iTwitter')."</option>\n";
						echo "</select>\n";

						echo '<label for="">'.__("Working", 'iTwitter').'</label>';
						echo "<br/>\n";
						?>
					</td>
				</tr>
				
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for=""><?php echo __('Sidebar:', 'iTwitter');?></label>
					</th>
					<td>
						<?php
						echo "<textarea rows='5' cols='80'";
						echo "name='html_sidebar'";
						echo "id='html_sidebar'>";
						echo stripslashes(get_option('itex_t_html_sidebar'))."</textarea>\n";
						?>
						<p style="margin: 5px 10px;"><?php echo __('Enter your html in this box.', 'iTwitter');?></p>
						
						<?php
						echo "<select name='html_sidebar_enable' id='html_sidebar_enable'>\n";
						echo "<option value='1'";

						if(get_option('itex_t_html_sidebar_enable')) echo " selected='selected'";
						echo ">".__("Enabled", 'iTwitter')."</option>\n";

						echo "<option value='0'";
						if(!get_option('itex_t_html_sidebar_enable')) echo" selected='selected'";
						echo ">".__("Disabled", 'iTwitter')."</option>\n";
						echo "</select>\n";

						echo '<label for="">'.__("Working", 'iTwitter').'</label>';
						echo "<br/>\n";
						
						
						?>
					</td>
				</tr>
				
			</table>
			<?php
			
	}

	/**
   	* get http source from url
   	*
   	* @param   string   $domnod   $text
   	* @param   string   $find   find test
   	* @return  string	$text
   	*/
	function GetSourceFromUrl($url)
	{
		$a = $this->cacheGet('GetFile'.$url);
		if ($a !== false) return $a;
		
		//echo $url;
		//die();
		$content = @file_get_contents($url);
		if (strlen($content))
		{
			//die('dsdssssssssss');
			$this->cacheIt('GetFile'.$url,$content);
			return $content;
		}
		//die('31231231231');
		return false;
		
	}
	
  	/**
   	*
   	*
   	* @param   string   $   1
   	* @param   string   $   2
   	* @return  string	$
   	*/
	function cacheIt($id,$value)
	{
		//if (rand(1,10) == 1) $this->cacheClear();
		if (!get_option('itex_t_cache_enable')) return false;
		//itex_t_cache_enable
		//itex_t_cache_time
		//itex_t_cache_where
		//itex_t_cache_file
		//print_r($this->cache);die('wewewewe');
		$where = get_option('itex_t_cache_where');
		$file = get_option('itex_t_cache_file');
		$time = get_option('itex_t_cache_time');
		if (empty($this->cache))
		{
			//$this->cacheGet(''); //для инициализации кеша
			if ($where)
			{
				if (!empty($file) && is_file($file)) $this->cache = @unserialize(@file_get_contents($file));
			}
			else
			{
				$this->cache = @unserialize(base64_decode(get_option('itex_t_cache_base')));
			}
		}
		$this->cache[$id] = array('id'=>$id,'time'=>time(),'value'=>$value);
		//удаляем устаревшие
		foreach ($this->cache as $k=>$v)
		{
			if (($v['time']+$time)<time() ) unset($this->cache[$k]);
		}
		
		if ($where)
		{
			if (!empty($file)) @file_put_contents($file,serialize($this->cache));
		}
		else update_option('itex_t_cache_base', base64_encode(serialize($this->cache)));
		
		//print_r($this->cache);die('wewewewe');
		return true;
		
		
	}
	
  	/**
   	*
   	*
   	* @param   string   $   1
   	* @param   string   $   2
   	* @return  string	$
   	*/
	function cacheGet($id)
	{
		if (!get_option('itex_t_cache_enable')) return false;
		//itex_t_cache_enable
		//itex_t_cache_time
		//itex_t_cache_where
		//itex_t_cache_file
		$where = get_option('itex_t_cache_where');
		$file = get_option('itex_t_cache_file');
		$time = get_option('itex_t_cache_time');
		if (empty($this->cache))
		{
			if ($where)
			{
				if (!empty($file) && is_file($file)) $this->cache = @unserialize(@file_get_contents($file));
			}
			else
			{
				$this->cache = @unserialize(base64_decode(get_option('itex_t_cache_base')));
			}
		}
		//print_r(get_option('itex_t_cache_base'));die('1111');
		//print_r($this->cache);die();
		if (isset($this->cache[$id]))
		{
			if (($this->cache[$id]['time']+$time)>time()) 
				return $this->cache[$id]['value'];
			unset($this->cache[$id]);
		}
		return false;
		
	}
}

if (function_exists(add_action)) $itex_twitter = & new itex_twitter();

?>