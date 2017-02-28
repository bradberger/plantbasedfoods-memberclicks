#!/bin/bash

rm build/*.zip

#cp wordpress/wp-content/plugins/plantbasedfoods-member-directory/* .
zip -r build/plantbasedfoods-member-directory.zip . -x ".*" -x "build*" -x ".git" -x "*.sh" -x "deployment-config.json" -x "ruleset.xml" -x "wordpress*"
