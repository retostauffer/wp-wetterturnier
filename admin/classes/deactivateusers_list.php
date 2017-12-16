<?php
// ------------------------------------------------------------------
/// @file admin/classes/deactivateusers_list.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief Contains definition of the @ref Wetterturnier_DeactivateUsers_List_Table.
/// @details Contains definition of the @ref Wetterturnier_DeactivateUsers_List_Table
///   which is used in the wordpress admin backend.
// ------------------------------------------------------------------
class Wetterturnier_DeactivateUsers_List_Table extends WP_List_Table {

   /// Attribute limit (used on initialization if set)
   private $limit  = NULL;
   /// Attribute to search for user
   private $search = NULL;
   /// Attribute to save the input 'doshow' variable.
   private $doshow = NULL;

   /// Attribute used to store the total number of users.
   public $count_total = NULL;
   /// Attribute used to store the total number of active users.
   public $count_active = NULL;
   /// Attribute used to store the total number of inactive users.
   public $count_inactive = NULL;

   /// @details Constructor, we override the parent to pass our own arguments
   ///    We usually focus on three parameters: singular and plural labels,
   ///    as well as whether the class supports AJAX.
   function __construct( $limit = 50, $search = NULL, $doshow = "all" ) {
      parent::__construct( array(
        'singular'=> 'wetterturnier_deactivateusers_list', //Singular label
        'plural' => 'wetterturnier_deactivateusers_list', //plural label, also this well be one of the table css class
        'ajax'   => false //We won't support Ajax for this table
      ) );

      // Setting attributes used in prepare_items
      $this->limit  = (is_numeric($limit) ? (int)$limit : 50);
      $this->search = $search;
      $this->doshow = $doshow;

      add_filter('bulk_actions-edit-comments', 'my_remove');
   }

   /// @details Add extra markup in the toolbars before or after the list
   /// @param string $which, helps you decide if you add the markup after (bottom)
   ///        or before (top) the list
   function extra_tablenav( $which ) {
      if ( $which === "top" ) { ?>
         <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
            <select name="bulkaction" id="bulk-action-selector-top">
               <option value="-1">Bulk Actions</option>
               <option value="deactivate">Deactivate</option>
               <option value="activate">Activate</option>
            </select>
            <input type="submit" id="doaction" class="button action" value="Apply" />
         </div>
      <?php }
      //if ( $which == "bottom" ){
      //    //The code that goes after the table is there
      //    echo"Hi, I'm after the table";
      //}
   }


   /// @details Define the columns that are going to be used in the table
   /// @return array $columns, the array of columns to use with the table
   function get_columns() {
      return $columns= array(
         'col_checkbox'=>"<input type='checkbox' />",
         'col_userID'=>__('UserID','wpwt'),
         'col_user_login'=>__('Username','wpwt'),
         'col_user_email'=>__('User e-Mail','wpwt'),
         'col_participated'=>__('Participated','wpwt'),
         'col_lastplayed'=>__('Last Played','wpwt'),
         'col_ndaysago'=>__('N Days Ago','wpwt'),
         'col_inactive'=>__('Inactive','wpwt')
      );
   }
   
   /// @details Decide which columns to activate the sorting functionality on
   /// @return array $sortable, the array of columns that can be sorted by the user
   public function get_sortable_columns() {
      return $sortable = array(
         "col_userID"=>array("userID",false),
         "col_user_login"=>array("user_login",false),
         "col_user_email"=>array("user_email",false),
         "col_participated"=>array("participated",false),
         "col_lastplayed"=>array("lastplayed",false),
         "col_ndaysago"=>array("ndaysago",false),
         "col_inactive"=>array("inactive",false)
      );
   }
   
