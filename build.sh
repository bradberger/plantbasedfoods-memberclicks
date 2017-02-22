#!/bin/bash

rm build/*.zip

cp wordpress/wp-content/plugins/memberclicks-auth/* .
zip -r build/plantbasedfoods-memberclicks.zip . -x ".*" -x "build*" -x ".git" -x "*.sh" -x "deployment-config.json" -x "ruleset.xml" -x "wordpress*"
