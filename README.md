# inventory
Insecure, extremely simple way to gather a basic inventory of hosts on your network
Create a database called "inventory"
import the sql into there.
in the codebase, there's an includes dir with a database class.  put your username and password in there
put the codebase into somewhere that apache can serve up the files
(thought that one would be obvious)

Put the bash script on a Linux box.  Make sure you can reach all the boxes you want to scan from here.  Put one of these on a jump box for each environment.
Put the scan range into the script.
Put in the hostname of the server you're going to post all this information to.

Put the vbscript on a Windows box.
Put one on each jump host such that all servers in all environments can be reached.
Put the scan range into the script.
Put in the hostname of the server you're going to post all this information to.
