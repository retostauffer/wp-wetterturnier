<?php
// -------------------------------------------------------------------
// - NAME:        linkcollection.php
// - AUTHOR:      Reto Stauffer
// - DATE:        2016-01-05
// -------------------------------------------------------------------
// - DESCRIPTION: Loading all link categories and displays them.
//                Can be included via shortcode:
//                [wetterturnier_linkcollection]
// -------------------------------------------------------------------
// - EDITORIAL:   2016-01-05, RS: Created file on thinkreto.
// -------------------------------------------------------------------
// - L@ST MODIFIED: 2014-09-13 15:12 on thinkreto
// -------------------------------------------------------------------
?>

<style>
   ul.wt-linkcollection name {
      display: inline-block;
      min-width: 300px;
   }
   ul.wt-linkcollection desc {

   }
</style>

<?php
// Loading linkcategories
$taxonomy = 'link_category';
$title = 'Link Category: ';
$args = array('orderby'=>'name','order'=>'ASC','hide_empty'=>1);
$terms = get_terms( $taxonomy, $args );

// If there were no terms:
if ( ! $terms ) {
   _e("Sorry, no links available at the moment.","wpwt");
} else {
   //// Looping over all entries, show hyperlinks first (to jump to
   //// a certain category) before the links itself will be shown.
   //foreach ( $terms as $rec ) {
   //   printf("<a href=\"#category-%d\"><input type=\"button\" value=\"%s\" /></a>\n",
   //          $rec->term_id,$rec->name);
   //}

   // Then show the different categories as list
   foreach ( $terms as $rec ) {
      printf("<a name=\"category-%d\"></a><h3>%s</h3>\n",$rec->term_id,$rec->name);
      // Loading corresponding links
      $bargs = array('orderby'=>'name','order'=>'ASC','category'=>$rec->term_id);
      $links = get_bookmarks($bargs);
      // If there are no links available
      if ( ! $links ) {
         print(__("Sorry, currently no links in this category ...","wpwt")."<br>\n");
      // Else show links as a list
      } else {
         printf("  <ul class=\"wt-linkcollection\">\n");
         foreach ( $links as $link ) {
            printf("    <li><name>%s:</name> <desc>%s</desc></li>\n",
                   sprintf("<a href=\"%s\" target=\"_blank\">%s</a>",$link->link_url,$link->link_name),
                   $link->link_description);
         }
         printf("  </ul>\n");
      }
   }
}





