<?php

/* ##########################################################################################################################################
  dbConn class - For database connection/query management
  by Scott Morris
  smmorris@gmail.com
  May 30, 2004
  Copyright © 2004 - 2013 by Scott Morris – All Rights Reserved

  Updated December 2007
  ---------------------
  public instead of var member variables
  'or die' error-handling added to critical functionality
  renamed member functions to better names
  $this->dbconn added to mysqli_select_db and mysqli_query functions for more accurate connection handling

  Updated October 2013
  --------------------
  Modernized member variables by specifying 'public' or 'private' where necessary
  Implemented PHPDoc comments
  ############################################################################################################################################

  This is a class designed to make database connections less painful. The
  variables are documented below. The functions are documented as well. A small
  example is as follows:


  //FOR A QUERY THAT WILL RETURN ROWS (A 'SELECT', FOR EXAMPLE):

  $myquery="select * from table";
  $databaseObj=new dbConn();
  $results=$databaseObj->doSel($myquery);

  //$results NOW CONTAINS AN ARRAY OF THE RESULTS OF THE QUERY
  //$databaseObj->numrows CONTAINS THE NUMBER OF ROWS RETURNED


  //FOR A QUERY THAT WILL NOT RETURN ANY ROWS (INSERT, UPDATE, DELETE, DROP, CREATE, ETC):

  $myquery="insert into table(col1,col2,col3) values('string','string','string');
  $databaseObj=new dbConn();
  $result=$databaseObj->doExec($myquery);

  //$result NOW CONTAINS EITHER A TRUE (QUERY SUCCESSFUL) OR A FALSE (NOT SUCCESSFUL)
  //IF AN 'INSERT' STATEMENT, $databaseObj->insert_id NOW CONTAINS THE ID OF THE LAST ROW INSERTED

  ########################################################################################################################################## */

class dbConn {

    /**
     * $dbserver - The server to which we are going to connect
     * @var string
     */
    private $dbserver;

    /**
     * $dbuser - The user account that we are going to use
     * @var string
     */
    private $dbuser;

    /**
     * $dbpass - The password that we are going to use in our connection
     * @var string
     */
    private $dbpass;

    /**
     * $dbname - The name of the database to which we are going to connect
     * @var string
     */
    public $dbname;

    /**
     * $dbconn - Holds the actual database connection - public does not need to see
     * @var database connection
     */
    private $dbconn;

    /**
     * $numrows - Tells us how many rows are in the result of our query
     * @var integer
     */
    public $numrows;

    /**
     * $mode - WHAT MODE THE CLASS IS IN. 'NORMAL'(TRUE) MODE MEANS THAT THE
     * CLASS WILL AUTOMATICALLY CREATE AND CLOSE THE CONNECTION.
     * 'MANUAL'(FALSE) MODE MEANS THAT THE USER WILL CREATE AND CLOSE
     * THE CONNECTION. 'NORMAL' (TRUE) IS THE DEFAULT.
     * @var boolean
     */
    public $mode;

    /**
     * $insert_id - Used to retrieve the id of a newly inserted row
     * @var integer
     */
    public $insert_id;

    /**
     * Database object (dbConn) constructor.  Initializes the database connection
     * with server, user, password, and database name settings.
     */
    function __construct() {
        $this->dbserver = "localhost";
        $this->dbuser = "root";
        $this->dbpass = "!@MountainF0551l#$";
        $this->dbname = "vulns";
        $this->numrows = 0;
        $this->mode = true;
    }

    /**
     * openConn() - Opens the connection to the database and verifies that the
     * connection opened.  Selects the database against which to query.  Most
     * of the time, this function is called automatically, unless the object
     * is in 'MANUAL' (false) mode, as described in the $mode variable description.
     */
    function openConn() {
        //CONNECT TO THE DATABASE
        if (!$this->dbconn = mysqli_connect($this->dbserver, $this->dbuser, $this->dbpass)) {
            die("Failed to connect to DB server\n" . mysqli_error($this->dbconn));
        }
        //SELECT THE DATABASE CONNECTION FOR USE
        if (!mysqli_select_db($this->dbconn, $this->dbname)) {
            die("Failed to connect to database\n" . mysqli_error($this->dbconn));
        }
    }

    /**
     * closeConn() - Closes the connection to the database.  Most of the time,
     * this function is called automatically, unless the object is in 'MANUAL'
     * or 'false' mode, as described in the $mode member variable description.
     */
    function closeConn() {
        mysqli_close($this->dbconn);
    }

