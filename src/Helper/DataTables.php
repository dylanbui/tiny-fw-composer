<?php

namespace TinyFw\Helper;

class DataTables {

//  Tao truy van DataTables https://www.datatables.net/
//  https://legacy.datatables.net/examples/data_sources/server_side.html

    // -- $object co the truyen vao la 1 callback function --
    public static function loadDataWithOption($object, $aColumns ,$defaultSort)
	{
//        $aColumns = array( 'user_id', 'username', 'full_name', 'email', 'mobile', 'active', 'user_id');

        // -- Paging --
        $offset = ""; $items_per_page = "";
        if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
        {
            $offset = $_GET['iDisplayStart'];
            $items_per_page = $_GET['iDisplayLength'];
        }

        // -- Ordering --
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            $sOrder = "  ";
            for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
            {
                if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
                {
                    $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
				 	".real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
                }
            }

            $sOrder = substr_replace( $sOrder, "", -2 );
            if (empty(trim($sOrder)))
            {
                $sOrder = NULL;
            }
        }

        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = NULL;
        if ( $_GET['sSearch'] != "" )
        {
            $sWhere = "(";
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                $sWhere .= $aColumns[$i]." LIKE '%".real_escape_string( $_GET['sSearch'] )."%' OR ";
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
        }

        /* Individual column filtering */
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
            {
                if ( $sWhere == "" )
                {
                    $sWhere = "WHERE ";
                }
                else
                {
                    $sWhere .= " AND ";
                }
                $sWhere .= $aColumns[$i]." LIKE '%".real_escape_string($_GET['sSearch_'.$i])."%' ";
            }
        }

        // -- Neu object la ham callback --
        if (is_callable($object))
        {
            $returnVal = call_user_func($object, $sWhere, $sOrder, $offset, $items_per_page);
            $totalRecord = $returnVal['totalRecord'];
            $rowSets = $returnVal['rowSets'];
        } else {
            $totalRecord = $object->getTotalRow($sWhere);
            $sOrder = empty($sOrder) ? $defaultSort : $sOrder;
            $rowSets = $object->getRowSet($sWhere, array(), $sOrder, $offset, $items_per_page);
        }

        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $totalRecord,
            "iTotalDisplayRecords" => $totalRecord, //$iFilteredTotal,
            "aaData" => array()
        );

        foreach ($rowSets as $aRow) {
            $row = array();
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                if ( $aColumns[$i] == "version" )
                {
                    /* Special output formatting for 'version' column */
                    $row[] = ($aRow[ $aColumns[$i] ]=="0") ? '-' : $aRow[ $aColumns[$i] ];
                }
                else if ( $aColumns[$i] != ' ' )
                {
                    /* General output */
                    $row[] = $aRow[ $aColumns[$i] ];
                }
            }
            $output['aaData'][] = $row;
        }
        return json_encode($output);
	}


} 

