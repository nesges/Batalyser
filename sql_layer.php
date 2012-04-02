<?

if (eregi("sql_layer.php",$_SERVER['PHP_SELF'])) {
	header("Location: index.php");
	die();
}

if(! isset($dbhost) && ! isset($dbhost2)) {
    include("dbconfig.php");
}
$dbi = array();

if($sql_layer_database_mode=='new') {
    $dbi[1] = sql_connect($db[$sql_layer_database]['host'], $db[$sql_layer_database]['user'], $db[$sql_layer_database]['pass'], $db[$sql_layer_database]['db'], 1);
} else {
    if($sql_layer_database==1) {
        $dbi[1] = sql_connect($dbhost, $dbuname, $dbpass, $dbname, 1);
    } else if($sql_layer_database!=2) {
        $dbi[1] = sql_connect($dbhost, $dbuname, $dbpass, $dbname, 1);
        $dbi[2] = sql_connect($dbhost2, $dbuname2, $dbpass2, $dbname2, 2);
    } else {
       $dbi[1] = sql_connect($dbhost2, $dbuname2, $dbpass2, $dbname2, 1);
    }
}

/* $dbtype = "MySQL"; */
/* $dbtype = "mSQL"; */
/* $dbtype = "PostgreSQL"; */
/* $dbtype = "PostgreSQL_local";// When postmaster start without "-i" option. */
/* $dbtype = "ODBC"; */
/* $dbtype = "ODBC_Adabas"; */
/* $dbtype = "Interbase"; */
/* $dbtype = "Sybase"; */

class ResultSet {
	var $result;
	var $total_rows;
	var $fetched_rows;

	function set_result( $res ) {
		$this->result = $res;
	}

	function get_result() {
		return $this->result;
	}

	function set_total_rows( $rows ) {
		$this->total_rows = $rows;
	}

	function get_total_rows() {
		return $this->total_rows;
	}

	function set_fetched_rows( $rows ) {
		$this->fetched_rows = $rows;
	}

	function get_fetched_rows() {
		return $this->fetched_rows;
	}

	function increment_fetched_rows() {
		$this->fetched_rows = $this->fetched_rows + 1;
	}
}

/*
 * sql_connect($host, $user, $password, $db)
 * returns the connection ID
 */

function sql_connect($host, $user, $password, $db, $dbno=1) {
	global $PHP_SELF, $dbtype, $dbi, $sql_stats;

	switch ($dbtype) {
		case "MySQL":
			$dbi[$dbno]=mysql_connect($host, $user, $password);
			mysql_select_db($db);
			break;
		
		case "mSQL":
			$dbi[$dbno]=msql_connect($host);
			msql_select_db($db);
			break;
		
		case "PostgreSQL":
			$dbi[$dbno]=pg_connect("host=$host user=$user password=$password port=5432 dbname=$db");
			break;
		
		case "PostgreSQL_local":
			$dbi[$dbno]=pg_connect("user=$user password=$password dbname=$db");
			break;
		
		case "ODBC":
			$dbi[$dbno]=odbc_connect($db,$user,$password);
			break;
		
		case "ODBC_Adabas":
			$dbi[$dbno]=odbc_connect($host.":".$db,$user,$password);
			break;
		
		case "Interbase":
			$dbi[$dbno]=ibase_connect($host.":".$db,$user,$password);
			break;
		
		case "Sybase":
			$dbi[$dbno]=sybase_connect($host, $user, $password);
			sybase_select_db($db,$dbi[$dbno]);
			break;
		
		case "Oracle":
			$dbi[$dbno]=ora_logon("$user@$host", $password);
			ora_commiton($dbi[$dbno]);
			break;
	}
	return $dbi[$dbno];
}

function sql_logout($id=0, $dbno=1) {
	global $PHP_SELF, $dbtype, $dbi;
	if($id==0) {
		$id = $dbi[$dbno];
	}
	switch ($dbtype) {
		case "MySQL":
			$dbi[$dbno]=mysql_close($id);
			break;
		
		case "mSQL":
			$dbi[$dbno]=msql_close($id);
			break;
		
		case "PostgreSQL":
		case "PostgreSQL_local":
			$dbi[$dbno]=pg_close($id);
			break;
		
		case "ODBC":
		case "ODBC_Adabas":
			$dbi[$dbno]=odbc_close($id);
			break;
		
		case "Interbase":
			$dbi[$dbno]=ibase_close($id);
			break;
		
		case "Sybase":
			$dbi[$dbno]=sybase_close($id);
			break;
		
		case "Oracle":
			$dbi[$dbno]=ora_logoff($id);
			break;
	}
	return $dbi[$dbno];
}


