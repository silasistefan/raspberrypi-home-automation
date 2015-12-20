#!/usr/bin/python

#
# The switch is the Raspberry PI that switches the heating on or off
# In my case, I have a temperature and humidity sensor on a different GPIO
#
# I also have added this to /etc/rc.local (assuming you're using the same GPIO for the relay and without any # in front):
# echo 3 > /sys/class/gpio/export
# echo "out" > /sys/class/gpio/gpio3/direction
# echo 1 > /sys/class/gpio/gpio3/value
#

import socket
import Adafruit_DHT
import time

s = socket.socket()
s.bind(("192.168.1.2", 8001))

# gpio3 - values (the values here depend on where you connect the wires of the heating)
# 0 - ON
# 1 - OFF

def start_heating():
        with open("/sys/class/gpio/gpio3/value", "r") as file:
                value=file.read().replace('\n', '')

        if str(value) == "0":
                print time.strftime("%d-%m-%Y %H:%M:%S") + ": Heating already started..."
        elif str(value) == "1":
                with open("/sys/class/gpio/gpio3/value", "w") as file:
                        print time.strftime("%d-%m-%Y %H:%M:%S") + ": Heating is stopped... starting it..."
                        file.write("0")
                        file.close()

def stop_heating():
        with open("/sys/class/gpio/gpio3/value", "r") as file:
                value=file.read().replace('\n', '')

        if str(value) == "1":
                print time.strftime("%d-%m-%Y %H:%M:%S") + ": Heating already stopped..."
        elif str(value) == "0":
                with open("/sys/class/gpio/gpio3/value", "w") as file:
                        print time.strftime("%d-%m-%Y %H:%M:%S") + ": Heating is started... stoping it..."
                        file.write("1")
                        file.close()

def status_heating():
        print time.strftime("%d-%m-%Y %H:%M:%S") + ": Reading current status..."
        with open("/sys/class/gpio/gpio3/value", "r") as file:
                value=file.read().replace('\n', '')

        if value == "1":
                print time.strftime("%d-%m-%Y %H:%M:%S") + ": Heating is already OFF..."
        else:
                print time.strftime("%d-%m-%Y %H:%M:%S") + ": Heating is already ON..."
        return str(value)

def get_temp():
        print time.strftime("%d-%m-%Y %H:%M:%S") + ": Reading current temperature..."
        humidity, temperature = Adafruit_DHT.read_retry(22, 2)

        return temperature

s.listen(5)
while True:
        c, addr = s.accept()
        print time.strftime("%d-%m-%Y %H:%M:%S") + ': Got connection from', addr
        msg=c.recv(1024)
        if msg == "start":
                start_heating()
        elif msg == "stop":
                stop_heating()
        elif msg == "status":
                ret=status_heating()
                c.send(ret)
        elif msg == "temperature":
                ret=get_temp()
                c.send(ret)
        else:
                print time.strftime("%d-%m-%Y %H:%M:%S") + ": I don't understand that message"
        c.close
