#!/usr/bin/php
<?php
require( "class.argv.php" );
require( "class.census_csv.php" );
require( "class.spanner.php" );

# $arguments = new acs5_argv();

// ===========================================================================
// todo: cli improvements
//        - state refinement (query only certain states.)
// ===========================================================================

$files = acs5_argv::validate_and_get_files();


// ===========================================================================
// loop through files, apply filters / counters, get results
// ===========================================================================
$spanner = new spanner();

$file_count = 0;
foreach( $files as $file ) {
    $demo = new acs5_file($file);
    
    $format = "html";
    
    // p or h are required parameters
    for( $i = 2; $i < $argc; $i++ ) {

        // parse command
        $temp = explode("=", $argv[$i]);
        $directive = array_shift($temp);
        $value = implode("=", $temp);
        
        // is this a command line option, or a filter? or a counter?
        switch( strtolower($directive) ) {
            
            // cli option
            case 'format':
                $spanner->setFormat($value);
            break;
            
            case 'input':
                $input_file = $value . "/" . basename($file);
                if( !file_exists($input_file) ) {
                    printf(
                        "error: input file does not exist: %s\n",
                        $input_file
                    );
                    die;
                }
                $demo->filename = $input_file;
            break;
            
            case 'output':
                if( $value == "data" ) {
                    printf( "error: can't use output=data, reserved\n" );
                    die;
                }

                if( !file_exists($value) ) {
                    printf( "creating folder %s\n", $value );
                    mkdir( $value );
                }
                
                $output_file = $value . "/" . basename($file);
                $demo->setOutput( $output_file );
            break;
            
            case 'limit':
                $demo->setLimit( intval($value) );
            break;
            
            case 'count':
                $demo->setCounter( new acs5_counter($value) );
            break;
            
            case 'group':
                var_dump( $directive );
                var_dump( $value );
                $r = acs5_argv::parse_filter($value);
                //$demo->setGrouping($r);
                die("feature incomplete");
            break;
            
            case 'filter':
                $r = acs5_argv::parse_filter($value);
                $demo->setFilter( new acs5_filter(
                    $r["key"],
                    $r["operator"],
                    $r["value"]
                ));
            break;
            
            default:
            break;
        }
        
    }
    
    $demo->run();
    preg_match("/ss12.(.*)\./", $file, $fr);
    
    $spanner->addCounters( $fr[1], $demo->counters );
    # $demo->output();
    
    $file_count++;
    unset( $demo );
}

$spanner->output();
