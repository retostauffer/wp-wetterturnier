<?php
// ------------------------------------------------------------------
/// @file admin/classes/cities_list.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief Contains definition of the @ref Wetterturnier_Cities_List_Table.
/// @details Contains definition of the @ref Wetterturnier_Cities_List_Table
///   which is used in the wordpress admin backend.
// ------------------------------------------------------------------


class Wetterturnier_Cities_List_Table extends WP_List_Table {


   // Wordpress table list constructur
   function __construct() {
      parent::__construct( array(
         'singular'=> 'wetterturnier_cities_list', // Singular label
         'plural' => 'wetterturnier_cities_list',  // Plural label
         'ajax'   => false //We won't support Ajax for this table
      ) );
   }


   /// Define the columns that are going to be used in the table
   /// @return array $columns, the array of columns to use with the table
   function get_columns() {
      return $columns= array(
         'col_id'=>__('ID','wpwt'),
         'col_sort'=>__('Sort','wpwt'),
         'col_hash'=>__('City Hash','wpwt'),
         'col_name'=>__('Name','wpwt'),
         'col_paramconfig'=>__('Parameter','wpwt'),
         'col_stations'=>__('Active Stations','wpwt'),
         'col_status'=>__('City Status','wpwt'),
      );
   }
   
   /// Decide which columns to activate the sorting functionality on
   /// @return array $sortable, the array of columns that can be sorted by the user
   public function get_sortable_columns() {
      return $sortable = array(
         "col_id"=>array("ID",false),
         "col_sort"=>array("sort",false),
         "col_hash"=>array("hash",false),
         "col_name"=>array("name",false),
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

       // Loading all city objects from the system
       $this->items = $WTadmin->get_all_cityObj();

       /* -- Register the pagination -- */
       $perpage = 100;
       $this->set_pagination_args( array(
          "total_items" => count($this->items),
          "total_pages" => ceil(count($this->items)/$perpage),
          "per_page" => $perpage,
       ) );

   }
    
    
   // Display the rows of records in the table
   // @return string, echo the markup of the rows
   function display_rows() {

       global $WTadmin;

       // Get the columns registered in the get_columns and get_sortable_columns methods
       list( $columns, $hidden ) = $this->get_column_info();
       $columns = $this->get_columns();
   
       //Loop for each record
       foreach ( $this->items as $cityObj ) {
   
           // String to show whether city is active or not.
           $until   = $cityObj->get("active") ? "City is active" :
               __('Inactive since','wpwt').": ".strftime('%ormat the status outputY-%m-%d',strtotime($cityObj->get("until")));
           // City created
           $created = __('City created','wpwt').": ".strftime('%Y-%m-%d',strtotime($cityObj->get("since")));
   
           //Open the line
           echo "<tr id=\"record_".$cityObj->get("ID")."\" class=\"row-active-".$cityObj->get("active")."\">\n";
           foreach ( $columns as $column_name => $column_display_name ) {
   
               //Style attributes for each col
               $class = "class='$column_name column-$column_name'";
               $style = "";
               #if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
               $attributes = $class . $style;
            
               // Create output in cell
               switch ( $column_name ) {
                   case "col_id":          echo "<td ".$attributes.">".stripslashes($cityObj->get("ID"))."</td>\n"; break;
                   case "col_sort":        echo "<td ".$attributes.">".stripslashes($cityObj->get("sort"))."</td>\n"; break;
                   case "col_hash":        echo "<td ".$attributes.">".stripslashes($cityObj->get("hash"))."</td>\n";   break;
                   case "col_name":        echo "<td ".$attributes.">".$this->add_actions($cityObj)."</td>\n";   break;
                   case "col_paramconfig": echo "<td ".$attributes.">".$this->show_params($cityObj)."</td>\n";   break;
                   case "col_stations":    echo "<td ".$attributes.">".$this->show_stations($cityObj)."</td>\n";   break;
                   case "col_status":      echo "<td ".$attributes.">".$created."<br>".$until."</td>\n"; break;
               }
           }
   
          //Close the line
          echo "</tr>\n";
       }
   }

   // Show station information
   function show_stations( $cityObj ) {
        global $wpdb;
        if ( count($cityObj->stations()) == 0 ) {
           return('No station set');
        } else {
           $res =array();
           foreach ( $cityObj->stations() as $stnObj ) {
              array_push($res,sprintf("%d: %s",$stnObj->get("wmo"),$stnObj->get("name"))); 
           }
           return( implode("<br>",$res) );
        }
   }

   // Show parameter
   function show_params( $cityObj ) {
      $res = array();
      foreach ( $cityObj->stations() as $stnObj ) {
         array_push($res,sprintf("%s\n",$stnObj->showActiveParams()));
      }
      return( implode("<br>",$res) );
   }

   // Add the edit button to the group entries.
   function add_actions( $cityObj ) {
       if ( $cityObj->get("active") == 1 ) {
           $actions = array(
              'edit'   => sprintf('<a href="?page=%s&action=%s&city=%d">Edit</a>',
                          $_REQUEST['page'],'edit',$cityObj->get("ID")),
              'delete' => sprintf('<a href="?page=%s&action=%s&city=%d">Delete</a>',
                          $_REQUEST['page'],'delete',$cityObj->get("ID")),
           );
           return sprintf('%1$s %2$s', $cityObj->get("name"), $this->row_actions($actions) );
       } else {
           return sprintf('%1$s', $cityObj->get("name"));
       }
   }

}

?>