/*
 * sql_query($query, $id)
 * executes an SQL statement, returns a result identifier
 */

function sql_query($query, $id=0, $count=1, $dbno=1) {
	global $PHP_SELF, $dbtype, $sql_debug, $lastquery, $dbi, $prefix, $sql_stats, $sql_stats_conn;

	
	$log = fopen("/homepages/10/d26844295/htdocs/sql.log", "a");
	fwrite($log, "-- \n".date("Y-m-d H:i:s")." ".preg_replace('#/kunden/homepages/10/d26844295/htdocs/#', '', $_SERVER['SCRIPT_FILENAME'])."\n");
	fwrite($log, $query."\n");
	fclose($log);
	
	if($sql_stats == 1 && $count==1) {
		if(preg_match("/(update|insert|select)/i", $query, $matches)) {
			$type = strtolower($matches[1]);
 			sql_query("update $prefix"."_sql_stats set query_$type = query_$type + 1", $sql_stats_conn, 0, $dbno);
		}
	}
	if($sql_debug && $count==1) {
		echo "SQL query: ".str_replace(",",", ",$query)."<BR>";
	}
	$lastquery = $query;
	
	if($id==0) {
		$id = $dbi[$dbno];
	}

	switch ($dbtype) {
		case "MySQL":
			$res=mysql_query($query, $id);
			break;
		
		case "mSQL":
			$res=msql_query($query, $id);
			break;
		
		case "PostgreSQL":
		case "PostgreSQL_local":
			$res=pg_exec($id,$query);
			$result_set = new ResultSet;
			$result_set->set_result( $res );
			$result_set->set_total_rows(sql_num_rows( $result_set ));
			$result_set->set_fetched_rows( 0 );
			$res = $result_set;
			break;
		
		case "ODBC":
		case "ODBC_Adabas":
			$res=odbc_exec($id,$query);
			break;
		
		case "Interbase":
			$res=ibase_query($id,$query);
			break;
		
		case "Sybase":
			$res=sybase_query($query, $id);
			break;
		
		case "Oracle":
			$res = ora_open($id);
			ora_parse($res, $query);
  			ora_exec($res);
	}
	echo sql_error();
	return $res;
}

/*
 * sql_num_rows($res)
 * given a result identifier, returns the number of affected rows
 */

function sql_num_rows($res, $count=1) {
	global $PHP_SELF, $dbtype, $prefix, $sql_stats, $sql_stats_conn;
	
	if($sql_stats == 1 && $count==1) {
		sql_query("update $prefix"."_sql_stats set num_rows=num_rows+1", $sql_stats_conn, 0, $dbno);
	}
	
	switch ($dbtype) {
		case "MySQL":
			$rows=mysql_num_rows($res);
			break;
		
		case "mSQL":
			$rows=msql_num_rows($res);
			break;
		
		case "PostgreSQL":
		case "PostgreSQL_local":
			$rows=pg_numrows( $res->get_result() );
			break;
		
		case "ODBC":
		case "ODBC_Adabas":
			$rows=odbc_num_rows($res);
			break;
		
		case "Interbase":
			echo "<BR>Error! PHP dosen't support ibase_numrows!<BR>";
			break;
		
		case "Sybase":
			$rows=sybase_num_rows($res);
			break;
		
		case "Oracle":
			$rows = ora_numrows($res);
			break;
	}
	return $rows;
}

/*
 * sql_fetch_row($res,$row)
 * given a result identifier, returns an array with the resulting row
 * Needs also a row number for compatibility with PostgreSQL
 */

