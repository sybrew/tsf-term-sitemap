<?php
/**
 * Plugin Name: Term sitemap for The SEO Framework.
 * Description: This plugin adds a category sitemap to The SEO Framework. Other terms need to be hardcoded.
 * Version: 1.0.0
 * Author: Sybre Waaijer
 * Author URI: https://theseoframework.com/
 * License: GPLv3
 */

define( 'MY_TSF_CATEGORY_SITEMAP_BASE_DIR_PATH', __DIR__ . DIRECTORY_SEPARATOR );

add_filter( 'the_seo_framework_sitemap_endpoint_list', 'tsf_term_sitemap_adjust_list' );
function tsf_term_sitemap_adjust_list( $list ) {

	$taxonomies = [ 'category' ];

	foreach ( $taxonomies as $tax )
		$list[ $tax ] = [
			'endpoint' => "sitemap-$tax.xml",
			'regex'    => "/^sitemap\-{$tax}\.xml/", // Don't add a $ at the end, for translation-plugin support.
			'callback' => 'tsf_term_sitemap_output',
			'robots'   => true,
			'_args'    => [ // Prefix arbitrary arguments with an underscore for forward compatibility.
				'taxonomy' => $tax,
			],
		];

	return $list;
}

function tsf_term_sitemap_output( $sitemap_id ) {
	//* Remove output, if any.
	the_seo_framework()->clean_response_header();

	if ( ! headers_sent() ) {
		\status_header( 200 );
		header( 'Content-type: text/xml; charset=utf-8', true );
	}

	// Pass this to the view. $sitemap_id is what you set in $list before.
	$taxonomy = $sitemap_id;

	// Alternatively, use '\The_SEO_Framework\Bridges\Sitemap::get_instance()->get_sitemap_endpoint_list()[$sitemap_id]'.
	// It returns the arguments you've passed in filter 'the_seo_framework_sitemap_endpoint_list'; including arbitrary arguments.

	include MY_TSF_CATEGORY_SITEMAP_BASE_DIR_PATH . 'views' . DIRECTORY_SEPARATOR . 'term-xml.php';
	echo "\n";
	exit;
}
