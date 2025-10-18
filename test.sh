#!/bin/bash
# Run tests, remove HTML from inline and separate lines
docker-compose run --rm app vendor/bin/phpunit "$@" 2>&1 | \
  sed -u 's/<!DOCTYPE[^$]*//' | \
  grep --line-buffered -v "Container lords" | \
  grep --line-buffered -vE "^[[:space:]]*<" | \
  grep --line-buffered -v "^[[:space:]]*$"
