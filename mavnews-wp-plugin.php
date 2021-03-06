<?php
/**
 * @package Mavnews_WP_Plugin
 * @version 0.2
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
}

function sanitize($input) {
	if (isset($input["password"])) {
		if (trim($input["password"]) == "") {
			delete($input["password"]);
		}
	}
	return $input;
}

function mavnews_html() {
	include_once("config.php");
	$client = new Client([
		// Base URI is used with relative requests
		'base_uri' => 'http://mavnews.dailymaverick.co.za:5001/api/',
		// You can set any number of default request options.
		'timeout'  => 20.0,
		'auth' => [$mavnews_options["mavnews_api_username"], $mavnews_options["mavnews_api_password"]]
	]);
	$search = "";
	if (isset($_POST["s"])) {
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
	include("config.php");
	$client = new Client([
		// Base URI is used with relative requests
		'base_uri' => 'http://mavnews.dailymaverick.co.za:5001/api/',
		// You can set any number of default request options.
		'timeout'  => 2.0,
		'auth' => [$mavnews_options["mavnews_api_username"], $mavnews_options["mavnews_api_password"]]
	]);
	if (isset($_GET["mavnews-id"])) {
		$response = $client->request('GET', 'article/' . $_GET["mavnews-id"]);
		$body = $response->getBody();
		$contents = $body->getContents();
		$article = json_decode($contents);
		$body = $article->body;
		$lines = explode("\n", $body);
		if ($article->provider !== "News24") {
			$dateline = array_shift($lines);
		}
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
	include("config.php");
	$client = new Client([
		// Base URI is used with relative requests
		'base_uri' => 'http://mavnews.dailymaverick.co.za:5001/api/',
		// You can set any number of default request options.
		'timeout'  => 2.0,
		'auth' => [$mavnews_options["mavnews_api_username"], $mavnews_options["mavnews_api_password"]]
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

function my_editor_excerpt( $excerpt ) {
	include("config.php");
	$client = new Client([
		// Base URI is used with relative requests
		'base_uri' => 'http://mavnews.dailymaverick.co.za:5001/api/',
		// You can set any number of default request options.
		'timeout'  => 2.0,
		'auth' => [$mavnews_options["mavnews_api_username"], $mavnews_options["mavnews_api_password"]]
	]);
	if (isset($_GET["mavnews-id"])) {
		$response = $client->request('GET', 'article/' . $_GET["mavnews-id"]);
		$body = $response->getBody();
		$contents = $body->getContents();
		$article = json_decode($contents);
		// print_r($article);
		return $article->blurb;
	}
}

function my_editor_author() {
	include_once("config.php");
	if (isset($_GET["mavnews-id"])) {
		$response = $client->request('GET', 'article/' . $_GET["mavnews-id"]);
		$body = $response->getBody();
		$contents = $body->getContents();
		$article = json_decode($contents);
		// print_r($article);
		return $AFP_ID;
	}
}