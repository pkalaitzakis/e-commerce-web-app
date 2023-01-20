#!/bin/bash
mongoimport --host mongo_db --authenticationDatabase=admin  --username=root --password=secret --db estore --collection Products --type json --file products.json
