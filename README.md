# inventory
Insecure, extremely simple way to gather a basic inventory of hosts on your network

1. Create a database called "inventory"
2. import the sql into there.
3. in the codebase, there's an includes dir with a database class.  put your username and password in there
4. put the codebase into somewhere that apache can serve up the files (thought that one would be obvious)
5. Put the bash script on a Linux box.  Make sure you can reach all the boxes you want to scan from here.  Put one of these on a jump box for each environment.
6. Put the scan range into the script.
7. Put in the hostname of the server you're going to post all this information to.
8. Put the vbscript on a Windows box.
9. Put one on each jump host such that all servers in all environments can be reached.
10. Put the scan range into the script.
11. Put in the hostname of the server you're going to post all this information to.