function sql_fetch_row($res, $nr=0, $count=1, $dbno=1) {
	global $PHP_SELF, $dbtype, $lastquery, $dbi, $prefix, $sql_stats, $sql_stats_conn;
	
	if($sql_stats == 1 && $count==1) {
		sql_query("update $prefix"."_sql_stats set fetch_row=fetch_row+1", $sql_stats_conn, 0, $dbno);
	}
	
	if($nr==0) {
		$nr = $dbi[$dbno];
	}
	
	switch ($dbtype) {
		case "MySQL":
			$row = mysql_fetch_row($res);
			if(mysql_errno() != 0) {
				echo "SQL-ERROR: ".mysql_error()." in <i>$lastquery</i>";;
			}
			break;
		
		case "mSQL":
			$row = msql_fetch_row($res);
			break;
		
		case "PostgreSQL":
		case "PostgreSQL_local":
			if( $res->get_total_rows() > $res->get_fetched_rows() ) {
				$row = pg_fetch_row($res->get_result(), $res->get_fetched_rows() );
				$res->increment_fetched_rows();
			} else {
				return false;
			}
			break;
		
		case "ODBC":
		case "ODBC_Adabas":
			$row = array();
			$cols = odbc_fetch_into($res, $nr, $row);
			break;
		
		case "Interbase":
			$row = ibase_fetch_row($res);
			break;
		
		case "Sybase":
			$row = sybase_fetch_row($res);
			break;
		
		case "Oracle":
			ora_fetch_into($res, $row, ORA_FETCHINTO_NULLS );
			break;
	}
	return $row;
}

/*
 * sql_fetch_array($res,$row)
 * given a result identifier, returns an associative array
 * with the resulting row using field names as keys.
 * Needs also a row number for compatibility with PostgreSQL.
 */

function sql_fetch_array($res, $nr=0, $count=1, $dbno=1) {
	global $PHP_SELF, $dbtype, $dbi, $prefix, $sql_stats, $sql_stats_conn;
	
	if($sql_stats == 1 && $count==1) {
		sql_query("update $prefix"."_sql_stats set fetch_array=fetch_array+1", $sql_stats_conn, 0, $dbno);
	}
	
	if($nr==0) {
		$nr = $dbi[$dbno];
	}
	
	switch ($dbtype) {
		case "MySQL":
			$row = array();
			$row = mysql_fetch_array($res);
			break;
		
		case "mSQL":
			$row = array();
			$row = msql_fetch_array($res);
			break;
		
		case "PostgreSQL":
		case "PostgreSQL_local":
			if( $res->get_total_rows > $res->get_fetched_rows() ) {
				$row = array();
				$row = pg_fetch_array($res->get_result(), $res->get_fetched_rows() );
				$res->increment_fetched_rows();
			} else {
				return false;
			}
			break;
		
		/*
		 * ODBC doesn't have a native _fetch_array(), so we have to
		 * use a trick. Beware: this might cause HUGE loads!
		 */
		case "ODBC":
			$row = array();
			$result = array();
			$result = odbc_fetch_row($res, $nr);
			$nf = odbc_num_fields($res); /* Field numbering starts at 1 */
			for($count=1; $count < $nf+1; $count++) {
				$field_name = odbc_field_name($res, $count);
				$field_value = odbc_result($res, $field_name);
				$row[$field_name] = $field_value;
			}
			break;
		
		case "ODBC_Adabas":
			$row = array();
			$result = array();
			$result = odbc_fetch_row($res, $nr);
			
			$nf = count($result)+2; /* Field numbering starts at 1 */
			for($count=1; $count < $nf; $count++) {
				$field_name = odbc_field_name($res, $count);
				$field_value = odbc_result($res, $field_name);
				$row[$field_name] = $field_value;
			}
		 	break;
		
		case "Interbase":
			$orow=ibase_fetch_object($res);
			$row=get_object_vars($orow);
		 	break;

		case "Sybase":
			$row = sybase_fetch_array($res);
			break;
		
		case "Oracle":
			ora_fetch_into($res, $row, ORA_FETCHINTO_NULLS|ORA_FETCHINTO_ASSOC);
			break;
	}
	return $row;
}

