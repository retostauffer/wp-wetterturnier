<?php
// ------------------------------------------------------------------
/// @file admin/classes/webcams_list.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief Contains definition of the @ref Wetterturnier_Webcams_List_Table.
/// @details Contains definition of the @ref Wetterturnier_Webcams_List_Table
///   which is used in the wordpress admin backend.
// ------------------------------------------------------------------


class Wetterturnier_Webcams_List_Table extends WP_List_Table {


   /**
    * Constructor, we override the parent to pass our own arguments
    * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
    */
    function __construct() {
       parent::__construct( array(
      'singular'=> 'wetterturnier_webcams_list', //Singular label
      'plural' =>  'wetterturnier_webcams_list', //plural label, also this well be one of the table css class
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
          'col_city'=>__('City','wpwt'),
          'col_source'=>__('Source','wpwt'),
          'col_desc'=>__('Description','wpwt'),
          'col_status'=>__('Webcam Status','wpwt')
       );
    }
    
    /**
     * Decide which columns to activate the sorting functionality on
     * @return array $sortable, the array of columns that can be sorted by the user
     */
    public function get_sortable_columns() {
       return $sortable = array(
          "col_id"=>array("ID",false),
          "col_city"=>array("cityhash",false),
       );
    }
    
    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    function prepare_items() {

        global $wpdb, $_wp_column_headers;
        $screen = get_current_screen();
    
        // Prepare sql query
        $query = "SELECT * FROM ".$wpdb->prefix."wetterturnier_webcams";
    
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
    
            //Open the line
            echo "<tr id=\"record_".$rec->ID."\">\n";
            foreach ( $columns as $column_name => $column_display_name ) {
    
                //Style attributes for each col
                $class = "class='$column_name column-$column_name'";
                $style = "";
                if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
                $attributes = $class . $style;
             
                // Create output in cell
                switch ( $column_name ) {
                    case "col_id":        echo "<td ".$attributes.">".stripslashes($rec->ID)."</td>\n"; break;
                    case "col_city":      echo "<td ".$attributes.">".stripslashes($rec->cityID)."</td>\n";   break;
                    case "col_source":    echo "<td ".$attributes.">".$this->show_webcam($rec)."</td>\n";   break;
                    case "col_desc":      echo "<td ".$attributes.">".stripslashes($rec->desc)."</td>\n";   break;
                    case "col_status":    echo "<td ".$attributes.">".$rec->modified."</td>\n"; break;
                }
            }
    
           //Close the line
           echo "</tr>\n";
        }}
    }

    function show_webcam($item) {

        global $wpdb;
        if ( strlen(stripslashes($item->source)) == 0 ) {
            return "<a href=\"".$item->uri."\" target=\"_blank\">".$item->uri."</a>"
                   .$this->add_actions($item)."\n";
        } else {
            return "<a href=\"".$item->uri."\" target=\"_blank\">".$item->source."</a>"
                   .$this->add_actions($item)."\n";
        }

    }

    // Add the edit button to the group entries.
    function add_actions($item) {
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&cam=%d">Edit</a>',$_REQUEST['page'],'edit',$item->ID),
            'delete'    => sprintf('<a href="?page=%s&action=%s&cam=%d">Delete</a>',$_REQUEST['page'],'delete',$item->ID),
        );
        return sprintf('%s', $this->row_actions($actions) );
    }

}

?>
