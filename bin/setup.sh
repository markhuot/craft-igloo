#!/bin/bash

mkdir -p storage
cp vendor/craftcms/craft/.env.example.dev ./.env
cp -r vendor/craftcms/craft/web ./
cp vendor/craftcms/craft/craft ./
chmod +x ./craft
cp vendor/craftcms/craft/bootstrap.php ./
