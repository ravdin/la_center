<?php
/* Template Name: Search Results */
// retrieve our search query if applicable
function get_field($param) {
  return isset($_GET[$param]) ? sanitize_text_field($_GET[$param]) : '';
}

function add_to_query($key, $value, $compare, &$args) {
  if (!empty($value)) {
    $args['meta_query'][] = compact('key', 'value', 'compare');
  }
}

function add_description_to_query($description, &$args) {
  if (empty($description)) {
    return;
  }
  $query = array('relation' => 'AND');
  $words = preg_split('/\s+/', $description, -1, PREG_SPLIT_NO_EMPTY);
  foreach ($words as $word) {
    $query[] = array('key' => 'description', 'value' => $word, 'compare' => 'LIKE');
  }
  $args['meta_query'][] = $query;
}

function get_sort_link($field, $display, $orderby, $order) {
  $query_data = $_GET;
  $query_data['orderby'] = $field;
  $query_data['order'] = 'ASC';
  if ($field === $orderby) {
    $query_data['order'] = $order === 'ASC' ? 'DESC' : 'ASC';
  }
  $qs = http_build_query($query_data);
  $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  return "<a href=\"$url?$qs\">$display</a>";
}

$author = get_field('author');
$title = get_field('title');
$description = get_field('description');
$start = get_field('start');
$end = get_field('end');
$orderby = get_field('orderby');
$order = get_field('order');
$keywords = isset($_GET['keywords']) ? $_GET['keywords'] : array();
$publication_type = isset($_GET['publication_type']) ? $_GET['publication_type'] : array();

$orderby_fields = array(
  'post_title' => array(
    'orderby' => 'post_title'
  ),
  'year' => array(
    'orderby' => 'meta_value',
    'meta_key' => 'year'
  )
);
$order_fields = array('ASC', 'DESC');

if (!in_array($order, $order_fields)) {
  $order = 'ASC';
}

$args = array(
  "post_type" => "entry",
  "post_status" => "publish",
  "posts_per_page" => 20,
  "order" => $order,
  "custom_query" => true,
  "meta_query" => array()
);

if (!empty($title)) {
  $args['search_title'] = $title;
}

if (!isset($orderby_fields[$orderby])) {
  $orderby = 'post_title';
}

$args = array_merge($args, $orderby_fields[$orderby]);

add_to_query('authors_names', $author, 'LIKE', $args);
add_description_to_query($description, $args);
add_to_query('year', $start, '>=', $args);
add_to_query('year', $end, '<=', $args);

if (!empty($keywords)) {
  if (!is_array($keywords)) {
    $keywords = explode(',', $keywords);
  }
  $keywords = array_map('sanitize_text_field', $keywords);
  $args['tax_query'] = array(
    array(
      'taxonomy' => 'keyword',
      'field' => 'slug',
      'terms' => $keywords
    )
  );
}

if (!empty($publication_type)) {
  if (!is_array($publication_type)) {
    $publication_type = explode(',', $publication_type);
  }
  $publication_type = array_map('sanitize_text_field', $publication_type);
  add_to_query('publication_type', $publication_type, 'IN', $args);
}

$entries = pods('entry');
$the_query = new WP_Query($args);
$total_count = $the_query->found_posts;

get_header(); ?>
	<section id="primary" class="et_pb_section et_pb_fullwidth_section">
		<main id="main" class="site-main" role="main">
      <?php if ($total_count == 0): ?>
  			<header class="page-header">
  				<h1 class="page-title">
  					Research Database Search Results
  				</h1>
        </header>
        <div class="et_pb_row">
          <p>Sorry, no results were found.</p>
          <p>Search suggestions:</p>
          <ul style="list-style-type:disc; margin: 20px;">
             <li>Double check your spelling.</li>
             <li>Enter fewer search parameters. You’ll have a chance to refine further on the Search Results page.</li>
             <li>Try different wording, e.g., CO2 --> carbon or carbon dioxide; air pollutant --> air pollution</li>
             <li>Try your search in the Description field.</li>
          </ul>
        </div>
      <?php else: ?>
  			<header class="page-header">
  				<h1 class="page-title">
  					Research Database Search Results
  				</h1>
          <h3>
            Your search returned <?= $total_count ?> result<?= $total_count == 1 ? '' : 's' ?>.
          </h3>
  			</header><!-- .page-header -->
        <div class="et_pb_row">
          Sort by: <?= get_sort_link('post_title', 'Title', $orderby, $order) ?> <?= get_sort_link('year', 'Year', $orderby, $order) ?>
        </div>
        <div class="et_pb_row et_pb_row_3-4_1-4">
          <div class="et_pb_column et_pb_column_1_4 et_pb_column_0 et_pb_column_single">
            <h3>Refine results</h3>
            <h5>Year</h5>
            <?php echo facetwp_display('facet', 'Year'); ?>
            <h5>Publication type</h5>
            <?php echo facetwp_display('facet', 'publication_type'); ?>
            <h5>Keywords</h5>
            <?php echo facetwp_display('facet', 'keywords'); ?>
          </div>
          <div class="et_pb_column et_pb_column_3_4 et_pb_column_1 et_pb_column_single facetwp-template">
            <?php while ( $the_query->have_posts() ) :
              $the_query->the_post();
              $entries->fetch(get_the_ID()); ?>
                  <div class="search-result-item">
                    <div class="entry-year">
                      <?= $entries->display('year') ?>
                    </div>
                    <div class="entry-details">
                      <div class="entry-title">
                        <a href="<?= $entries->display('permalink') ?>"><?= $entries->display('title') ?></a>
                      </div>
                      <div>By&nbsp;<?= $entries->display('authors_names') ?></div>
                      <div class="visible-xs"><?= $entries->display('year') ?></div>
                      <div><?= $entries->display('source') ?></div>
                    </div>
                  </div>
              <?php endwhile; ?>
              <?php echo facetwp_display( 'pager' ); ?>
            </div>
          </div>
        <?php endif; ?>
		</main><!-- .site-main -->
	</section><!-- .content-area -->

<?php
// clean up after the query and pagination
wp_reset_postdata();
?>
<?php get_footer(); ?>
