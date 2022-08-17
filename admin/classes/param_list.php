<?php
// ------------------------------------------------------------------
/// @file admin/classes/param_list.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief Contains definition of the @ref Wetterturnier_Param_List_Table.
/// @details Contains definition of the @ref Wetterturnier_Param_List_Table
///   which is used in the wordpress admin backend.
// ------------------------------------------------------------------


class Wetterturnier_Param_List_Table extends WP_List_Table {


   /**
    * Constructor, we override the parent to pass our own arguments
    * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
    */
    function __construct() {
       parent::__construct( array(
      'singular'=> 'wetterturnier_param_list', //Singular label
      'plural' => 'wetterturnier_param_list', //plural label, also this well be one of the table css class
      'ajax'   => false //We won't support Ajax for this table
      ) );
    }


    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    function get_columns() {
       return $columns= array(
          'col_paramID'=>__('ID','wpwt'),
          'col_paramName'=>__('Name','wpwt'),
          'col_EN'=>__('English (default)','wpwt'),
          'col_DE'=>__('German','wpwt'),
          'col_valformat'=>__('Format','wpwt'),
          'col_vallength'=>__('Maxlen','wpwt'),
          'col_valrange'=>__('Range','wpwt'),
          'col_decimals'=>__('Decimals','wpwt'),
          'col_unit'=>__('Unit','wpwt'),
       );
    }
    
    /**
     * Decide which columns to activate the sorting functionality on
     * @return array $sortable, the array of columns that can be sorted by the user
     */
    public function get_sortable_columns() {
       return $sortable = array(
          "col_paramID"=>array("paramID",false),
          "col_paramName"=>array("paramName",false),
          "col_EN"=>array("EN",false),
          "col_DE"=>array("DE",false)
       );
    }
    
    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    function prepare_items() {

        global $wpdb, $_wp_column_headers;
        $screen = get_current_screen();
    
        // Prepare sql query
        $query = "SELECT * FROM ".$wpdb->prefix."wetterturnier_param";
    
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
            echo "<tr id=\"record_".$rec->paramID."\">\n";
            foreach ( $columns as $column_name => $column_display_name ) {
    
                //Style attributes for each col
                $class = "class='$column_name column-$column_name'";
                $style = "";
                if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
                $attributes = $class . $style;
             
                // Create output in cell
                switch ( $column_name ) {
                    case "col_paramID":   echo "<td ".$attributes.">".stripslashes($rec->paramID)."</td>\n"; break;
                    case "col_paramName": echo "<td ".$attributes.">".$this->add_actions($rec)."</td>\n"; break;
                    case "col_EN":        echo "<td ".$attributes.">".$this->show_desc($rec,'EN')."</td>\n";   break;
                    case "col_DE":        echo "<td ".$attributes.">".$this->show_desc($rec,'DE')."</td>\n";   break;
                    case "col_valformat": echo "<td ".$attributes.">".stripslashes($rec->valformat)."</td>\n";   break;
                    case "col_vallength": echo "<td ".$attributes.">".$rec->vallength."</td>\n";   break;
                    case "col_valrange":  echo "<td ".$attributes.">".($rec->valmin/10.)." to ".($rec->valmax/10.)."</td>\n";   break;
                    case "col_decimals":  echo "<td ".$attributes.">".$rec->decimals."</td>\n";   break;
                    case "col_unit":      echo "<td ".$attributes.">".$rec->unit."</td>\n";   break;
                }
            }
    
           //Close the line
           echo "</tr>\n";
        }}
    }

    // Show description and an info if there is a help entry
    function show_desc($item,$lang) {
        $res = stripslashes($item->$lang)."<br>";
        $help = 'help'.$lang;
        if ( strlen( $item->$help ) > 0 ) {
            $res .= "<i>Parameter help available</i>";
        } else {
            $res .= "<i>No parameter help set</i>";
        }
        return $res;
    }

    // Add the edit button to the group entries.
    function add_actions($item) {
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&param=%d">Edit</a>',$_REQUEST['page'],'edit',$item->paramID),
        );
        return sprintf('%1$s %2$s', $item->paramName, $this->row_actions($actions) );
    }
}

?>
