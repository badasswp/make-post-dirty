#!/bin/bash

wp-env run cli wp theme activate twentytwentythree
wp-env run cli wp rewrite structure /%postname%
wp-env run cli wp option update blogname "Make Post Dirty"
wp-env run cli wp option update blogdescription "A useful tool for populating the block editor title and content."
