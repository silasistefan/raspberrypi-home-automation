#!/usr/bin/env python
#
# I have a Display-O-Tron 3000 connected to the Raspberry PI from my bedroom.
# https://shop.pimoroni.com/products/displayotron-3000
# 
# This is what I'm using to see the status of the room (temperature, etc).
# Initially I used this to control the relay from the Attic, but I added more Raspberry PIs
# and commented out the functions that were actually starting/stopping the heating. The rest
# of the functionality remains.
#
# I didn't define the left and right buttons, but I'm thinking to program them so I could get the stats
# from other rooms. Depending on free time, I might maintain this file or not.
#

print("""
Press CTRL+C to exit.
""")

import Adafruit_DHT
import dothat.touch as j
import dothat.lcd as l
import dothat.backlight as b
import signal
import socket
import time

j.enable_repeat(True)

dtemp=float(21.00)  # temperature that i want to be in the room
rtemp=float(20.00)  # read temperature from sensor
heatc=0.0    # count to light or dim the vertical leds
heat="OFF" # default heating
display_status="ON"
room="Bedroom: "

def status_heating():
	s = socket.socket()
        s.connect(("192.168.1.2",8001))
        s.send('status')
	msg=s.recv(1024)
	return str(msg)

def start_heating():
	print time.strftime("%d-%m-%Y %H:%M:%S") + ": I read " + str(rtemp) + "*C and the room is set for " + str(dtemp) + "*C. I'm trying to start the heating..."
	status=status_heating()
	if status == "1":
		s = socket.socket()
		s.connect(("192.168.1.2",8001))
		#s.send('start')
		s.close()
		print time.strftime("%d-%m-%Y %H:%M:%S") + ": I've sent to command to start the heating..."
	else:
		print "I've read: " + str (status)
		print time.strftime("%d-%m-%Y %H:%M:%S") + ": Nevermind, the termostat is already on..."

def stop_heating():
        print time.strftime("%d-%m-%Y %H:%M:%S") + ": I read " + str(rtemp) + "*C and the room is set for " + str(dtemp) + "*C. I'm trying to stop the heating..."
	status=status_heating()
        if status == "0":
	        s = socket.socket()
        	s.connect(("192.168.1.2",8001))
	        #s.send('stop')
		s.close()
		print time.strftime("%d-%m-%Y %H:%M:%S") + ": I've sent the command to stop the heating..."
	else:
		print "I've read " + str (status)
		print time.strftime("%d-%m-%Y %H:%M:%S") + ": Nevermind, the termostat is already off..."

def get_temp():
	global rtemp
	humidity, temperature = Adafruit_DHT.read_retry(22, 5)
	if humidity is not None and temperature is not None:
		rtemp=float(format(temperature, '.2f'))
		time.sleep(1)

def count_heat():
	global heat,heatc,dtemp,rtemp

	get_temp()
	if (dtemp-rtemp) > 0:
		if heatc < 1:
			heatc=heatc+0.01
			print time.strftime("%d-%m-%Y %H:%M:%S") + ": Counting heating, currently " + str(heatc) + "/1.0"
		else:
			heatc=1.0
			heat="ON"
			start_heating()
	else:
		if heatc > 0:
			heatc=heatc-0.01
			print time.strftime("%d-%m-%Y %H:%M:%S") + ": Counting heating, currently " + str(heatc) + "/0.0"
		else:
			heatc=0.0
			heat="OFF"
			stop_heating()
	if display_status == "ON":
		b.set_graph(heatc)
	else:
		b.set_graph(0)

def default_display():
	global dtemp, rtemp, heat, display_status, heatc
	l.clear()

	if display_status == "ON":
		b.rgb(128,128,128)
	else:
		b.rgb(0,0,0)

	l.set_cursor_position(0,0)
	l.write(str(room) + str(dtemp) + chr(223) + 'C')
	l.set_cursor_position(0,1)
	l.write("Heating: " + str(heat))
	l.set_cursor_position(0,2)
	x= "Now:" + str(rtemp) + chr(223) + 'C ' + time.strftime("%H:%M")
	l.write(x)

	count_heat()
	time.sleep(1)

@j.on(j.UP)
def handle_up(ch, evt):
    global dtemp, rtemp, display_status
    l.clear()
    b.rgb(128, 0, 0) #red
    dtemp=dtemp+1
    l.clear()
    l.set_cursor_position(0,1)
    l.write(str(room) + str(dtemp) + chr(223) + 'C')
    time.sleep(1)
    display_status="ON"
    default_display()

@j.on(j.DOWN)
def handle_down(ch, evt):
    global dtemp, rtemp, display_status
    l.clear()
    b.rgb(128, 128, 0) #blue
    dtemp=dtemp-1
    l.clear()
    l.set_cursor_position(0,1)
    l.write(str(room) + str(dtemp) + chr(223) + 'C')
    time.sleep(1)
    display_status="ON"
    default_display()

@j.on(j.LEFT)
def handle_left(ch, evt):
    print("Left pressed!")
    l.clear()
    b.rgb(0, 0, 255)
    l.write("Leftie left left!")


@j.on(j.RIGHT)
def handle_right(ch, evt):
    print("Right pressed!")
    l.clear()
    b.rgb(0, 255, 255)
    l.write("Rightie tighty!")


@j.on(j.BUTTON)
def handle_button(ch, evt):
    global display_status
    display_status="ON"
    time.sleep(1)
    default_display()

@j.on(j.CANCEL)
def handle_cancel(ch, evt):
    global display_status
    display_status="OFF"
    time.sleep(1)
    default_display()


# Prevent the script exiting!
while 1:
	default_display()
	time.sleep(1)
#signal.pause()
