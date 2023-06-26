#!/bin/bash

# this script should be executed from within docker container mubu_apache

chown 1000:1000 . -Rf
rm var/cache/dev var/cache/test var/cache/prod var/sessions/* var/spool.mails/* -Rf
chown 1000:1000  /root/.composer/ -Rf
chmod 777 var/cache/ -Rf
chmod 777 var/log/ -Rf