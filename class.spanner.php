<?php

// ===========================================================================
// combines disparate acs5 counters, normalizes them for output
// ===========================================================================
class spanner {
    var $keys = array();
    var $counters = array();
    var $expected_uniques = array();
    var $format = "html";
    
    function addCounters($state, $counters) {
        foreach( $counters as $counter ) {
            if( !isset( $this->keys[$counter->key] ) ) {
                $this->keys[$counter->key] = 0;
            }
            $this->keys[$counter->key]++;
            $this->addCounter($state, $counter);
        }
    }
    
    function addCounter($state, $counter) {
        if( !isset( $this->counters[$state] ) ) {
            $this->counters[$state] = array();
        }
        $this->counters[$state][$counter->key] = $counter->counts;
    }

    // returns all empty values
    function all_values($key) {
        $uniques = array();
        foreach( $this->counters as $state => $counters ) {
            foreach( $counters[$key] as $value => $count ) {
                if( !isset( $uniques[$value] ) ) $uniques[$value] = 0;
                #$uniques[$value]++;
            }
        }
        ksort( $uniques );
        return( $uniques );
    }
    
    // reorder counter parameters
    function normalize() {
        $this->expected_uniques = $this->keys;
        foreach( $this->keys as $key => $dummy ) {
            $this->expected_uniques[$key] = $this->all_values($key);
        }
        
        // at this point $this->expected_uniques is a representation 
        // of keys and vals. use it to recompose $this->counters'
        // values for a clean display
        foreach( $this->counters as $state => $counters ) {
            foreach( $counters as $key => $values ) {
                // array_merge doesn't like numeric keys
                /*$this->counters[$state][$key] = array_merge(
                    $this->expected_uniques[$key],
                    $values
                );*/
                
                $this->counters[$state][$key] = 
                    $values + 
                    $this->expected_uniques[$key];

                ksort( $this->counters[$state][$key] );
            }
        }
    }
    
    function setFormat($format = "html") {
        $this->format = $format;
    }
    
    function output() {
        $this->normalize();
        switch( $this->format ) {
            case 'html': $this->output_html(); break;
            case 'csv': $this->output_csv(); break;
            default:
                printf( "error: format %s not supported\n", $this->format );
                die;
            break;
        }
    }
    
    function output_html() {
?>
<style>
table { border-collapse:collapse }
td, th { padding: 4px }
td { text-align: right }
th { }
.zero { color: rgb(230,230,230); }
</style>
<h4><?php global $argv; echo implode(" ", $argv); ?></h4>
<table border="1">
    <thead>
        <tr>
            <th>state</th>
<?php foreach( $this->expected_uniques as $key => $uniques ) { ?>
            <th colspan="<?php echo count($uniques) ?>"><?php echo $key ?></th>
<?php } ?>
        </tr>
        <tr>
            <th></th>
<?php foreach( $this->expected_uniques as $key => $uniques ) { ?>
<?php     foreach( $uniques as $key2 => $count ) { ?>
            <th><?php echo $key2 ?></th>
<?php     } ?>
<?php } ?>
        </tr>
    </thead>
    <tbody>
<?php foreach( $this->counters as $state => $keys ) { ?>
        <tr>
            <th><?php echo $state ?></th>
<?php     foreach( $keys as $key => $counts ) { ?>
<?php         foreach( $counts as $value_key => $value ) { ?>
            <td<?php if( $value === 0 ) echo ' class="zero"'; ?>><?php echo $value ?></td>
<?php         } ?>
<?php     } ?>
        </tr>
<?php } ?>
    </tbody>
</table>

<?php
        #print_r( $this->counters );
    }
    
    
    function output_csv() {
    
        global $argv;
        echo implode(" ", $argv) . "\n\n";
        
        $hrow = array(" ");
        foreach( $this->expected_uniques as $key => $uniques ) {
            foreach( $uniques as $key2 => $count ) {
                $hrow[] = $key2;
            }
        }
        echo implode("\t", $hrow ) . "\n";

        foreach( $this->counters as $state => $keys ) {
            $row = array();
            $row[] = $state;
            foreach( $keys as $key => $counts ) {
                foreach( $counts as $value_key => $value ) {
                    $row[] = $value;

                }
            }
            echo implode("\t", $row) . "\n";
            unset( $row );
        }
    }
}
