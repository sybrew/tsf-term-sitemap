<?php

defined( 'ABSPATH' ) or die;

$tsf    = the_seo_framework();
$locale = get_locale(); // For translation-plugin support.

$sitemap_bridge = \The_SEO_Framework\Bridges\Sitemap::get_instance();
$sitemap_bridge->output_sitemap_header();

$sitemap_bridge->output_sitemap_urlset_open_tag();

$sitemap_generated = false;
$sitemap_content   = $tsf->get_option( 'cache_sitemap' ) ? get_transient( "my_tsf_{$taxonomy}_sitemap_transient_{$locale}" ) : false;

if ( false === $sitemap_content ) {
	$sitemap_generated = true;

	include MY_TSF_CATEGORY_SITEMAP_BASE_DIR_PATH . 'classes' . DIRECTORY_SEPARATOR . 'sitemap-term.class.php';
	$sitemap_term = new My_TSF_Term_Sitemap;

	$sitemap_term->taxonomy = $taxonomy;
	$sitemap_term->prepare_generation();

	$sitemap_content = $sitemap_term->build_sitemap();

	$sitemap_term->shutdown_generation();
	$sitemap_term = null; // destroy class.

	/**
	 * Transient expiration: 1 week.
	 * Keep the sitemap for at most 1 week.
	 */
	$expiration = WEEK_IN_SECONDS;

	if ( $tsf->get_option( 'cache_sitemap' ) )
		set_transient( "my_tsf_{$taxonomy}_sitemap_transient_{$locale}", $sitemap_content, $expiration );
}
// phpcs:ignore, WordPress.Security.EscapeOutput
echo $sitemap_content;

$sitemap_bridge->output_sitemap_urlset_close_tag();

if ( $sitemap_generated ) {
	echo "\n" . '<!-- ' . \esc_html__( 'Sitemap is generated for this view', 'autodescription' ) . ' -->';
} else {
	echo "\n" . '<!-- ' . \esc_html__( 'Sitemap is served from cache', 'autodescription' ) . ' -->';
}
