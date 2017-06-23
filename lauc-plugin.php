<?php
/*
Plugin Name: LAUC Extension
Version: 0.0.1
License: GPL v2 or later
*/

wp_enqueue_script('jquery-ui-autocomplete');
wp_enqueue_style('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css');

function search_keywords() {
  $term = strtolower($_GET['term']);
  $keywords = get_terms(array(
    'taxonomy' => 'keyword',
    'name__like' => $term
  ));
  $keywords = array_map(function ($k) { return $k->name; }, $keywords);
  echo json_encode($keywords);
  die();
}

add_action( 'wp_ajax_search_keywords', 'search_keywords' );
add_action( 'wp_ajax_nopriv_search_keywords', 'search_keywords' );

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
    if ( $title = $wp_query->get( 'search_title' ) ) {
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

    if (isset($params['description'])) {
      $params['description'] = str_replace('\"', '"', $params['description']);
    }

    $queryString = http_build_query($params);
    wp_redirect('/research/search-results?' . $queryString);
}

add_action( 'admin_post_search_form', 'et_custom_search' );
add_action( 'admin_post_nopriv_search_form', 'et_custom_search' );

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
