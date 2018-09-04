#!/bin/bash

# create database
mysqladmin -u root --password=thenerdery create thenerdery
# restore data dump
mysql -u root --password=thenerdery thenerdery < /nerdery/source-data/dump.2008.09.08.sql
# create user
mysql -u root --password=thenerdery thenerdery < /nerdery/scripts/create-user.sql
