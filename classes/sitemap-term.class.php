<?php

defined( 'ABSPATH' ) or die;

class My_TSF_Term_Sitemap extends \The_SEO_Framework\Builders\Sitemap {

	public $taxonomy = '';

	public function build_sitemap() {

		$show_priority = static::$tsf->get_option( 'sitemaps_priority' );
		$show_modified = static::$tsf->get_option( 'sitemaps_modified' );

		// We want to get empty categories, so we can test for index-overide-states.
		$_args = [ 'hide_empty' => false ];
		$terms = \get_terms( $this->taxonomy, $_args );

		$_items      = array_column( $terms, 'term_id' );
		$total_items = count( $terms );

		// 49998 = 50000-2, max sitemap items.
		if ( $total_items > 49998 ) array_splice( $_items, 49998 );

		foreach ( $this->generate_url_item_values(
			$_items,
			compact( 'show_priority', 'show_modified', 'total_items' ),
			$count
		) as $_values ) {
			$content .= $this->build_url_item( $_values );
		}

		return $content;
	}

	/**
	 * @generator
	 */
	protected function generate_url_item_values( $term_ids, $args, &$count = 0 ) {

		foreach ( $term_ids as $term_id ) {
			if ( $this->is_term_included_in_sitemap( $term_id, $this->taxonomy ) ) {
				$_values = [];

				$_values['loc'] = static::$tsf->create_canonical_url(
					[
						'id'       => $term_id,
						'taxonomy' => $this->taxonomy,
					]
				);

				if ( $args['show_modified'] ) {
					// Yeah. So, good luck with this... This is the primary reason why we never implemented term-sitemaps in TSF.
					// $_values['lastmod'] = get_magic_number( $term_id, $this->taxonomy ) ?: '0000-00-00 00:00:00';
					$_values['lastmod'] = '0000-00-00 00:00:00';
				}

				if ( $args['show_priority'] ) {
					// Add at least 1 to prevent going negative. We add 9 to smoothen the slope.
					$_values['priority'] = .949999 - ( $count / ( $args['total_items'] + 9 ) );
				}

				++$count;
				yield $_values;
			}
		}
	}

	protected function build_url_item( $args ) {

		if ( empty( $args['loc'] ) ) return '';

		static $timestamp_format = null;

		$timestamp_format = $timestamp_format ?: static::$tsf->get_timestamp_format();

		return sprintf(
			"\t<url>\n%s\t</url>\n",
			vsprintf(
				'%s%s%s',
				[
					sprintf(
						"\t\t<loc>%s</loc>\n",
						$args['loc'] // Already escaped.
					),
					isset( $args['lastmod'] ) && '0000-00-00 00:00:00' !== $args['lastmod']
						? sprintf( "\t\t<lastmod>%s</lastmod>\n", static::$tsf->gmt2date( $timestamp_format, $args['lastmod'] ) )
						: '',
					isset( $args['priority'] ) && is_numeric( $args['priority'] )
						? sprintf( "\t\t<priority>%s</priority>\n", number_format( $args['priority'], 1, '.', ',' ) )
						: '',
				]
			)
		);
	}
}
