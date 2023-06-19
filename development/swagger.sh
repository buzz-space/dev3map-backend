#!/bin/bash

mkdir -p ../public/swagger
../vendor/bin/swagger --bootstrap ./swagger-constants.php --output ../public/swagger ./swagger-v1.php ../app/Http/Controllers
