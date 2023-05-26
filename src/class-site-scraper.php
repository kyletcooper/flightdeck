<?php
/**
 * Contains the Site_Scraper class.
 *
 * @package flightdeck
 */

namespace flightdeck;

use DOMDocument, DOMXPath, DOMNodeList, DOMNode;

/**
 * Scrapes a URL and parses it.
 */
class Site_Scraper {
	/**
	 * URL to scrape.
	 *
	 * @var string $url
	 */
	public $url;

	/**
	 * The DOM Document. Empty when the object is created.
	 *
	 * @var DOMDocument $doc
	 */
	private $doc;

	/**
	 * If the site has been fetched correctly.
	 *
	 * @var bool $retrieved
	 */
	private $retrieved = false;

	/**
	 * Creates the Site_Scraper class.
	 *
	 * @param string $url The URL to scrape.
	 */
	public function __construct( $url ) {
		$this->url                      = $url;
		$this->doc                      = new DOMDocument();
		$this->doc->strictErrorChecking = false; // phpcs:ignore -- Not my class.
	}

	/**
	 * Retrieves the content from the URL and loads it into the document parser.
	 */
	public function retrieve() {
		try {
			libxml_use_internal_errors( true );
			$contents = @file_get_contents( $this->url ); // phpcs:ignore

			if ( $contents ) {
				$this->doc->loadHTML( $contents );
				$this->retrieved = true;
			}
		} catch ( \Exception $e ) {
			// Nothing we can really do.
		}
	}

	/**
	 * Runs an xpath on the document.
	 *
	 * Document must be loaded with ->retrieve() first.
	 *
	 * @param string $query The xpath query.
	 *
	 * @return DOMNodeList|false The DOMNodeList of matches or false.
	 */
	public function xpath( $query ) {
		if ( ! $this->retrieved ) {
			return false;
		}

		$xpath = new DOMXPath( $this->doc );
		return $xpath->query( $query );
	}

	/**
	 * Retrieves the URL for the thumbnail of a site.
	 *
	 * Trys the og:image, then gets any image, then falls back to an empty string.
	 *
	 * This value is cached per address URL.
	 *
	 * @return string The URL of the thumbnail image.
	 */
	public function get_thumbnail_image_url() {
		$possible_thumbnails = $this->xpath( '//meta[@property="og:image"]' );

		if ( $possible_thumbnails && count( $possible_thumbnails ) ) {
			return $possible_thumbnails[0]->getAttribute( 'content' );
		}

		$possible_thumbnails = $this->xpath( '//img[not(contains(@src, ".svg"))]' );

		if ( $possible_thumbnails && count( $possible_thumbnails ) ) {
			return $possible_thumbnails[0]->getAttribute( 'src' );
		}

		return '';
	}

	/**
	 * Retrieves the URL for the page's favicon.
	 *
	 * This value is cached per address URL.
	 *
	 * @return string The URL of the favicon.
	 */
	public function get_favicon_url() {
		$possible_favicons = $this->xpath( '//link[contains(@rel, "icon")]' );

		if ( $possible_favicons && count( $possible_favicons ) ) {
			return $possible_favicons[0]->getAttribute( 'href' );
		}

		return $this->url . '/favicon.ico';
	}
}
