<?php

/**
 * WordPress "More Like This" Query class
 * 
 * @link https://github.com/rcoll/WP_MLT_Query
 */
class WP_MLT_Query {
	/**
	 * Holder array for query results
	 *
	 * @access public
	 *
	 * @var array
	 */
	var $results = array();
	/**
	 * Get an array of words from a string. Parses out HTML tags and shortcodes, removes punctuation, 
	 * removes line breaks, removes whitespace, lowercase all words, removes stop words, and removes
	 * words that are less than 3 characters.
	 *
	 * @access private
	 *
	 * @param string $text String of text to parse
	 *
	 * @uses strip_shortcodes()
	 * @uses apply_filters()
	 * 
	 * @return array Sanitized words
	 */
	private function get_sanitized_word_array( $text ) {
		// Strip html tags and shortcodes
		$text = strip_tags( $text );
		$text = strip_shortcodes( $text );
		// Remove anything that's not a letter or number
		$text = preg_replace( '/[^a-zA-Z 0-9]+/', ' ', $text );
		// Remove line breaks
		$text = str_replace( "\n", '', $text );
		$text = str_replace( "\r", '', $text );
		// Replace multiple spaces with a single space
		$text = preg_replace( '!\s+!', ' ', $text );
		// Create an array of words
		$words = explode( ' ', $text );
		// Lowercase and trim the words
		$words = array_map( 'strtolower', $words );
		$words = array_map( 'trim', $words );
		// Define our stop words
		$stopwords = apply_filters( 'mlt_stop_words', array( 'a', 'just', 'nbsp', 'about', 'above', 'above', 'across', 'after', 'afterwards', 'again', 'against', 'all', 'almost', 'alone', 'along', 'already', 'also','although','always','am','among', 'amongst', 'amoungst', 'amount', 'an', 'and', 'another', 'any','anyhow','anyone','anything','anyway', 'anywhere', 'are', 'around', 'as', 'at', 'back','be','became', 'because','become','becomes', 'becoming', 'been', 'before', 'beforehand', 'behind', 'being', 'below', 'beside', 'besides', 'between', 'beyond', 'bill', 'both', 'bottom','but', 'by', 'call', 'can', 'cannot', 'cant', 'co', 'con', 'could', 'couldnt', 'cry', 'de', 'describe', 'detail', 'do', 'done', 'down', 'due', 'during', 'each', 'eg', 'eight', 'either', 'eleven','else', 'elsewhere', 'empty', 'enough', 'etc', 'even', 'ever', 'every', 'everyone', 'everything', 'everywhere', 'except', 'few', 'fifteen', 'fify', 'fill', 'find', 'fire', 'first', 'five', 'for', 'former', 'formerly', 'forty', 'found', 'four', 'from', 'front', 'full', 'further', 'get', 'give', 'go', 'had', 'has', 'hasnt', 'have', 'he', 'hence', 'her', 'here', 'hereafter', 'hereby', 'herein', 'hereupon', 'hers', 'herself', 'him', 'himself', 'his', 'how', 'however', 'hundred', 'ie', 'if', 'in', 'inc', 'indeed', 'interest', 'into', 'is', 'it', 'its', 'itself', 'keep', 'last', 'latter', 'latterly', 'least', 'less', 'ltd', 'made', 'many', 'may', 'me', 'meanwhile', 'might', 'mill', 'mine', 'more', 'moreover', 'most', 'mostly', 'move', 'much', 'must', 'my', 'myself', 'name', 'namely', 'neither', 'never', 'nevertheless', 'next', 'nine', 'no', 'nobody', 'none', 'noone', 'nor', 'not', 'nothing', 'now', 'nowhere', 'of', 'off', 'often', 'on', 'once', 'one', 'only', 'onto', 'or', 'other', 'others', 'otherwise', 'our', 'ours', 'ourselves', 'out', 'over', 'own','part', 'per', 'perhaps', 'please', 'put', 'rather', 're', 'same', 'see', 'seem', 'seemed', 'seeming', 'seems', 'serious', 'several', 'she', 'should', 'show', 'side', 'since', 'sincere', 'six', 'sixty', 'so', 'some', 'somehow', 'someone', 'something', 'sometime', 'sometimes', 'somewhere', 'still', 'such', 'system', 'take', 'ten', 'than', 'that', 'the', 'their', 'them', 'themselves', 'then', 'thence', 'there', 'thereafter', 'thereby', 'therefore', 'therein', 'thereupon', 'these', 'they', 'thickv', 'thin', 'third', 'this', 'those', 'though', 'three', 'through', 'throughout', 'thru', 'thus', 'to', 'together', 'too', 'top', 'toward', 'towards', 'twelve', 'twenty', 'two', 'un', 'under', 'until', 'up', 'upon', 'us', 'very', 'via', 'was', 'we', 'well', 'were', 'what', 'whatever', 'when', 'whence', 'whenever', 'where', 'whereafter', 'whereas', 'whereby', 'wherein', 'whereupon', 'wherever', 'whether', 'which', 'while', 'whither', 'who', 'whoever', 'whole', 'whom', 'whose', 'why', 'will', 'with', 'within', 'without', 'would', 'yet', 'you', 'your', 'yours', 'yourself', 'yourselves', 'the' ) );
		// Remove stop words from our words array
		$words = preg_replace( '/\b(' . implode( '|', $stopwords ) . ')\b/', '', $words );
		// Remove words that are less than 3 characters
		$words = array_filter( $words, function( $word ) {
			return strlen( $word ) > 2;
		});
		// Return the results
		return $words;
	}
	/**
	 * Get a single keyword from a string or array of sanitized words.
	 *
	 * @access private
	 *
	 * @param string|array $words Array or string of sanitized words
	 * 
	 * @return string A single keyword
	 */
	private function get_keyword( $words ) {
		// Make a string from array if array is provided
		if ( is_array( $words ) )
			$words = implode( ' ', $words );
		// Get an array of all words contained in the string
		$words = str_word_count( $words, 1 );
		// Get the count of all words
		$total_words = count( $words );
		// Count all the values in the array and sort by values
		$word_count = array_count_values( $words );
		arsort( $word_count );
		// Holder for parsed words
		$new_words = array();
		// Loop through the words and score each into a percentage of word density
		foreach ( $word_count as $key => $value ) {
			$new_words[$key] = number_format( ( $value / $total_words ) * 100 );
		}
		// Pop the first word off the array
		reset( $new_words );
		$first_key = key( $new_words );
		// And return it
		return $first_key;
	}
	/**
	 * Constructor.
	 *
	 * Sets up and runs the query and stores the results into the $results property.
	 *
	 * @access public
	 *
	 * @param array $args Argument array
	 *
	 * @global $post
	 *
	 * @uses absint()
	 * @uses wp_parse_args()
	 * @uses get_option()
	 * @uses get_post()
	 * @uses wp_get_post_categories()
	 * @uses wp_get_post_tags()
	 * @uses get_posts()
	 * @uses WP_Query
	 */
	function __construct( $args = array() ) {
		global $post;
		// Get the current global post ID
		$mlt_post_id = absint( $post->ID );
		// Parse in our default arguments
		$args = wp_parse_args( $args, array( 
			'posts_per_page' => get_option( 'posts_per_page' ), 
			'p' => absint( $mlt_post_id ), 
			'fields' => 'ids', 
		));
		// Formulate cache key
		$cache_key = 'mltq_' . md5( serialize( $args ) );
		// Try to get results from the cache
		$posts = wp_cache_get( $cache_key, 'mlt' );
		// If we have cache, use it and return
		if ( $posts ) {
			$this->results = $posts;
			return;
		}
		
		// Get the post object to compare
		$mlt_post = get_post( $args['p'] );
		
		// Get the posts categories, tags, and words
		$categories = wp_get_post_categories( $mlt_post_id, array( 'fields' => 'ids' ) );
		$tags = wp_get_post_tags( $mlt_post_id, array( 'fields' => 'ids' ) );
		$words = $this->get_sanitized_word_array( $mlt_post->post_content );
		// Get the first keyword for this article
		$keyword = $this->get_keyword( $words );
		// Primary category
		$category = $categories[0];
		// Primary tag
		$tag = $tags[0];
		// Get posts with our calculated arguments
		$posts = get_posts( array( 
			'posts_per_page' => ( $args['posts_per_page'] + 1 ), 
			's' => $keyword, 
			'cat' => $category, 
			'fields' => 'ids', 
		));
		
		// Remove the compared post if present
		if ( in_array( $mlt_post_id, $posts ) ) {
			$idx = array_search( $mlt_post_id, $posts );
			unset( $posts[$idx] );
		} else { 
			unset( $posts[count($posts) - 1] );
		}
		
		// Set the result in the requested format
		if ( 'ids' == $args['fields'] ) {
			$this->results = $posts;
		} elseif ( 'all' == $args['fields'] ) {
			$this->results = new WP_Query( array( 'post__in' => $posts, 'orderby' => 'post__in' ) );
		} else {
			$this->results = $posts;
		}

		// Save the results in the cache for future use
		wp_cache_set( $cache_key, $posts, 'mlt', 3600 );
	}
}

// omit