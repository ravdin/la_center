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
     jQuery('#keywords').autocomplete({
       source: function(request, response) {
         var terms = request.term.split(/,\s+/);
         var term = terms[terms.length - 1];
         jQuery.ajax({
            url: '<?php echo esc_url( admin_url('admin-ajax.php?action=search_keywords') ); ?>',
            dataType: "json",
            data: {
                term: term,
            },
            success: response,
            error: function() {
                response([]);
            }
         });
       },
       select: function(e, ui) {
         var keyword = ui.item.value;
         var keywords = jQuery("#keywords").val().split(/,\s+/);
         keywords.pop();
         keywords.push(keyword);
         jQuery("#keywords").val(keywords.join(', ') + ', ');
         return false;
       },
       delay: 150,
       minLength: 3
     });
     jQuery("#tag_cloud a").click(function(e) {
       e.preventDefault();
       var link = jQuery(this);
       document.location.href = '/lauc/search-results?keywords[]=' + link.text();
       return false;
     })
   })
 </script>
<div class="et_pb_row">
    <h1>Urban Natural Resources Research Database</h1>
    <h3 style="color:#a4c954;">A curated database of scientific work on environmental, urban ecosystem, urban natural resources and socioeconomic topics relevant to the southern California region.</h3>
</div>
<div class="et_pb_row et_pb_row_3-4_1-4">
  <div class="et_pb_column et_pb_column_3_4">
    <h3>Search the database:</h3>
    <form action="<?php echo esc_url( admin_url('admin-ajax.php') ); ?>" method="POST">
      <input type="hidden" name="action" value="search_form">
      <div class="search_form">
        <div class="search_form_row">
          <span>Author last name</span>
          <input id="author" name="author" type="text" placeholder="McPherson" />
        </div>
        <div class="search_form_row">
          <span>Title</span>
          <input id="title" name="title" type="text" placeholder="Urban forestry in North America" />
        </div>
        <div class="search_form_row">
          <span>Keywords</span>
          <input id="keywords" name="keywords" type="text" placeholder="air quality" />
        </div>
        <div class="search_form_row">
          <span>Description</span>
          <input id="description" name="description" type="text" placeholder="Sacramento Greenprint" />
        </div>
        <div class="search_form_row">
          <span>Year</span>
          <input id="start" name="start" type="number" style="width:20%" placeholder="1984" />
          &nbsp;to&nbsp;
          <input id="end" name="end" type="number" style="width:20%" placeholder="2017" />
        </div>
        <div class="search_form_row">
          <span>Publication type</span>
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
  <div class="et_pb_column et_pb_column_1_4">
    <h3>New entries:</h3>
    <div>Do you have a suggestion for a database entry? Send us an <a href="mailto:info@laurbanresearchcenter.org">email</a> with the title and link.</div>
  </div>
</div>
<div id="tag_cloud" class="et_pb_row" style="padding-top:0">
  <h3>Keywords</h3>
  <?php wp_tag_cloud( array( 'taxonomy' => 'keyword' ) ); ?>
</div>
<?php get_footer();  ?>
