<?php
// ------------------------------------------------------------------
/// @file admin/classes/obs_list.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief Contains definition of the @ref Wetterturnier_Rerunrequest_List_Table.
/// @details Contains definition of the @ref Wetterturnier_Rerunrequest_List_Table
///   which is used in the wordpress admin backend.
// ------------------------------------------------------------------
class Wetterturnier_Rerunrequest_List_Table extends WP_List_Table {

   public $limit  = NULL;

   /// @details Constructor, we override the parent to pass our own arguments
   ///    We usually focus on three parameters: singular and plural labels,
   ///    as well as whether the class supports AJAX.
   function __construct( $limit ) {
      parent::__construct( array(
        'singular'=> 'wetterturnier_rerunrequest_list', //Singular label
        'plural' => 'wetterturnier_rerunrequest_list', //plural label, also this well be one of the table css class
        'ajax'   => false //We won't support Ajax for this table
      ) );

      // Setting attributes used in prepare_items
      $this->limit  = $limit;
   }


   /// @details Add extra markup in the toolbars before or after the list
   /// @param string $which, helps you decide if you add the markup after (bottom)
   ///        or before (top) the list
   function extra_tablenav( $which ) {
      //if ( $which == "bottom" ){
      //    //The code that goes after the table is there
      //    echo"Hi, I'm after the table";
      //}
   }


   /// @details Define the columns that are going to be used in the table
   /// @return array $columns, the array of columns to use with the table
   function get_columns() {
      return $columns= array(
         'col_ID'=>__('ID','wpwt'),
         'col_tdate'=>__('Tournament Date','wpwt'),
         'col_cityID'=>__('City ID','wpwt'),
         'col_userID'=>__('By','wpwt'),
         'col_placed'=>__('Requested','wpwt'),
         'col_done'=>__('Done','wpwt')
      );
   }
   
   /// @details Decide which columns to activate the sorting functionality on
   /// @return array $sortable, the array of columns that can be sorted by the user
   public function get_sortable_columns() {
      return NULL;
      #return $sortable = array(
      #   "col_ID"=>array("ID",false),
      #   "col_cityname"=>array("cityname",false),
      #   "col_name"=>array("name",false),
      #);
   }
   
   /// @details Prepare the table with different parameters, pagination, columns and table elements
   /// @public $city, stdClass object with city information.
   /// @public $current, stdClass object with information about the tournament date and stuff.
   /// @public $search, string. Search-string.
   public function prepare_items() {

      global $wpdb, $_wp_column_headers;

      // Loading data
      $sql = array();
      array_push($sql,sprintf("SELECT ID,tdate,cityID,userID,placed,done "
                             ."FROM %swetterturnier_rerunrequest",$wpdb->prefix));
      array_push($sql,sprintf("ORDER BY ID DESC LIMIT %d",$this->limit));

      //print join("\n",$sql)."<br><br>\n";
      $data = $wpdb->get_results(join("\n",$sql));
      $totalitems = $wpdb->num_rows;

      //How many to display per page?
      $perpage = 20;
      //Which page is this?
      $paged = !empty($_GET["paged"]) ? $wpdb->prepare($_GET["paged"],NULL) : '';
      //Page Number
      if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
      //How many pages do we have in total?
      $totalpages = ceil($totalitems/$perpage);
      //adjust the query to take pagination into account
      if(!empty($paged) && !empty($perpage)){
         $offset=($paged-1)*$perpage;
      }

      $this->set_paginatio_args( array(
         "total_items" => $totalitems,
         "total_pages" => $totalpages,
         "per_page" => $perpage,
      ) );
   
      // Register the Columns
      $columns = $this->get_columns();
      $hidden = array();
      $sortable = $this->get_sortable_columns();
      $this->_column_headers = array($columns, $hidden, $sortable);

      // Fetch the items
      $this->items = $data;

   }
   
   
   /// @details Display the rows of records in the table
   /// @return string, echo the markup of the rows
   function display_rows() {

      global $WTadmin;

      //Get the records registered in the prepare_items method
      $records = $this->items;
      //Get the columns registered in the get_columns and get_sortable_columns methods
      list( $columns, $hidden ) = $this->get_column_info();

      //Loop for each record
      if(!empty($records)){foreach($records as $key=>$rec){

         //Open the line
         printf("<tr id=\"record_%d\">\n",$rec->ID);
         foreach ( $columns as $column_name => $column_display_name ) {

            //Style attributes for each col
            $class = sprintf("class='%s column-%s %s'",
                     $column_name,$column_name,(is_null($rec->done) ? "not-done" : "done"));
            $style = "";
            if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
            $attributes = $class . $style;

            // Create output in cell
            switch ( $column_name ) {
               case "col_ID":
                  echo "<td ".$attributes.">".stripslashes($rec->ID)."</td>\n"; break;
               case "col_tdate":
                  echo "<td ".$attributes.">".$WTadmin->date_format($rec->tdate)."</td>\n"; break;
               case "col_cityID":
                  $city = $WTadmin->get_city_info( (int)$rec->cityID );
                  echo "<td ".$attributes.">".$city->name."</td>\n"; break;
                  break;
               case "col_userID":
                  $user = get_user_by('ID',$rec->userID);
                  echo "<td ".$attributes.">".$user->get('user_login')."</td>\n"; break;
               case "col_placed":
                  echo "<td ".$attributes.">".$rec->placed."</td>\n"; break;
               case "col_done":
                  if ( is_null($rec->done) ) {
                     echo "<td ".$attributes.">[not yet done]</td>\n"; break;
                  } else {
                     echo "<td ".$attributes.">".$rec->placed."</td>\n"; break;
                  }
            }
         }

         //Close the line
         echo "</tr>\n";


      }}
   }

   /// @details Add the edit button to the bet entries
   function add_actions($rec) {
      $actions = array(
          'edit'      => sprintf('<a href="?page=%s&action=%s&cityID=%d&station=%d">Edit</a>',
                                 $_REQUEST['page'],'edit',$rec->cityID,$rec->wmo),
      );
      $name = sprintf("[%d] %s",$rec->wmo,$rec->name);
      return sprintf('%1$s %2$s', $name, $this->row_actions($actions) );
   }



}

?>
