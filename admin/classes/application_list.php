<?php
// ------------------------------------------------------------------
/// @file admin/classes/applications_list.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief Contains definition of the @ref Wetterturnier_Applications_List_Table.
/// @details Contains definition of the @ref Wetterturnier_Applications_List_Table
///   which is used in the wordpress admin backend.
// ------------------------------------------------------------------

class Wetterturnier_Application_List_Table extends WP_List_Table {


   /// Attribute to store the 'search' string if set.
   private $search = NULL;
   /// Attribute to store input action. Either 'wtapply' or 'wtremove'.
   private $action = NULL;

   /**
    * Constructor, we override the parent to pass our own arguments
    * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
    */
    function __construct( $search, $action ) {
       parent::__construct( array(
      'singular'=> 'wetterturnier_application_list', //Singular label
      'plural' => 'wetterturnier_application_list', //plural label, also this well be one of the table css class
      'ajax'   => false //We won't support Ajax for this table
      ) );

      // Save input arguments
      $this->search = $search;
      $this->action = $action;
    }


    /**
     * Add extra markup in the toolbars before or after the list
     * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
     */
    function extra_tablenav( $which ) {
        //if ( $which == "bottom" ){
        //    //The code that goes after the table is there
        //    echo"Hi, I'm after the table";
        //}
    }


    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    function get_columns() {
       return $columns= array(
          'col_what'=>__('What','wpwt'),
          'col_user'=>__('User Name','wpwt'),
          'col_groupName'=>__('Group Name','wpwt'),
          'col_application'=>__('Application','wpwt')
       );
    }
    
    /**
     * Decide which columns to activate the sorting functionality on
     * @return array $sortable, the array of columns that can be sorted by the user
     */
    public function get_sortable_columns() {
       return $sortable = array(
          "col_user"=>array("user_login",false),
          "col_groupName"=>array("groupName",false)
       );
    }
    
    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    function prepare_items( ) {

        global $wpdb, $_wp_column_headers;
        $screen = get_current_screen();
    
        // Prepare sql query
        $sql = array();
        array_push($sql,"SELECT gu.*, g.groupName, u.user_login, u.display_name, gu.application");
        array_push($sql,sprintf("FROM %swetterturnier_groupusers AS gu",$wpdb->prefix));
        array_push($sql,sprintf("LEFT OUTER JOIN %swetterturnier_groups AS g",$wpdb->prefix));
        array_push($sql,"ON g.groupID=gu.groupID");
        array_push($sql,sprintf("LEFT OUTER JOIN %s AS u ON u.ID=gu.userID",$wpdb->users));
        array_push($sql,sprintf("WHERE gu.active = %d",($this->action === "wtapply" ? 9 : 8)));
        if ( is_string($this->search) ) {
           array_push($sql,"AND (g.groupName LIKE '%".$this->search."%' "
                          ."OR u.user_login LIKE '%".$this->search."%')");
        }
    
        /* -- Ordering parameters -- */
        //Parameters that are going to be used to order the result
        $orderby = !empty($_GET["orderby"]) ? $wpdb->prepare($_GET["orderby"],NULL) : 'ASC';
        $order   = !empty($_GET["order"])   ? $wpdb->prepare($_GET["order"],NULL) : '';
        if(!empty($orderby) & !empty($order)){
           array_push($sql,sprintf("ORDER BY %s %s",$orderby,$order));
        }
    
        /* -- Pagination parameters -- */
        //Number of elements in your table?
        $totalitems = $wpdb->query(join("\n",$sql)); //return the total number of affected rows
        //How many to display per page?
        $perpage = 20;
        //Which page is this?
        $paged = !empty($_GET["paged"]) ? (int)$_GET["paged"] : 0;
        //Page Number
        if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
        //How many pages do we have in total?
        $totalpages = ceil($totalitems/$perpage);
        //adjust the query to take pagination into account
        if(!empty($paged) && !empty($perpage)){
           $offset=($paged-1)*$perpage;
           array_push($sql,sprintf("LIMIT %d,%d",(int)$offset,(int)$perpage));
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

        ///print join("\n",$sql);

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
            $rec->since = __('Applied','wpwt').": ".strftime('%Y-%m-%d',strtotime($rec->since));
    
            // Pre-define the edit link
            //$editlink  = '/wp-admin/link.php?action=edit&link_id='.(int)$rec->link_id;

            //Open the line
            echo "<tr id=\"record_".$rec->groupID."\" class=\"row-active-".$rec->active."\">\n";
            foreach ( $columns as $column_name => $column_display_name ) {
    
                //Style attributes for each col
                $class = "class='$column_name column-$column_name'";
             
                // Create output in cell
                switch ( $column_name ) {
                    case "col_what":      echo "<td ".$class.">".$this->show_what($rec)."</td>\n"; break;
                    case "col_user":      echo "<td ".$class.">".$this->add_actions($rec)."</td>\n"; break;
                    case "col_groupName": echo "<td ".$class.">".$rec->groupName."</td>\n";   break;
                    case "col_application": echo "<td ".$class.">".$rec->application
                         ."<br><b>".$rec->since."</b></td>\n"; break;
                }
            }
    
           //Close the line
           echo "</tr>\n";
        }}
    }

    // Show what. If active = 1 the user will get out of the
    // group. If active = 9 he just had applied for the job.
    function show_what($item) {
        if ( $item->active ==1 ) {
            return sprintf('<span style="color: red;">%s</span>','Remove');
        } else {
            return sprintf('<span style="color: green;">%s</span>','Apply');
        }
    }

    // Add the edit button to the group entries.
    function add_actions($item) {
        $actions = array(
            'xpprove' => sprintf('<a href="?page=%s&action=%s&active=%d&id=%s">Approve</a>',$_REQUEST['page'],'approve',$item->active,$item->ID),
            'reject'  => sprintf('<a href="?page=%s&action=%s&active=%d&id=%s">Reject</a>',$_REQUEST['page'],'reject',$item->active,$item->ID),
        );
        if ( strcmp( $item->display_name,$item->user_login ) == 0 ) {
            return sprintf('%1$s %2$s', $item->user_login, $this->row_actions($actions) );
        } else {
            return sprintf('%1$s %2$s', $item->user_login.' ['.$item->display_name.'; ID '.$item->userID.']', $this->row_actions($actions) );
        }
    }
}
?>
