<?php
// ------------------------------------------------------------------
/// @file admin/classes/obs_list.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief Contains definition of the @ref Wetterturnier_Obs_List_Table.
/// @details Contains definition of the @ref Wetterturnier_Obs_List_Table
///   which is used in the wordpress admin backend.
// ------------------------------------------------------------------
class Wetterturnier_Obs_List_Table extends WP_List_Table {

   public $city    = NULL;
   public $tdate   = NULL;
   public $search  = NULL;


   /// @details Constructor, we override the parent to pass our own arguments
   ///    We usually focus on three parameters: singular and plural labels,
   ///    as well as whether the class supports AJAX.
   /// @param $city. Integer city ID.
   /// @param $tdate. Integer tournament date.
   /// @param $search. Search string (or empty string).
   function __construct( $city, $tdate, $search ) {
      parent::__construct( array(
        'singular'=> 'wetterturnier_obs_list', //Singular label
        'plural' => 'wetterturnier_obs_list', //plural label, also this well be one of the table css class
        'ajax'   => false //We won't support Ajax for this table
      ) );

      // Setting attributes used in prepare_items
      $this->city    = $city;
      $this->tdate   = $tdate;
      $this->search  = $search;
   }


   /// @details Add extra markup in the toolbars before or after the list
   /// @param string $which, helps you decide if you add the markup after (bottom)
   ///        or before (top) the list
   function extra_tablenav( $which ) {
      if ( $which == "bottom" ){
          //The code that goes after the table is there
          echo"Hi, I'm after the table";
      }
   }


   /// @details Define the columns that are going to be used in the table
   /// @return array $columns, the array of columns to use with the table
   function get_columns() {
      return $columns= array(
         'col_ID'=>__('Station ID','wpwt'),
         'col_cityname'=>__('City','wpwt'),
         'col_name'=>__('Station Name','wpwt'),
      );
   }
   
   /// @details Decide which columns to activate the sorting functionality on
   /// @return array $sortable, the array of columns that can be sorted by the user
   public function get_sortable_columns() {
      return $sortable = array(
         "col_ID"=>array("ID",false),
         "col_cityname"=>array("cityname",false),
         "col_name"=>array("name",false),
      );
   }
   
   /// @details Prepare the table with different parameters, pagination, columns and table elements
   /// @public $city, stdClass object with city information.
   /// @public $tdate, integer tournament date. 
   /// @public $search, string. Search-string.
   public function prepare_items() {

      global $wpdb, $_wp_column_headers;

      $city    = $this->city;
      $tdate   = $this->tdate;
      $search  = $this->search;

      // Compute number of parameters being expected for a proper 'submitted'
      // bet. 
      $betdays  = get_option("wetterturnier_betdays");
      $numparam = count(json_decode($city->paramconfig));
      $expected = $betdays * $numparam;

      $tdate_min = $tdate;
      $tdate_max = $tdate + get_option("wetterturnier_betdays");

      // Loading data
      $sql = array();
      array_push($sql,"SELECT stn.*, cit.name AS cityname");
      array_push($sql,sprintf("FROM wp_wetterturnier_obs AS obs",$wpdb->prefix));
      array_push($sql,sprintf("LEFT OUTER JOIN wp_wetterturnier_stations AS stn",$wpdb->prefix));
      array_push($sql,"ON stn.wmo=obs.station");
      array_push($sql,sprintf("LEFT OUTER JOIN wp_wetterturnier_cities AS cit",$wpdb->prefix));
      array_push($sql,"ON cit.ID=stn.cityID");
      array_push($sql,sprintf("WHERE cit.ID = %d",$_REQUEST["cityID"]));
      array_push($sql,sprintf("AND obs.betdate BETWEEN %d AND %d",$tdate_min,$tdate_max));

      // If there is a search item set
      if ( is_string($search) ) {
         if ( is_numeric($search) ) {
            array_push($sql,sprintf("AND stn.wmo = %d",(int)$search));
         } else {
            array_push($sql,"AND LCASE(stn.name) LIKE '%".strtolower($search)."%'");
         }
      }
      // Grouping, essential
      array_push($sql,"GROUP BY obs.station");

      //Parameters that are going to be used to order the result
      $orderby = !empty($_GET["orderby"]) ? $wpdb->prepare($_GET["orderby"],NULL) : 'ASC';
      $order   = !empty($_GET["order"])   ? $wpdb->prepare($_GET["order"],NULL) : '';
      if(!empty($orderby) & !empty($order)){
         array_push($sql,sprintf("ORDER BY %s %s",$orderby,$order));
      }

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
            $class = "class='$column_name column-$column_name'";
            $style = "";
            if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
            $attributes = $class . $style;

            // Create output in cell
            switch ( $column_name ) {
               case "col_ID":
                  echo "<td ".$attributes.">".stripslashes($rec->ID)."</td>\n"; break;
               case "col_cityname":
                  if ( strlen($rec->cityname) == 0 ) { $rec->cityname = "[not in use]"; }
                  echo "<td ".$attributes.">".stripslashes($rec->cityname)."</td>\n"; break;
               case "col_name":
                  echo "<td ".$attributes.">".$this->add_actions($rec)."</td>\n"; break;
            }
         }

         //Close the line
         echo "</tr>\n";


      }}
   }

   /// @details Add the edit button to the bet entries
   function add_actions($rec) {
      $actions = array(
          'edit'      => sprintf('<a href="?page=%s&action=%s&cityID=%d&station=%d&tdate=%d">Edit</a>',
                                 $_REQUEST['page'],'edit',$rec->cityID,$rec->wmo,$this->tdate),
      );
      $name = sprintf("[%d] %s",$rec->wmo,$rec->name);
      return sprintf('%1$s %2$s', $name, $this->row_actions($actions) );
   }



}

?>
