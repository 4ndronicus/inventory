On Error Resume Next



' MAKE SURE YOU ADD THE WEBSERVER IP OR HOSTNAME BELOW


' Resolves an IP address to a hostname via DNS rather than WMI
Function rDNS(strIPAddr)		
	Set oShell = CreateObject("WScript.Shell")
	Set oFSO = CreateObject("Scripting.FileSystemObject")
	Set shellexec = oShell.Exec("nslookup " & strIPAddr)
	sResults = LCase( shellexec.StdOut.ReadAll )	
	If InStr(sResults, "name:") Then
		aNameTemp = Split(sResults, "name:")
		aName = Split(Trim(aNameTemp(1)), Chr(13))		
		result = aName(0)
	Else
		result = ""
	End If
	rDNS = result
End Function

' Pads a string with a character up to the specified length
Function LPad (str, pad, length)
	LPad = String(length - Len(str), pad) & str
End Function

' Writes a line to the specified file, prepended with a date and timestamp
Function appendFile( outFile, toWrite )
	
	Dim dt
	dt = Now
	yr = Year(dt)
	mo = LPad(Month(dt), "0", 2)
	dy = LPad(Day(dt), "0", 2)
	
	dateStamp = yr & "-" & mo & "-" & dy
	timeStamp = FormatDateTime(dt, vbLongTime)
	dateTime = dateStamp & " " & timeStamp
	
	Dim fso
	Set fso = CreateObject("Scripting.FileSystemObject")
	Set outputFile = fso.OpenTextFile( outFile, 8, True, 0 )
	outputFile.WriteLine dateTime & " - " & toWrite
	outputFile.Close
	
End Function

' CONSTANTS
'
Const wbemImpersonationLevelImpersonate = 3
Const wbemAuthenticationLevelPktPrivacy = 6
Const vbServerAccessDenied = -2147024891
Const vbServerNotAvailable = -2147023174
Const wbemFlagReturnImmediately = &h10
Const wbemFlagForwardOnly = &h20

' Array containing the potential usernames to get host information
' Add up to 17 total
Dim arrUser(17)
arrUser(0) = ""


' Array containing corresponding passwords to get host information
' Add up to 17 total
Dim arrPassword(17)
arrPassword(0) = ""



Set wshShell = CreateObject( "WScript.Shell" )
strComputerName = wshShell.ExpandEnvironmentStrings( "%COMPUTERNAME%" )
'outFile = strComputerName & "_output.csv"
outputLog = "log.txt"
Dim webServer
webServer = "[WEB SERVER IP OR HOSTNAME]"

appendFile outputLog, "##### => SCRIPT STARTING <= #####"

Dim ipblock
Dim class_b
hostList = Array()


' The network ranges to be scanned
ipblock = "10.30"
class_b = Array( 67, 68, 69, 70, 71, 72, 80, 84, 85 )
For Each i In class_b
	For j = 1 To 255
		ReDim Preserve hostList(UBound(hostList) + 1)
		hostList(UBound(hostList)) = ipblock & "." & i & "." & j
	Next
Next
ipblock = "10.31"
class_b = Array( 128, 129, 130, 131, 132, 133, 134, 135, 136, 139, 140, 141, 144 )
For Each i In class_b
	For j = 1 To 255
		ReDim Preserve hostList(UBound(hostList) + 1)
		hostList(UBound(hostList)) = ipblock & "." & i & "." & j
	Next
Next
ipblock = "172.245
class_b = Array( 16, 17, 18, 19, 27, 28, 88, 89, 90, 91, 92, 93, 120, 121, 129, 192 )
For Each i In class_b
	For j = 1 To 255
		ReDim Preserve hostList(UBound(hostList) + 1)
		hostList(UBound(hostList)) = ipblock & "." & i & "." & j
	Next
Next
ReDim Preserve hostList(UBound(hostList) + 1)
hostList(UBound(hostList)) = "172.24.21.191"
ReDim Preserve hostList(UBound(hostList) + 1)
hostList(UBound(hostList)) = "10.31.15.46"
ReDim Preserve hostList(UBound(hostList) + 1)
hostList(UBound(hostList)) = "10.23.18.87"

For Each strRemoteServer In hostList
	
	appendFile outputLog, "Current host: " & strRemoteServer
	
	If Not strRemoteServer = "" Then
		
		appendFile outputLog, "Next server to check: " & strRemoteServer
		
		appendFile outputLog, "Checking whether host alive: " & strRemoteServer
		
		serverAvailable = "DOWN"
		
