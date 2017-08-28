# inventory
Insecure but extremely simple way to gather a basic inventory of hosts on your network

1. Create a database called "inventory"
2. Import the .sql file into that database.
3. Set up an Apache server with PHP support.
4. In the codebase, there's an includes dir with a database class.  Put your username and password in there.
5. Put the codebase into somewhere that apache can serve up the files (thought that one would be obvious)
6. Put the bash script on a Linux box that can scan all the target hosts, but can also reach the web server created above.  For example, you could put one of these on a jump box for each isolated environment. This script assumes that you have put a public key on each target linux host that will allow the script to log in and gather the information.
7. Put the scan range(s) into the script.
8. Put the hostname of the server you're going to post all this information to into the script.
9. Put the vbscript on a Windows box.  This could be a Windows jump box. You'll need to put credentials into the vbscript that are valid on whatever servers you are scanning.  If you want to try multiple credentials, the script allows for that.  Just add multiple sets of credentials.
10. Put one on each jump host such that all servers in all environments can be reached.
11. Put the scan range into the script.
12. Put in the hostname of the server you're going to post all this information to.
13. The scripts run on the jump hosts, and scan the target host subnet ranges.  They then take the information that they gather and post it back to the web server.
