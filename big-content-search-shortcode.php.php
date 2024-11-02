<?php
/*
Plugin Name: BigContentSearch Shortcode
Plugin URI: http://bigcontentsearch.com/blogsensewp.php
Description: Use this shortcode plugin to import content from your Big Content Search database..
Version: 1.0.0.3
Author: Hudson Atwell
Author URI: https://plus.google.com/115026361664097398228/posts
*/

define('BIGCONTENTSEARCHSHORTCODE_URLPATH', WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' );

function bcss_stealth_curl($url,$params)
{		
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);	
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);		
	curl_setopt($ch, CURLOPT_TIMEOUT ,25);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	$data = curl_exec($ch);
	//echo $data; exit;
	curl_close($ch);
	return $data;
}


function shortcode_bigcontentsearch($atts){
	extract(shortcode_atts(array(
	'keywords' => false,
	'return' => "content",
	), $atts));
	if ( $keywords == false )
	{
		$content = "";
	}
	else
	{
		//echo "<hr>";
		$bcss_username = get_option('bcss_username');
		$bcss_api_key = get_option('bcss_api_key');
		$params = array('username'=>$bcss_username,'api_key'=>$bcss_api_key,'search_term'=>$keywords);
		//$keywords = urlencode($keywrods);
		
		$url = "https://members.bigcontentsearch.com/api/article_get_by_search_term";
	
		$data =  bcss_stealth_curl($url,$params);
		$data = json_decode($data, true);
		
		$body = $data['response']['text'];
		$title = $data['response']['title'];
		$body = str_replace($title,"",$body);
		if ($return=='content')
		{
			$content = $body;
			$content = nl2br($content);
			//print_r($data);exit;
		}
		else
		{			
			//print_r($data);exit;
			$content = $title;
			//echo $content;exit;
		}
	}
	
	return $content;
}
function bcss_prepare($title)
{
	return str_replace('&quot;','"',$title);
}
add_shortcode('bigcontentsearch', 'shortcode_bigcontentsearch');

if (!is_admin())
{
	add_filter('the_title', 'do_shortcode');
	add_filter('wp_title', 'bcss_prepare');
	add_filter('wp_title', 'do_shortcode');	
	//add_filter('the_excerpt', 'shortcode_bigcontentsearch');	
	add_filter('the_excerpt', 'do_shortcode');	
	//add_filter('get_the_excerpt', 'do_shortcode');	
	
}





function bcss_setup_admin() {
	//echo 1;
	add_options_page( 'BigContentSearch Shortcode', 'BigContentSearch Shortcode', 5, __FILE__, 'bcss_options_page' );
	//wp_enqueue_script('jquery');
}

/**
 * Options page
 *
 * @package StateAware
 * @since 0.1
 */
function bcss_options_page() {
	global $CANADA;
	global $Provinces;
	global $table_prefix;
	$post_types=get_post_types('','names'); 
	
	?>
	
	<div class="wrap">
		<h2>BigContentSearch Shortcode Setup</h2>
		<br>
		<div style=''>
		<img src="<?php echo BIGCONTENTSEARCHSHORTCODE_URLPATH; ?>BCS-logo_small.png" style="" border=0 title="BigContentSearch">		
		</div>
		<blockquote>
		Please enter in your BigContentSearch username and api_key in the input fields below. Click <a href='http://bigcontentsearch.com/blogsensewp.php' target=_blank>here</a> for registration if you have not created an account yet!
		</blockquote>
		<blockquote>
		<strong>Usage:</strong>
		[bigcontentsearch keywords="keyword" return="content"] will return the article content.
		[bigcontentsearch keywords="keyword,keyphrase" return="title"] will return the article title.
		</blockquote>
		
		
		<div style='float:left;'>
			<form method="post" action="options.php">
			<?php wp_nonce_field('update-options'); ?>
			
			<table class="form-table">
			
			<tr valign="top">
				<th scope="row">
				<img src="<?php echo BIGCONTENTSEARCHSHORTCODE_URLPATH; ?>tip.png" style="cursor:pointer;" border=0 title="Email (or username in case of older BCS members) that is used to log-in to https://members.bigcontentsearch.com/">					
				Email<br/></th>
				<td>   
				<?php
				   $bcss_username = get_option('bcss_username');
				   ?>
					<input size=25 name="bcss_username"  value = '<?php echo $bcss_username; ?>' >

				</td>
			</tr>	
			<tr valign="top">
				<th scope="row">
				<img src="<?php echo BIGCONTENTSEARCHSHORTCODE_URLPATH; ?>tip.png" style="cursor:pointer;" border=0 title="A secret key that BCS user generates on the BCS control panel at https://members.bigcontentsearch.com/.">					
				API Key<br/></th>
				<td>   
				<?php
				   $bcss_api_key = get_option('bcss_api_key');
				   ?>
					<input size=25 name="bcss_api_key"  value = '<?php echo $bcss_api_key; ?>' >

				</td>
			</tr>			
			
			</table>
			
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="bcss_username,bcss_api_key" />
			
			<p class="submit">
			<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
			</p>
			
			</form>
		</div>
	
	
	</div>
	<?php
}



add_action( 'init', 'bcss_admin_init', 10, 2 );

function bcss_admin_init()
{
	if (is_admin())
	{		
		add_action( 'admin_menu', 'bcss_setup_admin', 10, 2 );
		add_option( 'bcss_username', '' );
		add_option( 'bcss_api_key', '' );
	}
}
?>
