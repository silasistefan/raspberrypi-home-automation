#!/bin/bash

room_id=3 # change this for each room

rtemp=`/bin/temp_hum.py 11 5 | grep Temp | awk -F= '{print $2;}'` # 11 = sensor type, 5 = gpio

# DB = home
# table = heating
/usr/bin/mysql home -e "update heating set rtemp=${rtemp}, rtemp_time=now() where id=${room_id}"