   /// @details Prepare the table with different parameters, pagination, columns and table elements
   /// @public $city, stdClass object with city information.
   /// @public $current, stdClass object with information about the tournament date and stuff.
   /// @public $search, string. Search-string.
   public function prepare_items() {

      global $wpdb, $_wp_column_headers;

      // Loading data
      $sql = array();
      array_push($sql,"SELECT user.ID AS userID, user.user_login, user.user_email,");
      array_push($sql,"CASE WHEN meta.inactive IS NULL THEN 0 ELSE meta.inactive END AS inactive,");
      array_push($sql,"CASE WHEN bet.participated IS NULL THEN 0 ELSE bet.participated END AS participated,");
      array_push($sql,sprintf("bet.lastplayed, bet.ndaysago FROM %s AS user",$wpdb->users));
      array_push($sql,"LEFT JOIN");
      array_push($sql,sprintf("(SELECT user_id, meta_value AS inactive FROM %s",$wpdb->usermeta));
      array_push($sql,"WHERE meta_key='ja_disable_user') AS meta"); // key from disable-users plugin!
      array_push($sql,"ON user.ID=meta.user_id");
      array_push($sql,"LEFT JOIN (SELECT userID, max(tdate) AS lastplayed,");
      array_push($sql,"count(tdate) AS participated,");
      array_push($sql,sprintf("(%d - max(tdate)) AS ndaysago",(int)(time()/86400)));
      array_push($sql,sprintf("FROM %swetterturnier_betstat GROUP BY userID) AS bet",$wpdb->prefix));
      array_push($sql,"ON user.ID=bet.userID");

      // If the search string matches "> [0-9]{1,}" or "< [0-9]{1,}" we subset on
      // the 'last played [0-9]{1,} days ago'.
      $where = array();
      if ( !is_null($this->search) ) {
         $match = preg_match("/^\s*?([<|>])\s*?([0-9]{1,})\s*?$/",$this->search,$matches);
         if ( $match ) {
            array_push($where,sprintf("ndaysago %s %d",$matches[1],(int)$matches[2]));
         } else {
            array_push($where,"user_login LIKE '%".$this->search."%'");
         }
      }
      // Adding doshow option if required
      if ( $this->doshow === "active" ) {
         array_push($where,"inactive = 0");
      } else if ( $this->doshow === "deactivated" ) {
         array_push($where,"inactive = 1");
      }

      //Parameters that are going to be used to order the result
      $orderby = !empty($_GET["orderby"]) ? $wpdb->prepare($_GET["orderby"],NULL) : 'ASC';
      $order   = !empty($_GET["order"])   ? $wpdb->prepare($_GET["order"],NULL) : '';
      if(!empty($orderby) & !empty($order)){
          array_push($sql,sprintf("ORDER BY %s %s",$orderby,$order));
      }

      // Count items
      $query = sprintf("SELECT count(*) AS total, sum(inactive) AS inactive, "
                      ."count(*)-sum(inactive) AS active FROM (%s) AS tmp",join("\n",$sql));
      $count_data = $wpdb->get_row( $query );
      $this->count_total     = $count_data->total;
      $this->count_active    = $count_data->active;
      $this->count_inactive  = $count_data->inactive;

      $this->set_pagination_args( array(
          'total_items' => $this->count_total, //WE have to calculate the total number of items
          'per_page'    => $this->limit        //WE have to determine how many items to show on a page
        ) );

      //How many to display per page?
      $perpage = 50;
      //Which page is this?
      $paged = !empty($_GET["paged"]) ? $wpdb->prepare($_GET["paged"],NULL) : '';
      //Page Number
      if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
      //How many pages do we have in total?
      $totalpages = ceil($this->count_total/$perpage);
      //adjust the query to take pagination into account
      if(!empty($paged) && !empty($perpage)){
         $offset=($paged-1)*$perpage;
         array_push($sql,sprintf(" LIMIT %d,%d",$offset,$perpage));
      }

      // Crate SQL statement
      $query = sprintf("SELECT * FROM (%s) AS tmp %s", # create temporary table
               join("\n",$sql), # data sql statement
               (count($where) ? sprintf("WHERE %s",join(" AND ",$where)) : "")); # where selector
      $data = $wpdb->get_results( $query );

      $this->set_paginatio_args( array(
         "total_items" => $this->count_total,
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
   function display_rows() { ?>

      <!-- wordpress table styling -->
      <style type="text/css">
         .striped > tbody .inactive                     { background-color: #F5BBBB; }
         .striped > tbody :nth-child(odd).inactive        { background-color: #F4CDCD; }
         .striped > tbody > tr:hover                      { background-color: #66ccff; }
         .striped > tbody > tr.inactive:hover             { background-color: #ff9966; }
         #col_ID    { width: 80px; }
         #col_user_login { width: 250px; }
      
         /* Some table styling elements */
         table.wetterturnier_deactivateusers_list thead th#col_checkbox {
            width: 50px;
            text-align: center;
         }
         table.wetterturnier_deactivateusers_list tbody td.column-col_checkbox {
            text-align: center;
         }
      </style>
      <!-- Functionality for the wordpress table -->
      <script type="text/javascript">
         jQuery(document).on("ready",function() {
            (function($) {
               var targettable = "table.wp-list-table.wetterturnier_deactivateusers_list"
               $(targettable+" #col_checkbox input[type='checkbox']").on("click",function() {
                  if ( $(this).prop('checked') ) { var set_to = true } else { var set_to = false }
                  $(targettable+" td.column-col_checkbox input[type='checkbox']")
                     .prop('checked',set_to);
               });
            })(jQuery);
         });
      </script>
      <?php

      global $WTadmin;

      //Get the records registered in the prepare_items method
      $records = $this->items;
      //Get the columns registered in the get_columns and get_sortable_columns methods
      list( $columns, $hidden ) = $this->get_column_info();

      $today = floor( time() / 86400 );

      //Loop for each record
      if(!empty($records)){foreach($records as $key=>$rec){

         //Open the line
         printf("<tr id=\"record_%d\" class=\"%s\">\n",$rec->userID,
               ($rec->inactive == 1 ? 'inactive' : 'active'));
         foreach ( $columns as $column_name => $column_display_name ) {

            //Style attributes for each col
            $class = sprintf("class='%s column-%s'",$column_name,$column_name);
            $style = "";
            if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
            $attributes = $class . $style;

            // Create output in cell
            switch ( $column_name ) {
               case "col_checkbox":
                  printf("<td %s><input type='checkbox' name='user_%d' value='1' /></td>\n",
                         $attributes, $rec->userID); break;
               case "col_userID":
                  echo "<td ".$attributes.">".stripslashes($rec->userID)."</td>\n"; break;
               case "col_user_login":
                  echo "<td ".$attributes.">".$this->add_actions($rec)."</td>\n"; break;
               case "col_user_email":
                  echo "<td ".$attributes.">".$rec->user_email."</td>\n"; break;
               case "col_participated":
                  printf("<td %s>%s</td>\n",$attributes,($rec->participated ?
                     sprintf("%d",$rec->participated) : 'never')); break;
               case "col_lastplayed":
                  printf("<td %s>%s</td>\n",$attributes,($rec->participated ?
                     sprintf("%s",$WTadmin->date_format($rec->lastplayed)) : 'never')); break;
               case "col_ndaysago":
                  printf("<td %s>%d</td>\n",$attributes,$rec->ndaysago); break;
               case "col_inactive":
                  printf("<td %s>%s</td>\n",$attributes,($rec->inactive == 1 ?
                     "inactive" : "active")); break;
            }
         }
         //Close the line
         echo "</tr>\n";

      }}
   }

   /// @details Add the edit button to the bet entries
   function add_actions($rec) {
      $link = sprintf("?page=%s&userID=%d",$_REQUEST['page'],$rec->userID);
      if(!is_null($this->search)) { $link .= sprintf("&s=%s",$this->search); }

      // Show 'activate' or 'deactivate' button
      if ( (int)$rec->inactive ) {
         $actions = array(
             'edit' => sprintf('<a href="%s&action=activate" class="activate">Activate</a>',$link)
         );
      } else {
         $actions = array(
             'delete' => sprintf('<a href="%s&action=deactivate" class="deactivate">Deactivate</a>',$link)
         );
      }
      $name = sprintf("%s",$rec->user_login);
      return sprintf('%1$s %2$s', $name, $this->row_actions($actions) );
   }



}

?>