'		Set shellexec = WshShell.Exec("ping -n 1 " & strRemoteServer, 0, True)
		Set shellexec = WshShell.Exec("ping -n 1 " & strRemoteServer)
		result = LCase( shellexec.StdOut.ReadAll )
		appendFile outputLog, result
		If InStr(result, "expired in transit") Then
			serverAvailable = "DOWN"
			appendFile outputLog, "Host is down."
		End If
		If InStr(result, "host unreachable") Then
			appendFile outputLog, "Host is down."
			serverAvailable = "DOWN"
		End If
		If InStr(result, "bytes=") Then
			appendFile outputLog, "Host is alive."
			serverAvailable = "UP"
		End If
		
		strHostName=""
		strConnUser=""
		strServerOS=""
		
		appendFile outputLog, "Host is: " & serverAvailable
		
		'If ( serverAvailable = "UP" ) Then
			
			appendFile outputLog, "Attempting to connect to server: " & strRemoteServer & " with username: " & arrUser(0)
			
			' Connect using user and password
			Set objLocator = CreateObject("WbemScripting.SWbemLocator")
			Set objWMI = objLocator.ConnectServer _
			(strRemoteServer, "root\cimv2", ".\" & arrUser(0), arrPassword(0))
			
			If Err.Number <> 0 Then
				appendFile outputLog, "Error: " & Err.Number & " - " & Err.Description
			End If
			
			idxCtr = 0
			Do While ( Err.Number = vbServerAccessDenied And idxCtr < 17 )
				
				Err.Clear
				
				appendFile outputLog, "Trying username " & arrUser(idxCtr)
				
				Set objLocator = Nothing
				Set objWMI = Nothing
				
				Set objLocator = CreateObject("WbemScripting.SWbemLocator")
				Set objWMI = objLocator.ConnectServer _
				(strRemoteServer, "root\cimv2", ".\" & arrUser(idxCtr), arrPassword(idxCtr))
				
				' If the connection attempt failed, do this
				If Err.Number <> 0 Then
					
					appendFile outputLog, Err.Number & " - " & Err.Description
					idxCtr = idxCtr + 1
					
					' If the connection attempt was successful, do this
				ElseIf Err.Number = 0 Then
					
					strConnUser = arrUser(idxCtr)
					
				End If
				
			Loop
			
			If Err.Number = vbServerAccessDenied Then
				
				strServerOS = ""
				strHostName = ""
				strConnUser = ""
				appendFile outputLog, "Error connecting: " & Err.Number & " - " & Err.Description
				serverAvailable = "UP"
				loggedIn = False
				Err.Clear
				
			ElseIf Err.Number = 0 Then
			
				strConnUser = arrUser(idxCtr)
				appendFile outputLog, "Connected successfully with user " & strConnUser
				serverAvailable = "UP"
				loggedIn = True
				
			ElseIf Err.Number = vbServerNotAvailable Then
				
				strServerOS = ""
				strHostName = ""
				strConnUser = ""
				appendFile outputLog, "Error connecting: " & Err.Number & " - " & Err.Description
				'			serverAvailable = "DOWN"
				loggedIn = False
				Err.Clear
				
			End If
			
			If loggedIn = True Then
				
				objWMI.Security_.ImpersonationLevel = wbemImpersonationLevelImpersonate
				objWMI.Security_.AuthenticationLevel = wbemAuthenticationLevelPktPrivacy
				
				appendFile outputLog, "Retrieving the OS information"
				
				' Get OS name
				Set colOS = objWMI.InstancesOf ("Win32_OperatingSystem")
				For Each objOS In colOS
					strName = objOS.Name
					tmpArr = Split( strName, "|" )
					strServerOS = tmpArr(0)
					Set strName = Nothing
					Set tmpArr = Nothing
				Next
				
				appendFile outputLog, "Retrieving hostname"
				
				' Get Host Name
				Set colItems = objWMI.ExecQuery("SELECT * FROM Win32_ComputerSystem", "WQL", wbemFlagReturnImmediately + wbemFlagForwardOnly)
				For Each objItem In colItems
					strHostName = objItem.Name
				Next
				
				appendFile outputLog, "Hostname retrieved: " & strHostName
				
			Else
				' Could not log into the server. Attempt to get the hostname via DNS
				
				appendFile outputLog, "Looking up hostname for IP: " & strRemoteServer
				' Attempt to look up the name via DNS
				strHostName = rDNS( strRemoteServer )
				
				appendFile outputLog, "Hostname returned: " & strHostName
				
			End If
			
		'End If
		
		' Set up and perform the request sending the findings back to the inventory server
'		HTTPRequest = "http://" & webServer & "/maint.php?" & "ipaddr=" & strRemoteServer & "&alive="& serverAvailable & "&hostname=" & strHostName & "&connuser=" & strConnUser & "&os=" & strServerOS & "&connfrom=" & strComputerName
		HTTPRequest = "http://" & webServer & "/maint.php?" & "ipaddr=" & strRemoteServer & "&alive="
		
		' The 'isalive' check doesn't work very well.  If it reports as being UP, it is up.  If it reports as being down, it may still be up.
		if serverAvailable = "UP" then
			HTTPRequest = HTTPRequest & "UP"
		end if
		HTTPRequest = HTTPRequest & "&hostname=" & strHostName & "&connuser=" & strConnUser & "&os=" & strServerOS & "&connfrom=" & strComputerName
		
		Dim o
		Set o = CreateObject("MSXML2.XMLHTTP")
		o.open "GET", HTTPRequest, False
		
		' Send the data back to the inventory server
		appendFile outputLog, "Sending Request: " & HTTPRequest
		o.send
		
		Set strServerOS = Nothing
		Set strHostName = Nothing
		Set strRemoteServer = Nothing
		Set objLocator = Nothing
		Set objWMI = Nothing
		Set colOS = Nothing
		Set objOS = Nothing
		Set HTTPRequest = Nothing
		
	End If
Next

Set wshShell = Nothing

appendFile outputLog, "##### => SCRIPT ENDING <= #####"

WScript.Quit
