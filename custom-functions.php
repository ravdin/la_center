<?php

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

function et_custom_search() {
    $params = array();
    et_get_search_param('author', $params);
    et_get_search_param('title', $params);
    et_get_search_param('description', $params);
    et_get_search_param('region', $params);

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



$swp_query = new SWP_Query(
	array(
		's' => '',            // search query
		'engine' => 'entry_search_engine',      // engine to use
		'posts_per_page' => 10,     // posts per page
		'nopaging' => false,        // disable paging?
		'page' => 1,                // which page of results
		'tax_query' => array(       // tax_query support
		'meta_query' => array(      // meta_query support
			array(
				'key'     => 'age',
				'value'   => array( 3, 4 ),
				'compare' => 'IN',
			),
		),
	)
);

function search_results() {
  $fields = ['author', 'title', 'keywords', 'description', 'region', 'publication'];
  // TODO: Validate
  $page = isset($_GET['page']) ? $_GET['page'] : 1;
  $limit = 5;
  $where = array();
  $params = compact($page, $limit, $where);
  $result = '';

  $entries = pods('entry');
  $entries->find($params);
  if ($entries->total() > 0) {
    while ($entries->fetch()) {
      $year = $entries->display('year');
      $title = $entries->display('title');
      $source = $entries->display('source');
      $permalink = $entries->field('permalink');
      $authors_names = $entries->display('authors_names');
      $result .= "
       <div class=\"search-result-item\">
         <div class=\"entry-year\">
           {$year}
         </div>
         <div class=\"entry-details\">
           <div class=\"entry-title\">
             <a href=\"${permalink}\">{$title}</a>
           </div>
           <div>By&nbsp;{$authors_names}</div>
           <div>{$source}</div>
         </div>
       </div>";
    }
  }

  return $result;
}

add_shortcode('search_results', 'search_results')
?>
