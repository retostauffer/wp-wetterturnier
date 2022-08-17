<?php

global $wpdb;
global $WTadmin;

// If not allready available, load wordpress class-wp-list table first
if(!class_exists('WP_List_Table')){
   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
// Import personal list class
require_once( sprintf("%s/../classes/api_list.php",dirname(__FILE__)) );

// actionlink needed to send a few of the forms on this page
$actionlink =  sprintf('?page=%s',$_REQUEST['page']);

// Getting possible values for APITYPE
function get_enum_values( $table, $field )
{
   global $wpdb;
   $type = $wpdb->get_row("SHOW COLUMNS FROM {$table} WHERE Field = '{$field}'" )->Type;
   preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
   $enum = explode("','", $matches[1]);
   return $enum;
}
$enum_values = get_enum_values(sprintf("%swetterturnier_api",$wpdb->prefix),"APITYPE");
?>

<div class="wrap">


    <h2>API list</h2>
    <h3>Managing API calls</h3>
    Description missing!

    <h2>Add a new API Entry</h2>
    <form action="<?php print $actionlink; ?>" method=\"post\">
        <input type="hidden" name="page" value="<?php print $_REQUEST['page']; ?>" />
        <fd>API KEY (Unique!):</fd>
        <input type='text' name='APIKEY' maxlength='20' value='<?php print substr(md5((string)time()),0,20); ?>' /><br> 
        <fd>API Type:</fd>
        <select name='APITYPE'>
        <?php foreach( $enum_values AS $elem ) { ?>
            <option value='<?php print $elem; ?>'><?php print $elem; ?></option>
        <?php } ?>
        </select></br>
        <fd>Configuration:</fd>
        <input type='text' name='APICONFIG' /><br> 
        <fd>Is public:</fd>
        <input type="checkbox" name="ISPUBLIC" value="1"><br>
        <fd>Name:</fd>
        <input type='text' name='name' /><br> 
        <fd>Description:</fd>
        <textarea type='text' description='description'></textarea><br> 
        <fd>Until (can be empty):</fd>
        <input type="checkbox" name="useuntil" value="1">&nbsp;&nbsp;
        <input type="date" name="until" value="" /><br>

        <fd>&nbsp;</fd>
        <input type='submit' name='submit' class='button' value='Add API Entry'><br>
    </form>

   <h2>Overview</h2>

   <?php
   // doshow action
   if ( empty($_REQUEST['doshow']) ) { $_REQUEST['doshow'] = 'all'; } # default
   
   // prepare data/items with search string if set
   if ( empty($_REQUEST['s']) ) { $_REQUEST['s'] = NULL; }

   // Success message after changing something
   if ( ! empty($_GET['m']) && $_GET['m'] == 1 ) {
       echo "<div id='message' class='updated fade'><p><strong>"
           .__("Successfully changed the city entry","wpwt")
           ."</strong></p></div>";
   } 

   // Prepare table
   $list_api_table = new Wetterturnier_API_List_Table();
   $list_api_table->prepare_items();

   // Show table
   global $WTadmin;
   $link = sprintf("%s?page=%s",$WTadmin->curPageURL(true),$_REQUEST["page"]);
   ?>
   <h2 class="screen-reader-text">Filter API list</h2>
   <?php
      $call      = ($_REQUEST["doshow"] === "all"      ? "class='current'" : "");
      $cactive   = ($_REQUEST["doshow"] === "active"   ? "class='current'" : "");
      $cinactive = ($_REQUEST["doshow"] === "inactive" ? "class='current'" : "");
   ?>
   <ul class="subsubsub">
      <li class="all">
         <a href="<?php printf("%s&doshow=all",$link); ?>" <?php print $call; ?>>Show All </a>
         <span class="count"><?php printf("(%d)",$list_api_table->count_total); ?></span> |
      </li>
      <li class="active">
         <a href="<?php printf("%s&doshow=active",$link); ?>" <?php print $cactive; ?>>Show Active </a>
         <span class="count"><?php printf("(%d)",$list_api_table->count_active); ?></span> |
      </li>
      <li class="inactive">
         <a href="<?php printf("%s&doshow=inactive",$link); ?>" <?php print $cinactive; ?>>Show Inactive </a>
         <span class="count"><?php printf("(%d)",$list_api_table->count_inactive); ?></span>
      </li>
   </ul>
   
   <form action="<?php print $actionlink; ?>" method="get">
      <input type="hidden" name="page" value="<?php print $_REQUEST['page']; ?>" />
      <input type="hidden" name="doshow" value="<?php print $_REQUEST['doshow']; ?>" />
      <?php $list_api_table->search_box('search', 'name'); ?>
      <?php $list_api_table->display(); ?>
   </form>


</div>
