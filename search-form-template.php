<?php
/* Template Name: Search Form */

$pods = pods('entry');
$types = $pods->fields('publication_type');
$types = explode("\n", $types['options']['pick_custom']);
get_header();
 ?>

 <script type="text/javascript">
   function unique(arr) {
     var n={},r=[]
     for (var i = 0; i < arr.length; i++) {
       if (!n[arr[i]]) {
         n[arr[i]] = true;
         r.push(arr[i]);
       }
     }
     return r;
   }
   jQuery(document).ready(function() {
     jQuery("#tag_cloud a").click(function(e) {
       e.preventDefault();
       var link = jQuery(this);
       var keywords = jQuery("#keywords").val().split(/,\s+/);
       keywords = keywords.filter(function(x) { return x.length > 0; })
       keywords.push(link.text());
       keywords = unique(keywords);
       jQuery("#keywords").val(keywords.join(', '));
       return false;
     })
   })
 </script>

  <div class="et_pb_row et_pb_row_3-4_1-4">
  <div class="et_pb_column et_pb_column_3_4">
    <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
      <input type="hidden" name="action" value="search_form">
      <div class="search_form">
        <div class="search_form_row">
          <span>Author last name</span>
          <input id="author" name="author" type="text" />
        </div>
        <div class="search_form_row">
          <span>Title</span>
          <input id="title" name="title" type="text" />
        </div>
        <div class="search_form_row">
          <span>Keywords</span>
          <input id="keywords" name="keywords" type="text" />
        </div>
        <div class="search_form_row">
          <span>Description</span>
          <input id="description" name="description" type="text" />
        </div>
        <div class="search_form_row">
          <span>Geographic region</span>
          <input id="region" name="region" type="text" />
        </div>
        <div class="search_form_row">
          <span>Publication Type</span>
          <div class="search_checkboxes">
            <ul>
              <?php foreach ($types as $type_display):
                  $type_val = str_replace(' ', '_', strtolower($type_display)); ?>
                  <li><input id="cb_<?= $type_val ?>" name="publication_type[]" value="<?= $type_display ?>" type="checkbox" /><span><?= $type_display ?></span></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
        <div class="search_form_row">
          <input type="submit" class="et_pb_button  et_pb_module et_pb_bg_layout_light" value="Search">
        </div>
      </div>
    </form>
  </div>
  <div id="tag_cloud" class="et_pb_column et_pb_column_1_4">
    <?php wp_tag_cloud( array( 'taxonomy' => 'keyword' ) ); ?>
  </div>
</div>
<?php get_footer();  ?>
