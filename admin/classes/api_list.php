<?php
// ------------------------------------------------------------------
/// @file admin/classes/api_list.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief Contains definition of the @ref Wetterturnier_API_List_Table.
/// @details Contains definition of the @ref Wetterturnier_API_List_Table
///   which is used in the wordpress admin backend.
// ------------------------------------------------------------------
class Wetterturnier_API_List_Table extends WP_List_Table {

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
        'singular'=> 'wetterturnier_api_list', //Singular label
        'plural'  => 'wetterturnier_api_list', //plural label, also this well be one of the table css class
        'ajax'    => false //We won't support Ajax for this table
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
   }


   /// @details Define the columns that are going to be used in the table
   /// @return array $columns, the array of columns to use with the table
   function get_columns() {
      return $columns= array(
         'col_checkbox'=>"<input type='checkbox' />",
         'col_public'=>__('Public','wpwt'),
         'col_key'=>__('API Key','wpwt'),
         'col_type'=>__('Type','wpwt'),
         'col_config'=>__('Configuration','wpwt'),
         'col_name'=>__('Name','wpwt'),
         'col_description'=>__('Description','wpwt'),
         'col_since'=>__('Since','wpwt'),
         'col_until'=>__('Until','wpwt')
      );
   }
   
   /// @details Decide which columns to activate the sorting functionality on
   /// @return array $sortable, the array of columns that can be sorted by the user
   public function get_sortable_columns() {
      return $sortable = array(
         "col_key"=>array("APIKEY",false),
         "col_public"=>array("until",false),
         "col_name"=>array("name",false),
         "col_type"=>array("APITYPE",false),
         "col_until"=>array("until",false)
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
      array_push($sql,sprintf("SELECT * FROM %swetterturnier_api",$wpdb->prefix));

      // If the search string matches "> [0-9]{1,}" or "< [0-9]{1,}" we subset on
      // the 'last played [0-9]{1,} days ago'.
      $where = array();
      if ( !is_null($this->search) ) {
         array_push($where,"name like '%".$this->search."%'");
      }
      // Adding doshow option if required
      if ( $this->doshow === "active" )          { array_push($where,"active = 1");
      } else if ( $this->doshow === "inactive" ) { array_push($where,"active = 0"); }
      // Take input arg if set
      if ( ! empty($_REQUEST["doshow"]) ) {
         if ( $_REQUEST["doshow"] === "active" )          { array_push($where,"active = 1");
         } else if ( $_REQUEST["doshow"] === "inactive" ) { array_push($where,"active = 0"); }
      }

      //Parameters that are going to be used to order the result
      $orderby = !empty($_GET["orderby"]) ? $wpdb->prepare($_GET["orderby"],NULL) : 'ASC';
      $order   = !empty($_GET["order"])   ? $wpdb->prepare($_GET["order"],NULL) : '';
      if(!empty($orderby) & !empty($order)){
          array_push($sql,sprintf("ORDER BY %s %s",$orderby,$order));
      }

      // Count items
      $query = sprintf("SELECT count(*) AS total, count(*)-sum(active) AS inactive, "
                      ."sum(active) AS active FROM (%s) AS tmp",join("\n",$sql));
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

      ///print "<br>\n".$query."<br>\n";

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
         #col_checkbox { width: 30px; }
         #col_public   { width: 80px; }
         #col_key      { width: 180px; }
         #col_type     { width: 100px; }
         #col_since    { width: 150px; }
         #col_until    { width: 120px; }
      
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
         printf("<tr id=\"record_%d\" class=\"%s\">\n",$rec->ID,
               ($rec->active == 1 ? 'active' : 'inactive'));
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
                         $attributes, $rec->ID); break;
               case "col_key":
                  echo "<td ".$attributes.">".$this->add_actions($rec)."</td>\n"; break;
               case "col_type":
                  echo "<td ".$attributes.">".$rec->APITYPE."</td>\n"; break;
               case "col_config":
                  echo "<td ".$attributes.">".$rec->APICONFIG."</td>\n"; break;
               case "col_name":
                  echo "<td ".$attributes.">".$rec->name."</td>\n"; break;
               case "col_description":
                  echo "<td ".$attributes.">".$rec->description."</td>\n"; break;
                  ##add_actions($rec)."</td>\n"; break;
               case "col_public":
                  printf("<td %s><input type=\"checkbox\" %s disabled=\"1\"/></td>\n",
                     $attributes,($rec->ISPUBLIC ? "checked" : ""));
                  break;
               case "col_since":
                  printf("<td %s>%s</td>\n",$attributes,$rec->since); break;
               case "col_until":
                  printf("<td %s>%s</td>\n",$attributes,
                     (!strlen($rec->until) ? "" : $WTadmin->date_format($rec->until)));
                  break;
            }
         }
         //Close the line
         echo "</tr>\n";

      }}
   }

   /// @details Add the edit button to the bet entries
   function add_actions($rec) {
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&ID=%d">Edit</a>',
                                   $_REQUEST['page'],'edit',$rec->ID),
            'delete'    => sprintf('<a href="?page=%s&action=%s&ID=%d">Delete</a>',
                                   $_REQUEST['page'],'delete',$rec->ID),
        );
        return sprintf('<b>%1$s</b><br>%2$s', $rec->APIKEY, $this->row_actions($actions) );
   }



}

?>
