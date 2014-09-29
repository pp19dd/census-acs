#!/bin/bash

IFS=' '

limit=1000

echo "--------------------------------"
echo "persons"
echo "--------------------------------"

for f in `./acs.php p`
do
	echo " "
	./acs.php p limit=${limit} format=csv test=${f}
done

echo "--------------------------------"
echo "housing"
echo "--------------------------------"

for f in `./acs.php h`
do
	echo " "
	./acs.php h limit=${limit} format=csv test=${f}
done

