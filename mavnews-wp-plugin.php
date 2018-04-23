<?php
/**
 * @package Mavnews_WP_Plugin
 * @version 0.1
 */
/*
Plugin Name: Mavnews
Plugin URI: https://mavnews.dailymaverick.co.za
Description: Scan the news and easily use wire copy on the Daily Maverick
Author: Jason Norwood-Young
Version: 0.1
Author Email: jason@10layer.com
*/

require_once("vendor/autoload.php");
use GuzzleHttp\Client;

$AFP_ID=504;
$NEWS24_ID=685;

$api_base_url = 'http://mavnews.dailymaverick.co.za:5001/api/';

add_action( 'admin_menu', 'mavnews_add_admin_menu' );
add_action( 'admin_init', 'mavnews_settings_init' );


function mavnews_add_admin_menu(  ) {
	add_menu_page(
		'Mavnews',
		'Mavnews',
		'edit_posts',
		'mavnews.php',
		"mavnews_html",
		'dashicons-editor-kitchensink',
		20
	);
	add_submenu_page( 'mavnews.php', 'Mavnews Settings', 'Mavnews Settings', 'manage_options', 'mavnews', 'mavnews_options_page' );

}


function mavnews_settings_init(  ) { 

	register_setting( 'pluginPage', 'mavnews_settings' , 'sanitize');

	add_settings_section(
		'mavnews_pluginPage_section', 
		__( 'API Authentication', 'wordpress' ), 
		'mavnews_settings_section_callback', 
		'pluginPage'
	);

	add_settings_field( 
		'mavnews_api_username', 
		__( 'Mavnews API username', 'wordpress' ), 
		'mavnews_api_username_render', 
		'pluginPage', 
		'mavnews_pluginPage_section' 
	);

	add_settings_field( 
		'mavnews_api_password', 
		__( 'Mavnews API password', 'wordpress' ), 
		'mavnews_api_password_render', 
		'pluginPage', 
		'mavnews_pluginPage_section' 
	);


}

function sanitize($input) {
	if (isset($input["password"])) {
		if (trim($input["password"]) == "") {
			delete($input["password"]);
		}
	}
	return $input;
}


function mavnews_api_username_render(  ) { 

	$options = get_option( 'mavnews_settings' );
	?>
	<input type='text' name='mavnews_settings[mavnews_api_username]' value='<?php echo $options['mavnews_api_username']; ?>'>
	<?php

}


function mavnews_api_password_render(  ) { 

	$options = get_option( 'mavnews_settings' );
	?>
	<input type='password' name='mavnews_settings[mavnews_api_password]' value='    '>
	<?php

}


function mavnews_settings_section_callback(  ) { 

	echo __( 'Use your Mavnews credentials to authenticate with the API', 'wordpress' );

}


function mavnews_options_page(  ) { 

	?>
	<form action='options.php' method='post'>

		<h2>Mavnews</h2>

		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>

	</form>
	<?php

}

function mavnews_html() {
	$options = get_option( 'mavnews_settings' );
	$client = new Client([
		// Base URI is used with relative requests
		'base_uri' => 'http://mavnews.dailymaverick.co.za:5001/api/',
		// You can set any number of default request options.
		'timeout'  => 20.0,
		'auth' => [$options["mavnews_api_username"], $options["mavnews_api_password"]]
	]);
	$search = "";
	if ($_POST["s"]) {
		$search = "&search=" . $_POST["s"];
		$searchStr = $_POST["s"];
	}
	try {
		$response = $client->request('GET', 'article?limit=100&sort[date]=-1&fields=headline,date,keywords,provider' . $search);
		$body = $response->getBody();
		$contents = $body->getContents();
		$articles = json_decode($contents)->data;
		$count = json_decode($contents)->count;
		require_once("mavnews-articles.php");
	} catch(Exeption $error) {
		print "<h4>An error occured</h4>";
		print_r($exception);
	}
}

add_filter( 'default_content', 'my_editor_content' );
add_filter( 'default_title', 'my_editor_headline' );
add_filter( 'default_author', 'my_editor_author' );

function my_editor_content( $content ) {
	$options = get_option( 'mavnews_settings' );
	$client = new Client([
		// Base URI is used with relative requests
		'base_uri' => 'http://mavnews.dailymaverick.co.za:5001/api/',
		// You can set any number of default request options.
		'timeout'  => 2.0,
		'auth' => [$options["mavnews_api_username"], $options["mavnews_api_password"]]
	]);
	if (isset($_GET["mavnews-id"])) {
		$response = $client->request('GET', 'article/' . $_GET["mavnews-id"]);
		$body = $response->getBody();
		$contents = $body->getContents();
		$article = json_decode($contents);
		$body = $article->body;
		$lines = explode("\n", $body);
		$dateline = array_shift($lines);
		$copyright = array_pop($lines);
		$body = trim(implode("\n", $lines));
		$lines = explode("\n", $body);
		$editors = array_pop($lines);
		$body = trim(implode("\n", $lines));
		$lines = explode("\n", $body);
		$lines[sizeof($lines) - 1] = str_replace("</p>", ' <span style="text-decoration: underline;"><span class="s8"><b>DM</b></span></span></p>', $lines[sizeof($lines) - 1]);
		$body = trim(implode("\n", $lines));
		return $body;
	}
}

function my_editor_headline( $headline ) {
	$options = get_option( 'mavnews_settings' );
	$client = new Client([
		// Base URI is used with relative requests
		'base_uri' => 'http://mavnews.dailymaverick.co.za:5001/api/',
		// You can set any number of default request options.
		'timeout'  => 2.0,
		'auth' => [$options["mavnews_api_username"], $options["mavnews_api_password"]]
	]);
	if (isset($_GET["mavnews-id"])) {
		$response = $client->request('GET', 'article/' . $_GET["mavnews-id"]);
		$body = $response->getBody();
		$contents = $body->getContents();
		$article = json_decode($contents);
		// print_r($article);
		return $article->headline;
	}
}

function my_editor_author() {
	if (isset($_GET["mavnews-id"])) {
		$response = $client->request('GET', 'article/' . $_GET["mavnews-id"]);
		$body = $response->getBody();
		$contents = $body->getContents();
		$article = json_decode($contents);
		// print_r($article);
		return $AFP_ID;
	}
}