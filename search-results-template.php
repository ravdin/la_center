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

$author = get_field('author');
$title = get_field('title');
$description = get_field('description');
$region = get_field('region');
$start = get_field('start');
$end = get_field('end');
$keywords = isset($_GET['keywords']) ? $_GET['keywords'] : array();
$publication_type = isset($_GET['publication_type']) ? $_GET['publication_type'] : array();
// retrieve our pagination if applicable
$swppg = isset( $_REQUEST['swppg'] ) ? absint( $_REQUEST['swppg'] ) : 1;
$limit = 5;
$where = array();
$params = compact($page, $limit, $where);
$result = '';

$args = array(
  "post_type" => "entry",
  "post_status" => "publish",
  "orderby" => "title",
  "order" => "ASC",
  "posts_per_page" => 10,
  "custom_query" => true,
  "meta_query" => array()
);

add_to_query('authors_names', $author, 'LIKE', $args);
add_to_query('entry_title', $title, 'LIKE', $args);
add_to_query('description', $description, 'LIKE', $args);
add_to_query('geographic_region', $region, 'LIKE', $args);
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
			<header class="page-header">
				<h1 class="page-title">
					Research Database Search Results
				</h1>
        <h3>
          Your search returned <?= $total_count ?> result<?= $total_count == 1 ? '' : 's' ?>.
        </h3>
			</header><!-- .page-header -->
      <div class="et_pb_row et_pb_row_3-4_1-4">
        <div class="et_pb_column et_pb_column_1_4 et_pb_column_0 et_pb_column_single">
          <?php echo facetwp_display('facet', 'Year'); ?>
          <?php echo facetwp_display('facet', 'keywords'); ?>
          <?php echo facetwp_display('facet', 'publication_type'); ?>
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
                    <div><?= $entries->display('source') ?></div>
                  </div>
                </div>
            <?php endwhile; ?>
          </div>
        </div>
		</main><!-- .site-main -->
	</section><!-- .content-area -->

<?php
// clean up after the query and pagination
wp_reset_postdata();
?>
<?php get_footer(); ?>
