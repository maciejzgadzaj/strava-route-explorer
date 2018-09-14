#!/usr/bin/env bash

bin/console explorer:meetup:scrap --end_date=$(date +%F -d "+10 days")
bin/console explorer:athlete:sync