function sql_fetch_object($res, $nr=0, $count=1, $dbno=1) {
	global $PHP_SELF, $dbtype, $dbi, $prefix, $sql_stats, $sql_stats_conn;
	
	if($sql_stats == 1 && $count==1) {
		sql_query("update $prefix"."_sql_stats set fetch_object=fetch_object+1", $sql_stats_conn, 0, $dbno);
	}
	
	if($nr==0) {
		$nr = $dbi[$dbno];
	}
	
	switch ($dbtype) {
		case "MySQL":
			$row = mysql_fetch_object($res);
			break;

		case "mSQL":
			$row = msql_fetch_object($res);
			break;

		case "PostgreSQL":
		case "PostgreSQL_local":
			if( $res->get_total_rows > $res->get_fetched_rows() ) {
				$row = pg_fetch_object( $res->get_result(), $res->get_fetched_rows() );
				$res->increment_fetched_rows();
			} else {
				return false;
			}
 			break;

		case "ODBC":
			$result = odbc_fetch_row($res, $nr);
			if(!$result) return false;
			$nf = odbc_num_fields($res); /* Field numbering starts at 1 */
			for($count=1; $count < $nf+1; $count++) {
				$field_name = odbc_field_name($res, $count);
				$field_value = odbc_result($res, $field_name);
				$row->$field_name = $field_value;
			}
 			break;

		case "ODBC_Adabas":
			$result = odbc_fetch_row($res, $nr);
			if(!$result) return false;
			$nf = count($result)+2; /* Field numbering starts at 1 */
			for($count=1; $count < $nf; $count++) {
				$field_name = odbc_field_name($res, $count);
				$field_value = odbc_result($res, $field_name);
				$row->$field_name = $field_value;
			}
			break;

		case "Interbase":
			$orow = ibase_fetch_object($res);
			if($orow) {
				$arow=get_object_vars($orow);
				while(list($name,$key)=each($arow)) {
					$name=strtolower($name);
					$row->$name=$key;
				}
			} else {
				return false;
			}
			break;

		case "Sybase":
			$row = sybase_fetch_object($res);
			break;
		
		case "Oracle":
			echo "sql_fetch_object not implemented for Oracle!";
	}
	return $row;
}

/*** Function Free Result for function free the memory ***/
function sql_free_result($res) {
	global $PHP_SELF, $dbtype;
	switch ($dbtype) {
		case "MySQL":
			$rows = mysql_free_result($res);
			break;
		
		case "mSQL":
			$rows = msql_free_result($res);
			break;
		
		case "PostgreSQL":
		case "PostgreSQL_local":
			$rows=pg_FreeResult( $res->get_result() );
			break;
		
		case "ODBC":
		case "ODBC_Adabas":
			$rows=odbc_free_result($res);
			break;
		
		case "Interbase":
			break;
		
		case "Sybase":
			$rows=sybase_free_result($res);
			break;
		
		case "Oracle":
			ora_close($res);
			break;
	}
	return $rows;
}

function sql_error($id=0, $dbno=1) {
	global $PHP_SELF, $dbtype, $dbi;
	if($id==0) {
		$id = $dbi[$dbno];
	}
	$msg="";
	switch ($dbtype) {
		case "MySQL":
			if(mysql_errno($id) > 0) {
				$msg = mysql_error($id);
			}
			break;
		
		case "mSQL":
			$msg = msql_error($id);
			break;
		
		case "PostgreSQL":
		case "PostgreSQL_local":
			$msg = pg_last_error($id);
			break;
		
		case "ODBC":
		case "ODBC_Adabas":
			$msg = odbc_errormsg($id);
			break;
		
		case "Interbase":
			$msg = ibase_errmsg();
			break;
		
		case "Sybase":
			$msg = sybase_get_last_message();
			break;
		
		case "Oracle":
			if(ora_errorcode($id) > 0) {
				$msg = ora_error($id);
			}
			break;
	}
	return $msg;
}

function sql_dbtype() {
	global $PHP_SELF, $dbtype;
	return $dbtype;
}

