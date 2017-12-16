<?php
// ------------------------------------------------------------------
/// @file admin/classes/groupusers_list.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief Contains definition of the @ref Wetterturnier_Groupusers_List_Table.
/// @details Contains definition of the @ref Wetterturnier_Groupusers_List_Table
///   which is used in the wordpress admin backend.
// ------------------------------------------------------------------


class Wetterturnier_Groupusers_List_Table extends WP_List_Table {


   /// Attribute used to store search string on initialization
   private $search = NULL;
   private $counts = NULL;

   /// @details Constructor, we override the parent to pass our own arguments.
   /// @param $search. Search string (if set).
   function __construct( $search = NULL ) {
     parent::__construct( array(
        'singular'=> 'wetterturnier_groupusers_list', //Singular label
        'plural' => 'wetterturnier_groupusers_list', //plural label, also this well be one of the table css class
        'ajax'   => false //We won't support Ajax for this table
     ) );

     $this->search = $search;
   }


   /**
    * Add extra markup in the toolbars before or after the list
    * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
    */
   function extra_tablenav( $which ) {
      // Check if a group filter is set (used to set the selected flag)
      $groupfilter = $this->get_group_filter_id();

      // Show elements on top
      if ( $which === "top" ) { ?>
         <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-top" class="screen-reader-text">Group filter:</label>
            <select name="group-filter" id="group-filter">
               <option value="-1" <?php print (is_bool($groupfilter) ? "selected" : ""); ?>>Show all</option>
               <?php foreach ( $this->counts as $rec ) { ?>
                  <option value="<?php print $rec->groupID; ?>" <?php print ((int)$rec->groupID === $groupfilter ? "selected" : ""); ?>>
                     <?php printf("[active: %d/%d] %s",$rec->active,$rec->total,$rec->groupName); ?>
                  </option>
               <?php } ?>
            </select>
            <input type="submit" id="dofilter" class="button action" value="Apply Filter" />
         </div>
      <?php }

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
          'col_id'=>__('ID','wpwt'),
          'col_userid'=>__('userID','wpwt'),
          'col_groupid'=>__('groupID','wpwt'),
          'col_user'=>__('User Name','wpwt'),
          'col_groupName'=>__('Group Name','wpwt'),
          'col_status'=>__('Status','wpwt')
       );
    }
    
    /**
     * Decide which columns to activate the sorting functionality on
     * @return array $sortable, the array of columns that can be sorted by the user
     */
    public function get_sortable_columns() {
       return $sortable = array(
          "col_id"=>array("ID",false),
          "col_userid"=>array("userID",false),
          "col_groupid"=>array("groupID",false),
          "col_user"=>array("user_login",false),
          "col_groupName"=>array("groupName",false)
       );
    }

    /// @details Evaluates the get argument 'group-filter'. If not set or
    ///   negative (show all groupusers) false will be returned. If set
    ///   an integer will be returned containing the group ID identifier.
    /// @return Either boolean false if gorup-filter is not set or an integer.
    private function get_group_filter_id() {
        if ( ! empty($_GET["group-filter"]) ) {
           $groupfilter = (int)$_GET["group-filter"];
           if ( $groupfilter < 0 ) { $groupfilter = false; }
        } else { $groupfilter = false; }
        return( $groupfilter );
    }
    
    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    /// @global $search string.
    function prepare_items( ) {

        global $wpdb, $_wp_column_headers, $search;
        $screen = get_current_screen();

        // Getting all groups and count how many users are active/total in the group.s
        $sql = array();

        array_push($sql,"SELECT g.groupID, g.groupName, g.groupDesc, guall.total,");
        array_push($sql,sprintf("guactive.active FROM %swetterturnier_groups AS g",$wpdb->prefix));
        array_push($sql,"LEFT OUTER JOIN (");
        array_push($sql,"  SELECT groupID, count(*) AS total"); 
        array_push($sql,sprintf("FROM %swetterturnier_groupusers GROUP BY groupID",$wpdb->prefix));
        array_push($sql,") AS guall ON g.groupID=guall.groupID");
        array_push($sql,"LEFT OUTER JOIN (");
        array_push($sql,"   SELECT groupID, count(*) AS active");
        array_push($sql,sprintf("FROM %swetterturnier_groupusers",$wpdb->prefix));
        array_push($sql,"WHERE active IN (1,8) GROUP BY groupID");
        array_push($sql,") AS guactive ON g.groupID=guactive.groupID");

        // Save to object for extra_tablenav
        $this->counts = $wpdb->get_results(join("\n",$sql));
        $totalitems = 0;
        foreach ( $this->counts as $rec ) { $totalitems = $totalitems + (int)$rec->total; }


        // Prepare sql query
        $sql = array();
        array_push($sql,"SELECT gu.*, g.groupName, u.user_login, u.display_name FROM");
        array_push($sql,sprintf("%swetterturnier_groupusers AS gu",$wpdb->prefix));
        array_push($sql,sprintf("LEFT OUTER JOIN %swetterturnier_groups AS g",$wpdb->prefix));
        array_push($sql,"ON g.groupID=gu.groupID");
        array_push($sql,sprintf("LEFT OUTER JOIN %s AS u ON u.ID=gu.userID",$wpdb->users));

        if ( !is_null($this->search) ) {
           array_push($sql,"WHERE (g.groupName LIKE '%".$this->search."' OR");
           array_push($sql,"u.user_login LIKE '%".$this->search."%')");
        }


        $groupfilter = $this->get_group_filter_id();

        // If group filter is set: append where selector
        if ( is_integer($groupfilter) ) { array_push($sql,sprintf("WHERE gu.groupID=%d",$groupfilter)); }

        /* -- Ordering parameters -- */
        //Parameters that are going to be used to order the result
        $orderby = !empty($_GET["orderby"]) ? $wpdb->prepare($_GET["orderby"],NULL) : 'ASC';
        $order   = !empty($_GET["order"])   ? $wpdb->prepare($_GET["order"],NULL) : '';
        if(!empty($orderby) & !empty($order)){
            array_push($sql,sprintf("ORDER BY %s %s",$orderby,$order));
        }
    
        //How many to display per page?
        $perpage = 50;
        //Which page is this?
        $paged = !empty($_GET["paged"]) ? (int)$_GET["paged"] : 0;
        //Page Number
        if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
        //How many pages do we have in total?
        $totalpages = ceil($totalitems/$perpage);

        //adjust the query to take pagination into account
        if(!empty($paged) && !empty($perpage)){
           $offset=($paged-1)*$perpage;
           array_push($sql,sprintf(" LIMIT %d,%d",$offset,$perpage));
        }

        /* -- Register the pagination -- */
        $this->set_pagination_args( array(
           "total_items" => $totalitems,
           "total_pages" => $totalpages,
           "per_page" => $perpage,
        ) );
        //The pagination links are automatically built according to those parameters
    
        /* -- Register the Columns -- */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        /* -- Fetch the items -- */
        $this->items = $wpdb->get_results(join("\n",$sql));

    }
    
    
    /**
     * Display the rows of records in the table
     * @return string, echo the markup of the rows
     */
    function display_rows() {
    
        //Get the records registered in the prepare_items method
        $records = $this->items;
    
        //Get the columns registered in the get_columns and get_sortable_columns methods
        list( $columns, $hidden ) = $this->get_column_info();
    
        //Loop for each record
        if(!empty($records)){foreach($records as $rec){
    
            // Format the status output
            if ( strtotime($rec->until) <= 0 ) {
                $rec->until = false;
            } else {
                //$rec->until = __('Membership ended','wpwt').": ".strftime('%Y-%m-%d',strtotime($rec->until));
                $rec->until = '&nbsp;|&nbsp;Active until '.strftime('%Y-%m-%d',strtotime($rec->until));
            }
            $rec->since = __('Member since','wpwt').": ".strftime('%Y-%m-%d',strtotime($rec->since));
    
            //Open the line
            echo "<tr id=\"record_".$rec->groupID."\" class=\"row-active-".$rec->active."\">\n";
            foreach ( $columns as $column_name => $column_display_name ) {
    
                //Style attributes for each col
                $class = "class='$column_name column-$column_name'";
                $style = "";
                if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
                if ( $rec->active == 9 ) { $style = ' style="color: orange;"'; }
                $attributes = $class . $style;
             
                // Create output in cell
                switch ( $column_name ) {
                    case "col_id":        echo "<td ".$attributes.">".stripslashes($rec->ID)."</td>\n"; break;
                    case "col_userid":    echo "<td ".$attributes.">".stripslashes($rec->userID)."</td>\n"; break;
                    case "col_groupid":   echo "<td ".$attributes.">".stripslashes($rec->groupID)."</td>\n"; break;
                    case "col_user":      echo "<td ".$attributes.">".$this->add_actions($rec)."</td>\n"; break;
                    case "col_groupName": echo "<td ".$attributes.">".$rec->groupName."</td>\n";   break;
                    case "col_status":    $this->show_status($rec,$attributes); break;
                }
            }
    
           //Close the line
           echo "</tr>\n";
        }}
    }

    // Add the edit button to the group entries.
    function show_status($rec,$attributes) {
        // Status disabled:
        switch ( $rec->active ) {
            case 0:
               echo "<td ".$attributes.">Inactive<br>".$rec->since.$rec->until."</td>\n"; break;
               break;
            case 1: 
               echo "<td ".$attributes.">Active<br>".$rec->since.$rec->until."</td>\n"; break;
               break;
            case 9:
               echo "<td ".$attributes.">Open application<br>".$rec->since."</td>\n"; break;
               break;
            default:
               echo "<td ".$attributes.">Open application<br>Unknown status</td>\n"; break;
               break;
        }
    }

    // Add the edit button to the group entries.
    function add_actions($item) {
        if ( $item->active == 1 ) {
            $url = sprintf('?page=%s&action=%s&groupuserID=%s',$_REQUEST['page'],'setinactive',$item->ID);
            if ( is_string($this->search) ) { $url = sprintf("%s&s=%s",$url,$this->search); }
            $actions = array(
                'setinactive'    => sprintf('<a href="%s">Remove user from group</a>',$url),
            );
            if ( strcmp( $item->display_name,$item->user_login ) == 0 ) {
                return sprintf('%1$s %2$s', $item->user_login, $this->row_actions($actions) );
            } else {
                return sprintf('%1$s %2$s', $item->user_login.' ['.$item->display_name.']', $this->row_actions($actions) );
            }
        } else {
            return sprintf('%1$s', $item->user_login.' ['.$item->display_name.']' );
        }
    }
}

?>
