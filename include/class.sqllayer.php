<?
    class SQLLayer {
        var $dbi;
        var $dbid;
        var $debug;
        var $lastquery;
        var $connected;
        
        function SQLLayer($dbid=0) {
            $this->dbid = $dbid;
            $this->connected = false;
        }

        function conn($dbid=0, $host='', $user='', $pass='', $database='') {
            if($dbid>0) {
                $this->dbid = $dbid;
            }
            
            if($this->dbid) {
                include("../../../dbconfig.php");
                $this->dbid = $this->dbid;
                $host = $db[$this->dbid]['host'];
                $user = $db[$this->dbid]['user'];
                $password = $db[$this->dbid]['pass'];
                $database = $db[$this->dbid]['db'];
                unset($db);
                // legacy crap in dbconfig..
                unset($dbhost); unset($dbuname); unset($dbpass); unset($dbname); unset($dbhost2); unset($dbname2); unset($dbuname2); unset($dbpass2);
            }
            
            $this->dbi = mysql_connect($host, $user, $password);
			if(mysql_select_db($database)) {
			    $this->connected = true;
                $this->datasize();
			    $res = $this->query("show tables like 'data'");
			    list($table) = $this->fetch_row($res);
			    if($table != 'data') {
			        $this->create_datatable();
			    }
			}
			
			return $this->connected;
        }
        
        function logout() {
            if(mysql_close($this->dbi)) {
                $this->connected = false;
                return true;
            }
            return false;
        }

        function query($query) {
	        if(! $this->connected) {
	            $this->conn();
            }
	        if($this->debug) {
		        echo "SQL query: ".str_replace(",",", ",$query)."<BR>";
		    }
		    $this->lastquery = $query;
			
			return mysql_query($query, $this->dbi);
        }

        function num_rows($res) {
            return mysql_num_rows($res);
        }

        function fetch_row($res) {
			$row = mysql_fetch_row($res);
			if(mysql_errno() != 0) {
				echo "SQL-ERROR: ".mysql_error()." in <i>".$this->lastquery."</i>";;
			}
	        return $row;
	    }

        function fetch_array($res) {
			return mysql_fetch_array($res);
	    }

        function fetch_object($res) {
			return mysql_fetch_object($res);
        }

        function free_result($res) {
			return mysql_free_result($res);
	    }

        function error() {
			if(mysql_errno($id) > 0) {
				return mysql_error($id);
			}
        }

        function list_tables() {
			$res = mysql_list_tables($dbname);
			while(list($table)=$this->fetch_row($res)) {
				$arr[] = $table;
			}
	        return $arr;
        }

        function exists($tablename) {
			return array_search($tablename, $this->list_tables());
        }

        function field_name($res, $fieldno) {
			return mysql_field_name($res, $fieldno);
	    }
	    
	    function datasize() {
	        $res = $this->query("SELECT table_schema, data_length, index_length
                                    FROM information_schema.tables
                                    where table_schema = '".$this->database."'
                                    group by table_schema");
            list($data, $index) = $this->fetch_row($res );
            $this->datasize = $data + $index;
            return $this->size;
	    }
	    
	    function create_datatable() {
	        $this->query("CREATE TABLE IF NOT EXISTS `data` (
              `id` int(11) NOT NULL auto_increment,
              `logfile_id` int(11) NOT NULL,
              `fight_id` int(11) NOT NULL,
              `line_no` int(11) NOT NULL,
              `timestamp` datetime NOT NULL,
              `source_name` varchar(50) collate latin1_german2_ci default NULL,
              `source_id` bigint(20) NOT NULL,
              `source_type` varchar(10) collate latin1_german2_ci NOT NULL,
              `target_name` varchar(50) collate latin1_german2_ci default NULL,
              `target_id` bigint(20) NOT NULL,
              `target_type` varchar(10) collate latin1_german2_ci NOT NULL,
              `ability_id` bigint(20) NOT NULL,
              `effect_type_id` bigint(20) NOT NULL,
              `effect_id` bigint(20) NOT NULL,
              `hitpoints` int(11) default NULL,
              `hit_type_id` bigint(20) NOT NULL,
              `mitigation` int(11) NOT NULL,
              `crit` int(1) NOT NULL,
              `threat` int(11) NOT NULL,
              PRIMARY KEY  (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;");
	    }
    }
?>