function sql_list_tables($dbno=1) {
	global $PHP_SELF, $dbtype, $dbname, $dbi;
	switch ($dbtype) {
		case "MySQL":
			$res = mysql_list_tables($dbname);
			while(list($table)=sql_fetch_row($res, 0, 1, $dbno)) {
				$arr[] = $table;
			}
			break;
		
		case "mSQL":
			// untested!
			$res = msql_list_tables($dbname);
			while(list($table)=sql_fetch_row($res, 0, 1, $dbno)) {
				$arr[] = $table;
			}
			break;
		
		case "PostgreSQL":
		case "PostgreSQL_local":
			die("sql_list_tables is not implemented for $dbtype");
			break;
		
		case "ODBC":
		case "ODBC_Adabas":
			// untested!
			$res = odbc_tables($con, "%", "%", "%", "'TABLE', 'VIEW'");
			while(list($table)=sql_fetch_row($res, 0, 1, $dbno)) {
				$arr[] = $table;
			}
			break;
		
		case "Interbase":
			die("sql_list_tables is not implemented for $dbtype");
			break;
		
		case "Sybase":
			die("sql_list_tables is not implemented for $dbtype");
			break;
		
		case "Oracle":
			die("sql_list_tables is not implemented for $dbtype");
			break;
	}
	return $arr;
}

function sql_is_implemented($function) {
	global $PHP_SELF, $dbtype;
	// without 'dummy' array_search will return 0 (=false) on mysql
	$all = array('dummy', 'mysql', 'msql', 'postgresql', 'postgresql_local', 'odbc', 'odbc_adabas', 'interbase', 'sybase', 'oracle');
	switch($function) {
		case 'sql_connect':      $ret = array_search(strtolower($dbtype), $all);
		case 'sql_logout':       $ret = array_search(strtolower($dbtype), $all);
		case 'sql_query':        $ret = array_search(strtolower($dbtype), $all);
		case 'sql_num_rows':     $ret = array_search(strtolower($dbtype), $all);
		case 'sql_fetch_row':    $ret = array_search(strtolower($dbtype), $all);
		case 'sql_fetch_array':  $ret = array_search(strtolower($dbtype), $all);
		case 'sql_fetch_object': $ret = array_search(strtolower($dbtype), array('dummy', 'mysql', 'msql', 'postgresql', 'postgresql_local', 'odbc', 'odbc_adabas', 'interbase', 'sybase'));
		case 'sql_free_result':  $ret = array_search(strtolower($dbtype), $all);
		case 'sql_error':        $ret = array_search(strtolower($dbtype), $all);
		case 'sql_dbtype':       $ret = array_search(strtolower($dbtype), $all);
		case 'sql_list_tables':  $ret = array_search(strtolower($dbtype), array('dummy', 'mysql', 'msql', 'odbc'));
		case 'sql_exists':       $ret = array_search(strtolower($dbtype), array('dummy', 'mysql', 'msql', 'odbc'));
		case 'sql_field_name':   $ret = array_search(strtolower($dbtype), array('dummy', 'mysql', 'msql', 'postgresql', 'postgresql_local', 'odbc', 'odbc_adabas', 'interbase'));
	}
	return $ret;
}

function sql_exists($tablename, $dbno=1) {
	global $PHP_SELF, $dbtype, $dbname, $dbi;
	$exists = false;
	switch ($dbtype) {
		case "MySQL":
		case "mSQL":
		case "ODBC":
		case "ODBC_Adabas":
			$exists = array_search($tablename, sql_list_tables($dbno));
			break;
		
		case "PostgreSQL":
		case "PostgreSQL_local":
			die("sql_exists is not implemented for $dbtype");
			break;
		case "Interbase":
			die("sql_exists is not implemented for $dbtype");
			break;
		
		case "Sybase":
			die("sql_exists is not implemented for $dbtype");
			break;
		
		case "Oracle":
			die("sql_exists is not implemented for $dbtype");
			break;
	}
	return $exists;
}

function sql_field_name($res, $no) {
	global $PHP_SELF, $dbtype, $dbname, $dbi;
	$name = "";
	switch ($dbtype) {
		case "MySQL":
			$name = mysql_field_name($res, $no);
			break;
		case "mSQL":
			$name = msql_fieldname($res, $no);
			break;
		case "ODBC":
		case "ODBC_Adabas":
			$name = odbc_field_name($res, $no);
			break;
		case "PostgreSQL":
		case "PostgreSQL_local":
			$name = pg_field_name($res, $no);
			break;
		case "Interbase":
			$arr = ibase_field_info($res, $no);
			$name = $arr['name'];
		case "Sybase":
		case "Oracle":
			die("sql_field_name is not implemented for $dbtype");
			break;
	}
	return $name;
}
?>
