#!/bin/bash
# Wrapper to run artisan commands with PHP 7.4 deprecation warnings suppressed
php -d error_reporting=22527 artisan "$@"
