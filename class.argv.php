<?php

class acs5_argv {

    function usage($argv = array()) {
        $f = $argv[0];
        
        echo "\n";
        echo " usage: {$f} [p|h] filter=[key,condition] count=[key]\n";
        echo "    ex: {$f} p\n";
        echo "    ex: {$f} p filter=ENG,2 count=WAOB\n";
        echo "    ex: {$f} p filter=ENG,2 count=WAOB limit=30\n";
        echo "    ex: {$f} p filter=ENG,2 output=NEWFOLDER\n";
        echo "    ex: {$f} p input=SOMEFOLDER filter=AGE>30\n\n";
        
        die;
    }

    // =======================================================================
    // p or h are required parameters: get file list if everything is O.K.
    // =======================================================================
    function validate_and_get_files() {
        global $argc;
        global $argv;
        
        if( $argc > 1 ) {
            $set = strtolower($argv[1]);
            if( $set != "p" && $set != "h" ) acs5_argv::usage($argv);
            $files = glob("data/ss12{$set}*.csv");
        } else {
            acs5_argv::usage($argv);
        }

        // ===================================================================
        // display headers from either P or H set
        // ===================================================================
        if( $argc == 2 ) {
            $demo = new acs5_file($files[0]);
            unset( $demo->csv_headers["--EMPTY--"] );
            $demo->display_headers();
            die;
        }
        
        return( $files );
    
    }
    
    function parse_filter($value) {
        // cases: ENG>2  ENG <2  ENG >= 23 ENG <= 24
        $m = preg_match("/(.*?)([<=|>=|=|<|>|,]+)(.*)/i", $value, $r);
        
        // cases: ENG=2,3
        if( stripos($r[3], ",") !== false ) $r[3] = explode(",", $r[3]);
        
        return( array(
            "key" => $r[1],
            "operator" => $r[2],
            "value" => $r[3]
        ));
    }
}
