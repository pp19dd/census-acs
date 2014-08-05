census-acs
==========

Command line query tools for the U.S. Census Bureau's ACS dataset (American Community Survey)

To setup, first download the ACS5 dataset, CSV version, place them in the data/ folder. Then, chmod +X acs.php

Usage examples:

`./acs.php h` - will show all housing headers from the ACS5 dataset. 

`./acs.php h count=TOIL format=csv` - will show all households with running toilets.

                    1       2
    ak      7       83      10
    al      5       93      2
    ar      8       88      4
    az      4       93      3
    ca      3       97      0
    co      5       95      0
    ...
