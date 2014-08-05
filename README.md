census-acs
==========

Command line query tools for the U.S. Census Bureau's ACS dataset (American Community Survey), written in PHP. Performs rudimentary filtering, counting and display.

To setup, first download the ACS5 dataset from http://www2.census.gov/acs2012_5yr/pums/, the CSV version, and place them in a data/ folder. Place the PHP files there (or whever handily accessible) and `chmod +X acs.php`.  The ACS files are split into two categories - person and housing.

         +------- state
         |
    csv_h*.zip       Housing files
    csv_p*.zip       Person files

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

Because these CSV files decompress to about 20 gigabytes, you can create and use a reduced set.

`./acs.php filter="SEX=1" output=male` - will create a male folder, and place all surveyed male records into it.

When querying that dataset, do `./acs.php input=male count=ENG`
