<?php

// ===========================================================================
// example usage
// ===========================================================================
// $demo = new acs5_file("data/ss12pdc.csv");
// $demo->setFilter( new acs5_filter("SEX", "1") );
// $demo->setFilter( new acs5_filter("ENG", "2") );
// $demo->setCounter( new acs5_counter("WAOB") );
// $demo->setLimit(1000);
// $demo->run();

// ===========================================================================
// simple value filter, no range
// ===========================================================================
class acs5_filter {
    var $key = "";
    var $oper = "";
    var $val = "";
    
    var $csv_headers = null;
    var $counts = array();
    
    function __construct($key, $operator = null, $value = null) {
        $this->key = $key;
        $this->oper = $operator;
        $this->val = $value;
    }
    
    function key_to_num($k) {
        return( $this->csv_headers[$k] );
    }
    
    function evaluate_symbol($value, $operator, $test_against) {
        $value = intval($value);
        $test_against = intval($test_against);
        switch( $operator ) {
            case '=':  if( $value === $test_against ) return(true); break;
            case '<':  if( $value < $test_against ) return(true); break;
            case '>':  if( $value > $test_against ) return(true); break;
            case '<=': if( $value <= $test_against ) return(true); break;
            case '>=': if( $value >= $test_against ) return(true); break;
        }
        return( false );
    }
    
    function evaluate($row, $grouping = array()) {
        $num = $this->key_to_num($this->key);
        $act = trim($row[$num-1]);
        
        if( !is_array($this->val) ) {
            $compare_value = array($this->val);
        } else {
            $compare_value = $this->val;
        }

        foreach( $compare_value as $value ) {
            if( $this->evaluate_symbol($act, $this->oper,$value) === true ) {
                return( true );
            }
        }
        return( false );
    }
}

// ===========================================================================
// simple frequency counter
// ===========================================================================
class acs5_counter extends acs5_filter {
    function incval($k) {
        if( !isset( $this->counts[$k] ) ) {
            $this->counts[$k] = 0;
        }
        $this->counts[$k]++;
    }
    
    function getval(&$row) {
        $num = $this->key_to_num($this->key);
        $this->val = $row[$num-1];
    }
    
    function evaluate($row, $grouping = array()) {
        $this->getval($row);
        if( isset($grouping[$this->key]) ) {
            #echo $this->key;
            $parm = $grouping[$this->key];
            
            if( is_array($parm)) {
            
            } else {
                $step_size = intval($parm);
                $this->val = intval($this->val / $step_size);
            }
        }
        $this->incval( $this->val);
    }
}

// ===========================================================================
// handles a file, registers filters / counters, runs query
// ===========================================================================
class acs5_file {
    var $filename = "";
    var $output_filename = null;
    var $fp_output = null;
    
    var $csv_headers = array();
    var $filters = array();
    var $counters = array();
    var $debug = false;
    var $limit = 0;
    var $grouping = array();
    
    function __construct( $filename ) {
        $this->filename = $filename;
        $this->get_headers();
    }

    function __destruct() {
        if( is_null($this->output_filename) ) return(false);
        
        fclose( $this->fp_output );
    }
    
    function setLimit($rows = 0) {
        $this->limit = $rows;
    }
    
    function get_headers() {
        $fp = fopen($this->filename, "rt");
        $r = array_merge( array("--EMPTY--" ), fgetcsv($fp) );
        fclose($fp);
        
        $this->csv_headers = array_flip($r);
    }
    
    function setFilter($f) {
        $f->csv_headers = $this->csv_headers;
        array_push( $this->filters, $f );
    }

// $r variant 1:  [key] => AGEP       [operator] => ,     [value] => 5
// $r variant 2:  [value] => Array([0] => 0-20  [1] => 21-30   [2] => 51
    function setGrouping($r) {
        $this->grouping[$r["key"]] = $r["value"];
    }
    
    function setCounter($c) {
        $c->csv_headers = $this->csv_headers;
        array_push( $this->counters, $c );
    }
    
    // sets output file, opens it
    function setOutput($f) {
        $this->output_filename = $f;
        $this->fp_output = fopen($this->output_filename, "wt"); 
    }
    
    function run() {
        $count = 0;
        $fp = fopen($this->filename, "rt");
        
        $dummy_header = fgetcsv($fp);
        
        while( $r = fgetcsv($fp) ) {
            
            // any combination of filters skips a row
            $skip = false;
            foreach( $this->filters as $filter ) {
                if( $filter->evaluate($r) === false ) $skip = true;
            }
            if( $skip === true ) continue;
            
            // are we writing this anywhere?
            if( !is_null($this->output_filename) ) {
                fputcsv($this->fp_output, $r);
            }
            
            // any non-skipped rows get counted
            foreach( $this->counters as $counter ) {
                $counter->evaluate($r, $this->grouping);
            }
            
            // limit # of rows, for debugging purposes
            if( $this->limit > 0 ) {
                $count++;
                if( $count >= $this->limit ) break;
            }
        }
        fclose( $fp );
    }
    
    function output() {
        echo "\n--------------------\n{$this->filename}\n--------------------\n";
        foreach( $this->counters as $counter ) {
            ksort( $counter->counts );
            echo $counter->key . " ";
            print_r( $counter->counts );
        }
    }
    
    function display_headers() {
        foreach( $this->csv_headers as $header => $num ) {
            echo str_pad( $header, 10 );
        }
        echo "\n";
    }
}
