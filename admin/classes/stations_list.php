<?php
// ------------------------------------------------------------------
/// @file admin/classes/obs_stations.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief Contains definition of the @ref Wetterturnier_Stations_List_Table.
/// @details Contains definition of the @ref Wetterturnier_Stations_List_Table
///   which is used in the wordpress admin backend.
// ------------------------------------------------------------------


class Wetterturnier_Stations_List_Table extends WP_List_Table {

   // Wordpress table list constructur
   function __construct() {
      parent::__construct( array(
         'singular'=> 'wetterturnier_stations_list', // Singular label
         'plural' => 'wetterturnier_stations_list',  // Plural label
         'ajax'   => false //We won't support Ajax for this table
      ) );
   }

   /// Define the columns that are going to be used in the table
   /// @return array $columns, the array of columns to use with the table
   function get_columns() {
      return $columns= array(
         'col_id'=>__('ID','wpwt'),
         'col_wmo'=>__('WMO','wpwt'),
         'col_name'=>__('Name','wpwt'),
         'col_activeparams'=>__('Active Param','wpwt'),
         'col_city'=>__('Linked to City','wpwt'),
         'col_changed'=>__('Added','wpwt')
      );
   }
    
   /// Decide which columns to activate the sorting functionality on
   /// @return array $sortable, the array of columns that can be sorted by the user
   public function get_sortable_columns() {
      return $sortable = array(
         "col_id"=>array("ID",false),
         "col_wmo"=>array("wmo",false),
         "col_name"=>array("name",false),
         "col_city"=>array("cityID",false),
         "col_changed"=>array("changed",true)
      );
   }
    
   /// Prepare the table with different parameters, pagination, columns and table elements
   function prepare_items() {

       global $wpdb, $_wp_column_headers, $WTadmin;
       $screen = get_current_screen();
   
       // Register the columns 
       $columns = $this->get_columns();
       $hidden = array();
       $sortable = $this->get_sortable_columns();
       $this->_column_headers = array($columns, $hidden, $sortable);

       // Fetch the items
       $this->items = $WTadmin->get_all_stationObj();

       // Pagination
       $perpage = 20;
       $this->set_pagination_args( array(
          "total_items" => count($this->items),
          "total_pages" => ceil(count($this->items)/$perpage),
          "per_page" => $perpage,
       ) );
       
   }
    
    
   ///  Display the rows of records in the table
   ///  @return string, echo the markup of the rows
   function display_rows() {

       global $WTadmin;
   
       //Get the records registered in the prepare_items method
       $records = $this->items;
   
       //Get the columns registered in the get_columns and get_sortable_columns methods
       list( $columns, $hidden ) = $this->get_column_info();

       //Getting all defined parameters
       $param = $WTadmin->get_param_names();
   
       //Loop for each record
       if(!empty($records)){foreach($records as $rec){
   
           //Open the line
           echo "<tr id=\"record_".$rec->get("ID")."\">\n";
           foreach ( $columns as $column_name => $column_display_name ) {
   
               //Style attributes for each col
               $class = "class='$column_name column-$column_name'";
               $style = "";
               if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
               $attributes = $class . $style;
               //$rec->changed = strftime('%Y-%m-%d %H:%i:%s',strtotime($rec->changed));

               // Create output in cell
               switch ( $column_name ) {
                   case "col_id":           echo "<td ".$attributes.">".stripslashes($rec->get("ID"))."</td>\n"; break;
                   case "col_wmo":          echo "<td ".$attributes.">".stripslashes($rec->get("wmo")).$this->add_actions($rec)."</td>\n"; break;
                   case "col_name":         echo "<td ".$attributes.">".stripslashes($rec->get("name"))."</td>\n";   break;
                   case "col_activeparams": echo "<td ".$attributes.">".$rec->showActiveParams()."</td>\n";   break;
                   case "col_city":         echo "<td ".$attributes.">".$this->show_info($rec)."</td>\n";   break;
                   case "col_changed":      echo "<td ".$attributes.">".$rec->get("changed")."</td>\n"; break;
               }
           }
   
          //Close the line
          echo "</tr>\n";
       }}
   }

   // Add the edit button to the group entries.
   function add_actions($item) {
       $actions = array(
           'edit'      => sprintf('<a href="?page=%s&action=%s&station=%d">Edit</a>',$_REQUEST['page'],'edit',$item->get("ID")),
           //'delete'    => sprintf('<a href="?page=%s&action=%s&station=%d">Delete</a>',$_REQUEST['page'],'delete',$item->ID),
       );
       return sprintf('%1$s', $this->row_actions($actions) );
   }

   // Show information
   function show_info($item) {

       global $wpdb;

       if ( ! $item->get("cityID") ) {
           return('Not in use');
       } else {
           $row = $wpdb->get_row(sprintf("SELECT * FROM %swetterturnier_cities "
                              ." WHERE ID = %d",$wpdb->prefix,$item->get("cityID")));
           return( $row->name );
       }

   }

}

?>
