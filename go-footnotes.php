<?php
/**
 * Plugin Name: Footnotes
 * Plugin URI: https://github.com/janboddez/go-footnotes
 * Description: Surprisingly easily add footnotes to WordPress posts and pages.
 * Version: 0.1
 * Author: Jan Boddez
 * Author URI: https://janboddez.be/
 * License: GPL v2
 */

/**
 * 'Main' plugin class and settings.
 */
class GO_Footnotes {
	/**
	 * Registers all actions/hooks.
	 */
	public function __construct() {
		add_shortcode( 'footnote', array( $this, 'footnote_shortcode' ), 10, 2 );
		add_filter( 'the_content', array( $this, 'filter_content' ) );
	}

	/**
	 * Helps (properly) define the `footnote` shortcode. Will make it behave,
	 * e.g., in excerpts. Since @see GO_Footnotes::filter_content() is normally
	 * run before 'do_shortcode', returning an empty string is probably best.
	 *
	 * @param $atts Shortcode attributes. Not used.
	 * @param $content Content to be filtered. Or not filtered, here.
	 *
	 * @return string Empty string.
	 */
	public function footnote_shortcode( $atts, $content ) {
		return '';
	}

	/**
	 * Filters the whole post at once rather than running a filter 'per
	 * shortcode' in order to keep track of the number of notes and ease the
	 * footnote list creation.
	 *
	 * @param $content The content to be filtered.
	 *
	 * @return string Filtered content.
	 */
	public function filter_content( $content ) {
		if ( preg_match_all( '$\[footnote\](.*?)\[\/footnote\]$', $content, $matches ) ) { // Note: will also match empty 'footnotes'.
			// `$matches[0]` now holds the matched HTML, and `$matches[1]` the actual notes inside.
			$post_ID = get_the_ID(); // To guarantee unique IDs whenever the same page displays multiple posts in full.
			$count = 0;
			$output = '';

			foreach ( $matches[1] as $index => $footnote ) {
				// Find the first occurrence in the original text.
				if ( isset( $matches[0][$index] ) ) {
					$pos = strpos( $content, $matches[0][$index] );

					if ( false !== $pos ) {
						$footnote = trim( $footnote );

						if ( '' !== $footnote ) { // Disregard empty notes.
							$count++;
							// Replace (only) this occurrence with a link (to a newly created list item).
							$content = substr_replace( $content, '<sup><a href="#fn-' . $post_ID . '-' . $count . '" class="ref">' . $count . '</a></sup>', $pos, strlen( $matches[0][$index] ) );
							// Append a new list item to the output string.
							$output .= '<li id="fn-' . $post_ID . '-' . $count . '">' . $footnote . "</li>\n";
						} else {
							// Rather than 'wait' for the 'actual shortcode "parsing"', delete empty notes now.
							$content = substr_replace( $content, '', $pos, strlen( $matches[0][$index] ) );
						}
					}
				}
			}

			if ( ! empty ( $output ) ) {
				// Add those non-empty notes to the end of the post.
				$content .= '<ol class="footnotes">' . $output . "</ol>\n";
			}
		}

		// Always _return_ `$content`.
		return $content;
	}
}

new GO_Footnotes();
