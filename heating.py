#!/usr/bin/python

#
# This is the actual script that decides to start the heating or to stop it
# In my case, this is run on a 4th server (3 Raspberry PI in 3 different rooms reporting to this "server")
#

import datetime
import time
import mysql.connector
import socket

circuit_count=1 # number of circuits
ip=[]
ip.append("192.168.1.2") # switch for 1st circuit

cnx = mysql.connector.connect(user="root", password="a", database="home")
cursor = cnx.cursor()

query = ("select room, dtemp, rtemp, unix_timestamp(rtemp_time),circuit from heating")
cursor.execute(query)

now = int(time.time())
start=[]
for i in range(circuit_count):
    start.append(0)

def status_heating():
    s = socket.socket()
    # *** note to myself
    # this should be changed to connect to the switch of each circuit and get the status
    # i have only one circuit for the moment, so it's fine
    s.connect(("192.168.1.2",8001))
    s.send('status')
    msg=s.recv(1024)
    return str(msg)

for (room, dtemp, rtemp, rtemp_time, circuit) in cursor:
    # looking at live nodes only (updated info in the last 5 mins)
    if (now - rtemp_time) < 300:
        if (dtemp - rtemp) >= 1:
            print time.strftime("%d-%m-%Y %H:%M:%S") + " Counting +1 for circuit - {}".format(circuit)
            start[circuit]=start[circuit]+1
            print (time.strftime("%d-%m-%Y %H:%M:%S") + " {} - {}/{} ({} vs. {}) - {}").format(room, dtemp, rtemp, rtemp_time, now, circuit)
        else:
            print time.strftime("%d-%m-%Y %H:%M:%S") + " Skipping this circuit - {}".format(circuit)
            print (time.strftime("%d-%m-%Y %H:%M:%S") + " {} - {}/{} ({} vs. {}) - {}").format(room, dtemp, rtemp, rtemp_time, now, circuit)
    else:
        print (time.strftime("%d-%m-%Y %H:%M:%S") + " OFFLINE: {} - {}/{} ({} vs. {}) - {}").format(room, dtemp, rtemp, rtemp_time, now, circuit)
    print "---"

cursor.close()
cnx.close()

# Heating status
# 0 - ON
# 1 - OFF

for i in range(len(start)):
    if start[i] > 1:
        status=status_heating()
        if status == "1":
            s = socket.socket()
            s.connect((ip[i],8001))
            s.send('start')
            s.close()
            print time.strftime("%d-%m-%Y %H:%M:%S") + ": I've sent to command to start the heating for circuit {}...".format(i)
        else:
            print time.strftime("%d-%m-%Y %H:%M:%S") + ": Nevermind, the termostat is already on for circuit {}...".format(i)
    else:
        status=status_heating()
        if status == "0":
            s = socket.socket()
            s.connect((ip[i],8001))
            s.send('stop')
            s.close()
            print time.strftime("%d-%m-%Y %H:%M:%S") + ": I've sent the command to stop the heating for circuit {}...".format(i)
        else:
            print time.strftime("%d-%m-%Y %H:%M:%S") + ": Nevermind, the termostat is already off for circuit {}...".format(i)

print "------"
