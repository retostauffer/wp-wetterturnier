<?php
// ------------------------------------------------------------------
/// @file admin/classes/groups_list.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief Contains definition of the @ref Wetterturnier_Groups_List_Table.
/// @details Contains definition of the @ref Wetterturnier_Groups_List_Table
///   which is used in the wordpress admin backend.
// ------------------------------------------------------------------


class Wetterturnier_Groups_List_Table extends WP_List_Table {


   /**
    * Constructor, we override the parent to pass our own arguments
    * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
    */
    function __construct() {
       parent::__construct( array(
      'singular'=> 'wetterturnier_groups_list', //Singular label
      'plural' => 'wetterturnier_groups_list', //plural label, also this well be one of the table css class
      'ajax'   => false //We won't support Ajax for this table
      ) );
    }


    /**
     * Add extra markup in the toolbars before or after the list
     * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
     */
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
          'col_id'=>__('ID','wpwt'),
          'col_name'=>__('Group Name','wpwt'),
          'col_members'=>__('Members','wpwt'),
          'col_desc'=>__('Group Description','wpwt'),
          'col_status'=>__('Group Status','wpwt')
       );
    }
    
    /**
     * Decide which columns to activate the sorting functionality on
     * @return array $sortable, the array of columns that can be sorted by the user
     */
    public function get_sortable_columns() {
       return $sortable = array(
          "col_id"=>array("groupID",false),
          "col_name"=>array("groupName",false)
       );
    }
    
    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    function prepare_items() {

        global $wpdb, $_wp_column_headers;
        $screen = get_current_screen();
    
        // Prepare sql query
        $query = "SELECT * FROM ".$wpdb->prefix."wetterturnier_groups";
    
        /* -- Ordering parameters -- */
        //Parameters that are going to be used to order the result
        $orderby = !empty($_GET["orderby"]) ? $wpdb->prepare($_GET["orderby"],NULL) : 'ASC';
        $order   = !empty($_GET["order"])   ? $wpdb->prepare($_GET["order"],NULL) : '';
        if(!empty($orderby) & !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; }
    
        /* -- Pagination parameters -- */
        //Number of elements in your table?
        $totalitems = $wpdb->query($query); //return the total number of affected rows
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
          $query.=' LIMIT '.(int)$offset.','.(int)$perpage;
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
        $this->items = $wpdb->get_results($query);

        /* -- Fetch group member number -- */
        $members = $wpdb->get_results('SELECT count(groupID) AS members, groupID FROM '
                               .$wpdb->prefix.'wetterturnier_groupusers GROUP BY groupID');
        for ( $i=0; $i<count($this->items); $i++ ) {
            $gid = $this->items[$i]->groupID; $N = false;
            foreach ( $members as $mem ) {
                 if ( $mem->groupID == $gid ) { $N = $mem->members; break; }
            }
            $this->items[$i]->members = $N;
        }

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
                $rec->until = 'Group is active';
            } else {
                $rec->until = __('Inactive since','wpwt').": ".strftime('%Y-%m-%d',strtotime($rec->until));
            }
            $rec->since = __('Group created','wpwt').": ".strftime('%Y-%m-%d',strtotime($rec->since));
    
            // Pre-define the edit link
            //$editlink  = '/wp-admin/link.php?action=edit&link_id='.(int)$rec->link_id;

            //Open the line
            echo "<tr id=\"record_".$rec->groupID."\" class=\"row-active-".$rec->active."\">\n";
            foreach ( $columns as $column_name => $column_display_name ) {
    
                //Style attributes for each col
                $class = "class='$column_name column-$column_name'";
                $style = "";
                if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
                $attributes = $class . $style;
             
                // Create output in cell
                switch ( $column_name ) {
                    case "col_id":        echo "<td ".$attributes.">".stripslashes($rec->groupID)."</td>\n"; break;
                    case "col_name":      echo "<td ".$attributes.">".$this->add_actions($rec)."</td>\n";   break;
                    case "col_members":   echo "<td ".$attributes.">".$rec->members."</td>\n";   break;
                    case "col_desc":      echo "<td ".$attributes.">".stripslashes($rec->groupDesc)."</td>\n"; break;
                    case "col_status":    echo "<td ".$attributes.">".$rec->since."<br>".$rec->until."</td>\n"; break;
                }
            }
    
           //Close the line
           echo "</tr>\n";
        }}
    }

    // Add the edit button to the group entries.
    function add_actions($item) {
        if ( $item->active == 1 ) {
            $actions = array(
                'edit'      => sprintf('<a href="?page=%s&action=%s&group=%s">Edit</a>',$_REQUEST['page'],'edit',$item->groupID),
                'delete'    => sprintf('<a href="?page=%s&action=%s&group=%s">Delete</a>',$_REQUEST['page'],'delete',$item->groupID),
            );
            return sprintf('%1$s %2$s', $item->groupName, $this->row_actions($actions) );
        } else {
            return sprintf('%1$s', $item->groupName );
        }
    }
}

?>
