<?php

// Allow FacetWP to work with the WP query.
add_filter( 'facetwp_is_main_query', function( $is_main_query, $query ) {
	if ( isset( $query->query['custom_query'] ) ) {
		$is_main_query = true;
	}
	return $is_main_query;
}, 10, 2 );

function et_get_search_param($name, &$params) {
  if (!empty($_POST[$name])) {
    $params[$name] = sanitize_text_field($_POST[$name]);
  }
}

// Apply LIKE compare to title searches.
add_filter( 'posts_where', 'post_title_where', 10, 2 );
function post_title_where( $where, &$wp_query )
{
    global $wpdb;
    if ( $title = $wp_query->get( 'title' ) ) {
        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . $wpdb->esc_like( $title ) . '%\'';
    }
    return $where;
}

// Sort the Year facet in descending order.
add_filter( 'facetwp_facet_orderby', function( $orderby, $facet ) {
    if ( 'Year' == $facet['name'] ) {
        $orderby = 'f.facet_display_value+0 DESC';
    }
    return $orderby;
}, 10, 2 );

// Process the POST parameters from the search and redirect.
function et_custom_search() {
    $params = array();
    et_get_search_param('author', $params);
    et_get_search_param('title', $params);
    et_get_search_param('description', $params);
    et_get_search_param('start', $params);
    et_get_search_param('end', $params);

    if (!empty($_POST['keywords'])) {
      $params['keywords'] = array_map(sanitize_text_field, explode(',', $_POST['keywords']));
    }

    if (!empty($_POST['publication_type'])) {
      $params['publication_type'] = $_POST['publication_type'];
    }

    $queryString = http_build_query($params);
    wp_redirect('/search-results?' . $queryString);
}

add_action( 'admin_post_search_form', 'et_custom_search' );

/**
 * Extend WordPress search to include custom fields
 *
 * http://adambalee.com
 */

/**
 * Join posts and postmeta tables
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_join
 */
function cf_search_join( $join ) {
    global $wpdb;

    if ( is_search() ) {
        $join .=' LEFT JOIN '.$wpdb->postmeta. ' ON '. $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
    }

    return $join;
}
add_filter('posts_join', 'cf_search_join' );

/**
 * Modify the search query with posts_where
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_where
 */
function cf_search_where( $where ) {
    global $wpdb;

    if ( is_search() ) {
        $where = preg_replace(
            "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "(".$wpdb->posts.".post_title LIKE $1) OR (".$wpdb->postmeta.".meta_value LIKE $1)", $where );
    }

    return $where;
}
add_filter( 'posts_where', 'cf_search_where' );

/**
 * Prevent duplicates
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_distinct
 */
function cf_search_distinct( $where ) {
    global $wpdb;

    if ( is_search() ) {
        return "DISTINCT";
    }

    return $where;
}
add_filter( 'posts_distinct', 'cf_search_distinct' );

?>
