<?php
// ------------------------------------------------------------------
/// @file admin/classes/bets_list.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief Contains definition of the @ref Wetterturnier_Bets_List_Table.
/// @details Contains definition of the @ref Wetterturnier_Bets_List_Table
///   which is used in the wordpress admin backend.
// ------------------------------------------------------------------

class Wetterturnier_Bets_List_Table extends WP_List_Table {

   public $city    = NULL;
   public $tdate   = NULL;
   public $search  = NULL;

   // Constructor, we override the parent to pass our own arguments
   // We usually focus on three parameters: singular and plural labels,
   // as well as whether the class supports AJAX.
   function __construct( $city, $tdate, $search ) {
      parent::__construct( array(
        'singular'=> 'wetterturnier_bets_list', //Singular label
        'plural' => 'wetterturnier_bets_list', //plural label, also this well be one of the table css class
        'ajax'   => false //We won't support Ajax for this table
      ) );

      $this->city = $city;
      $this->tdate   = $tdate;
      $this->search  = $search;
   }


   // Add extra markup in the toolbars before or after the list
   // @param string $which, helps you decide if you add the markup after (bottom)
   //        or before (top) the list
   function extra_tablenav( $which ) {
      if ( $which == "bottom" ){
          //The code that goes after the table is there
          echo"Hi, I'm after the table";
      }
   }


   /**
    * Define the columns that are going to be used in the table
    * @return array $columns, the array of columns to use with the table
    */
   function get_columns() {
      return $columns= array(
         'col_userID'=>__('userID','wpwt'),
         'col_user_login'=>__('User','wpwt'),
         'col_missing'=>__("Missing Bets","wpwt"),
         'col_submitted'=>__('Submitted','wpwt'),
      );
   }
   
   /**
    * Decide which columns to activate the sorting functionality on
    * @return array $sortable, the array of columns that can be sorted by the user
    */
   public function get_sortable_columns() {
      return $sortable = array(
         "col_userID"=>array("userID",false),
         "col_user_login"=>array("user_login",false),
         "col_submitted"=>array("name",false),
      );
   }
   
   /**
    * Prepare the table with different parameters, pagination, columns and table elements
    */
   /// @public $city, stdClass object.
   /// @public $tdate, integer tournament date.
   /// @public $search, string.
   function prepare_items( ) {

      global $wpdb, $_wp_column_headers;

      // Compute number of parameters being expected for a proper 'submitted'
      // bet. 
      $betdays  = get_option("wetterturnier_betdays");
      $numparam = count(json_decode($this->city->paramconfig));
      $expected = $betdays * $numparam;

      // Loading data
      $sql = array();
      array_push($sql,"SELECT usr.ID AS userID, usr.user_login,");
      array_push($sql,"usr.user_nicename, stat.submitted, bet.cityID,"); 
      array_push($sql,"CASE WHEN stat.submitted IS NULL THEN 1 ELSE 0 END AS missing");
      array_push($sql,sprintf("FROM %swetterturnier_bets AS bet",$wpdb->prefix));
      array_push($sql,sprintf("LEFT OUTER JOIN %susers AS usr",$wpdb->prefix));
      array_push($sql,"ON usr.ID=bet.userID");
      array_push($sql,sprintf("LEFT OUTER JOIN %swetterturnier_param AS par",$wpdb->prefix));
      array_push($sql,"ON par.paramID=bet.paramID");
      array_push($sql,sprintf("LEFT OUTER JOIN %swetterturnier_betstat AS stat",$wpdb->prefix));
      array_push($sql,"ON bet.userID=stat.userID AND bet.cityID=stat.cityID");
      array_push($sql,"AND bet.tdate=stat.tdate");
      array_push($sql,sprintf("WHERE bet.cityID=%d",$this->city->ID));
      array_push($sql,sprintf("AND bet.tdate=%d",$this->tdate));
      array_push($sql,"AND usr.user_login NOT LIKE \"GRP_%\"");

      // If there is a search item set
      if ( is_string($this->search) ) {
         array_push($sql,"AND (LCASE(usr.user_login) LIKE '%".strtolower($this->search)."%'");
         array_push($sql,"OR LCASE(usr.user_nicename) LIKE '%".strtolower($this->search)."%')");
      }
      // Grouping, essential
      array_push($sql,"GROUP BY bet.userID");

      //Parameters that are going to be used to order the result
      $orderby = !empty($_GET["orderby"]) ? $wpdb->prepare($_GET["orderby"],NULL) : 'ASC';
      $order   = !empty($_GET["order"])   ? $wpdb->prepare($_GET["order"],NULL) : '';
      if(!empty($orderby) & !empty($order)){
         array_push($sql,sprintf("ORDER BY %s %s",$orderby,$order));
      }

      #print join("\n",$sql)."<br><br>\n";
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
   
   
   /**
    * Display the rows of records in the table
    * @return string, echo the markup of the rows
    */
   function display_rows() {

      global $WTadmin;

      //Get the records registered in the prepare_items method
      $records = $this->items;
      //Get the columns registered in the get_columns and get_sortable_columns methods
      list( $columns, $hidden ) = $this->get_column_info();

      //Loop for each record
      if( !empty($records) ) { foreach($records as $key=>$rec) {

         //Open the line
         printf("<tr id=\"record_%d\" class=\"row-valid-%s\">\n",
                       $rec->userID,($rec->missing == 0 ? "true" : "false"));
         foreach ( $columns as $column_name => $column_display_name ) {

            //Style attributes for each col
            $class = "class='$column_name column-$column_name'";
            $style = "";
            if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
            $attributes = $class . $style;

            // Create output in cell
            switch ( $column_name ) {
               case "col_userID":
                  echo "<td ".$attributes.">".stripslashes($rec->userID)."</td>\n"; break;
               case "col_user_login":
                  echo "<td ".$attributes.">".$this->add_actions($rec)."</td>\n"; break;
               case "col_missing":
                  echo "<td ".$attributes.">"
                       .($rec->missing == 0 ? " [valid]" : " [MISSING VALUES]")."</td>\n";   break;
               case "col_submitted":
                  echo "<td ".$attributes.">".stripslashes($rec->submitted)."</td>\n";   break;
            }
         }
         //Close the line
         echo "</tr>\n";
      }}
   }

   // Add the edit button to the bet entries
   function add_actions($rec) {
      $username = $rec->user_nicename." [".stripslashes($rec->user_login)."]";
      $actions = array(
          'edit'      => sprintf('<a href="?page=%s&action=%s&cityID=%d&userID=%d&tdate=%d">Edit</a>',
                                 $_REQUEST['page'],'edit',$rec->cityID,$rec->userID,$this->tdate),
      );
      return sprintf('%1$s %2$s', $username, $this->row_actions($actions) );
   }



}

?>