    /**
     * doSel($sqlquery) - This is the function used for <b>SELECT</b> queries.  The
     * resulting data is put into an array which is returned. If the query fails,
     * a value of <b><i>false</i></b> is returned, so that the user can check for a failed query.
     *
     * @param string $sqlquery - the 'select' query to run
     * @return multiple array of results OR <b><i>false</i></b>
     */
    function doSel($sqlquery) {
        $this->insert_id = -1; // ZERO THIS OUT SO THAT IT DOESN'T CARRY OVER THROUGH CONSECUTIVE QUERIES
        $this->numrows = 0; // ZERO THIS OUT SO THAT IT DOESN'T CARRY OVER THROUGH CONSECUTIVE QUERIES
        //IF WE ARE IN 'NORMAL'(TRUE) MODE, GO AHEAD AND CREATE THE CONNECTION.
        //IF WE ARE IN 'MANUAL'(FALSE) MODE, DO NOT CREATE THE CONNECTION.
        if ($this->mode) {
            $this->openConn();
        }
        //RUN THE QUERY AND STORE THE RESULTS
        $result = mysqli_query($this->dbconn, $sqlquery);
        if ($result === false) {
            debuglog("QUERY FAILED: $sqlquery");
            $out = var_dump(mysqli_error($this->dbconn), true);
            debuglog("MySQL Error: $out");
            return false;
        }
        //WE DON'T ALWAYS WANT TO CLOSE THE DATABASE AFTER A QUERY.
        //IN THE CASE OF FOR LOOPS, WE WILL CLOSE THE CONNECTION AFTER
        //THE LOOP HAS COMPLETED.
        if ($this->mode) {
            mysqli_close($this->dbconn);
        } //CLOSE THE DATABASE
        //RETRIEVE THE NUMBER OF ROWS IN THE RESULT
        if ($result !== false) {
            $this->numrows = mysqli_num_rows($result);
        }
        if ($this->numrows > 0) { //Place all of the data into an array
            for ($x = 0; $x < $this->numrows; $x++) {
                $returndata[] = mysqli_fetch_assoc($result);
            }
            return $returndata; //RETURN THE RESULTS
        } else {
            return false; //RETURN A VALUE OF 'FALSE'
        }
    }

    /**
     * This is the function used for queries that do not return data.  This
     * might be inserts, updates, deletes, etc.
     *
     * @param string $sqlquery - contains the query to run
     * @return boolean
     */
    function doExec($sqlquery) {
        $this->insert_id = -1; // ZERO THIS OUT SO THAT IT DOESN'T CARRY OVER THROUGH CONSECUTIVE QUERIES
        $this->numrows = 0; // ZERO THIS OUT SO THAT IT DOESN'T CARRY OVER THROUGH CONSECUTIVE QUERIES
        if ($this->mode) { //CONNECT TO THE DATABASE
            if (!$this->dbconn = mysqli_connect($this->dbserver, $this->dbuser, $this->dbpass)) {
                die("Failed to connect\n" . mysqli_error($this->dbconn));
            }
            //SELECT THE DATABASE CONNECTION FOR USE
            if (!mysqli_select_db($this->dbconn, $this->dbname)) {
                die("Failed to connect to database\n" . mysqli_error($this->dbconn));
            }
        }
        $result = mysqli_query($this->dbconn, $sqlquery); //RUN THE QUERY AND STORE THE RESULTS
        //GRAB THE ID IF SOMETHING WAS JUST INSERTED.
        $sqlquery_lower = strtolower($sqlquery);
        if (strstr($sqlquery_lower, "insert")) {
            $this->insert_id = mysqli_insert_id($this->dbconn);
        }

        //WE DON'T ALWAYS WANT TO CLOSE THE DATABASE.  IN THE CASE OF FOR LOOPS,
        //WE SHOULD WAIT UNTIL THE LOOP HAS TERMINATED
        if ($this->mode) {
            mysqli_close($this->dbconn);
        } //CLOSE THE DATABASE
        //IF WE WERE SUCCESSFUL, RETURN A TRUE, IF NOT, RETURN A FALSE
        if ($result === true) {
            return(true);
        } else {
            debuglog("Query failed: $sqlquery");
            debuglog(mysqli_error($this->dbconn));
            return(false);
        }
    }

}

?>
