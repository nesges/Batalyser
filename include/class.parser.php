<?
    define("PARSER_MAX_FETCH", 5000);
    
    class Parser {
        var $logfile;
        var $log_id;
        var $line_count;
        var $language;
        var $start_id;
        var $end_id;
        var $start_timestamp;
        var $end_timestamp;
        var $fight_count;
        var $players;
        var $loglines; // is actually a temp global var, which doesn't need to be serialized
        var $logdata;
        
        // may be created without params to unserialize a parser identified by $_SESSION['log_id']
        function Parser($logfile='', $log_id='') {
            global $version;
            
            $this->cache_parser_filename = 'cache/serialized_parser_'.$_SESSION['log_id'].'_'.$version;
            if(file_exists($this->cache_parser_filename)) {
                $unserialized = unserialize(file_get_contents($this->cache_parser_filename));
                // now copy everything from unserialized to $this
                $this->logfile         = $unserialized->logfile;
                $this->log_id          = $unserialized->log_id;
                $this->line_count      = $unserialized->line_count;
                $this->language        = $unserialized->language;
                $this->start_timestamp = $unserialized->start_timestamp;
                $this->end_timestamp   = $unserialized->end_timestamp;
                $this->start_id        = $unserialized->start_id;
                $this->end_id          = $unserialized->end_id;
                $this->fight_count     = $unserialized->fight_count;
                $this->players         = $unserialized->players;
                $this->logdata         = $unserialized->logdata;
                
                unset($unserialized);
            } else {
                if(preg_match('#upload/\d+/[^/]+#', $logfile)) {
                    $this->logfile = $logfile;
                } else {
                    die("101");
                }
                if(!file_exists($this->logfile)) {
                    $this->logfile = 'upload/'.basename($logfile); // make sure only files in the upload directory are processed
                }
                if(!file_exists($this->logfile)) {
                    die('Logfile not found');
                }
                $this->log_id = $log_id;
                $this->line_count = 0;
                $this->language = "";
                $this->start_timestamp = "";
                $this->end_timestamp = "";
                $this->fight_count = 0;
                $this->players = array();
                
                $this->actors = array();
                $this->abilities = array();
                $this->effects = array();
                $this->effect_types = array();
                $this->hit_types = array();

                $this->populate();
                $this->serialize_self();
            }
        }
        
        function serialize_self() {
            if($cachefile = fopen($this->cache_parser_filename, 'w')) {
                fwrite($cachefile, serialize($this));
                fclose($cachefile);
            }
        }
        
        function populate() {
            $read_from_file = $this->logfile;
            $archive_dir = '';
            
            if(preg_match('/\.zip$/', $this->logfile)) {
                $archive_dir = $this->logfile.'~';
                @mkdir($archive_dir);
                exec('unzip "'.$this->logfile.'" -d "'.$archive_dir.'/"');
                $extracted = glob($archive_dir.'/*.*');
                if(count($extracted)!=1) {
                    foreach($extracted as $extracted_file) {
                        @unlink($extracted_file);
                    }
                    @rmdir($archive_dir);
                    header('Location: '.$_SERVER['PHP_SELF'].'?op=noop&message=Ein Archiv muss genau ein Combatlog enthalten.');
                    exit();
                } else {
                    $read_from_file = $extracted[0];
                }
            }
            
            $logfile_handle = fopen($read_from_file, "r");
            if(!$logfile_handle) {
                die("couldn't open logfile ".$this->logfile);
            }
            
            $line_no = -1;
            
            sql_query("delete from data where logfile_id=".$this->log_id);
            
            while(($line = fgets($logfile_handle)) !== false) {
                $line_no++;
                    
                $matches = array();
                $month   = "";
                $day     = "";
                $year    = "";
                $time    = "";
                $row     = array();
                $row['logfile_id'] = $this->log_id;
                
                $previous_timestamp = 0;
                
                if(preg_match('/\[(?:(\d\d)\/(\d\d)\/(\d\d\d\d)\ )?(\d\d:\d\d:\d\d(?:\.(\d\d\d))?)\]\s*\[(.*?)\]\s*\[(.*?)\]\s*\[(.*?)\]\s*\[(.*?)\]\s*\((.*)\)(?:\s*<(.*?)>)?/', $line, $matches)) {
                    $month          = $matches[1];
                    $day            = $matches[2];
                    $year           = $matches[3];
                    $time           = $matches[4];
                    $msec           = $matches[5];
                    $source_raw     = $matches[6];
                    $target_raw     = $matches[7];
                    $ability_raw    = $matches[8];
                    $effect_raw     = $matches[9];
                    $hitpoints_raw  = $matches[10];
                    $threat         = $matches[11];
                    
                    $effect_id = "";
                    $efect_name = "";
                    $effect_type_id = "";
                    $effect_type_name = "";
                    $source_playerflag = "";
                    $source_name = "";
                    $source_id = "";
                    $source_type = "";
                    $target_playerflag = "";
                    $target_name = "";
                    $target_id = "";
                    $target_type = "";
                    $ability_name = "";
                    $ability_id = "";
                    $hitpoints = "";
                    $crit = "";
                    $hit_type_name = "";
                    $hit_type_id = "";
                    
                    // don't put anything into data before infight is not set
                    
                    // effect and effect_type
                    $matches_effect = array();
                    if(preg_match('/([^\{]*)\s*\{(.*?)\}\s*:\s*([^\{]*)\s*\{(.*?)\}/', $effect_raw, $matches_effect)) {
                        $effect_type_name = trim($matches_effect[1]);
                        $effect_type_id = $matches_effect[2];
                        $effect_name = trim($matches_effect[3]);
                        $effect_id = $matches_effect[4];
                
                        // first check if we are still infight
                        if($effect_id) {                           
                            if($effect_id == FIGHT_START) {
                                $this->infight = 1;
                                $this->fight_count++;
                                
                                // language detection
                                if(! $this->language) {
                                    switch($effect_name) {
                                        case "EnterCombat":     $this->language = "en"; break;
                                        case "Kampf beginnen":  $this->language = "de"; break;
                                        default:                $this->language = "other";
                                    }
                                }
                            } elseif ($effect_id == FIGHT_END) {
                                $this->infight = -1; // -1 for the last row to put in data; all further checks go for exactly infight!=0
                            }
                        }
                        
                        if($this->infight!=0) {
                            $row['line_no'] = $line_no;
                            if($effect_id) {
                                $this->effects[$effect_id] = $effect_name;
                                $row['effect_id'] = $effect_id;
                            }
                            if($effect_type_id) {
                                $this->effect_types[$effect_type_id] = $effect_type_name;
                                $row['effect_type_id'] = $effect_type_id;
                            }
                        }
                    }
                    
                    if($this->infight!=0) {
                        // source
                        $matches_source = array();
                        if(preg_match('/(@?)([^\{]*)(?:\s*\{(.*?)\})?/', $source_raw, $matches_source)) {
                            $source_playerflag = $matches_source[1];
                            $source_name = trim($matches_source[2]);
                            $source_id = $matches_source[3];
                            
                            $row['source_name'] = $source_name;
                            if($source_playerflag == '@') {
                                if(preg_match('/:/', $source_name)) {
                                    $source_type = 'companion';
                                } else {
                                    $source_type = 'player';
                                }
                            } else {
                                $source_type = 'npc';
                                $row['source_id'] = $source_id;
                                $this->actors[$source_id] = $source_name;
                            }
                            $row['source_type'] = $source_type;
                        }
                        
                        //target
                        $matches_target = array();
                        if(preg_match('/(@?)([^\{]*)(?:\s*\{(.*?)\})?/', $target_raw, $matches_target)) {
                            $target_playerflag = $matches_target[1];
                            $target_name = trim($matches_target[2]);
                            $target_id = $matches_target[3];
                            
                            $row['target_name'] = $target_name;
                            if($target_playerflag == '@') {
                                if(preg_match('/:/', $target_name)) {
                                    $target_type = 'companion';
                                } else {
                                    $target_type = 'player';
                                }
                            } else {
                                $target_type = 'npc';
                                $row['target_id'] = $target_id;
                                $this->actors[$target_id] = $target_name;
                            }
                            $row['target_type'] = $target_type;
                        }
                        
                        // ability
                        $matches_ability = array();
                        if(preg_match('/([^\{]*)(?:\s*\{(.*?)\})?/', $ability_raw, $matches_ability)) {
                            $ability_name = trim($matches_ability[1]);
                            $ability_id = $matches_ability[2];
                            
                            $row['ability_id'] = $ability_id;
                            $this->abilities[$ability_id] = $ability_name;
                        }
                        
                        // hitpoints, hit_type and crit
                        $matches_hitpoints = array();
                        if(preg_match('/(\d+)(\*?)(?: (.*?) \{(.*?)\})?/', $hitpoints_raw, $matches_hitpoints)) {
                            $hitpoints      = $matches_hitpoints[1];
                            $crit           = $matches_hitpoints[2]=="*"?1:0;
                            $hit_type_name  = trim($matches_hitpoints[3]);
                            $hit_type_id    = $matches_hitpoints[4];
                            
                            $row['hitpoints'] = $hitpoints;
                            $row['crit'] = $crit;
                            $row['hit_type_id'] = $hit_type_id;
                            $this->hit_types[$hit_type_id] = $hit_type_name;
                        }
                        
                        // threat
                        $row['threat'] = $threat;
                        
                        if($month && $year && $day) {
                            $timestamp = strtotime("$year-$month-$day $time");
                        } else {
                            // BW did cut of the datepart in the latest update on pts 
                            $timestamp = strtotime(date('Y-m-d')." $time");
                            preg_match('/(\d\d):\d\d:\d\d/', $time, $time_matches);
                            $current_hour   = $time_matches[1];
                            if(isset($last_hour) && $current_hour < $last_hour) {
                                // add one day when the clock turns from 23 to 00
                                $timestamp += 60*60*24;
                            } else {
                                $last_hour = $current_hour;
                            }
                        }
                        $row['timestamp'] = $timestamp;
                        if(! $this->start_timestamp) {
                            $this->start_timestamp = $timestamp;
                            $this->start_id = $line_no;
                        }
                        $this->end_timestamp = $timestamp;
                        $this->end_id = $line_no;
                    
                        $this->line_count = $line_no;

                        // gather player/fight data
                        $id = $line_no;
                        if($source_type == 'npc') {
                            switch($effect_id) {
                                case DEATH: 
                                    // if current_fight_source_name dies, there's no FIGHT_END effect logged
                                    if($target_name == $current_fight_source_name && isset($current_fight_id) && !isset($logdata['players'][$current_fight_source_name]['fights'][$current_fight_id]['end_id'])) {
                                        $logdata['players'][$current_fight_source_name]['fights'][$current_fight_id]['end_id'] = $id;
                                        $logdata['players'][$current_fight_source_name]['fights'][$current_fight_id]['end_timestamp'] = $timestamp;
                                        unset($current_fight_id);
                                        unset($current_fight_source_name);
                                        $this->infight = -1;
                                    }
                                    break;
                            }
                        } elseif($source_type == 'player') {
                            $logdata['players'][$source_name]['count']++;
                            if(! $logdata['players'][$source_name]['min_id']) {
                                $logdata['players'][$source_name]['min_id'] = $id;
                            }
                            $logdata['players'][$source_name]['max_id'] = $id;
                            
                            switch($effect_id) {
                                case HEAL: 
                                    $logdata['players'][$source_name]['fights'][$current_fight_id]['sum']['healed'] += $hitpoints;
                                    break;
                                case DAMAGE: 
                                    $logdata['players'][$source_name]['fights'][$current_fight_id]['sum']['damage'] += $hitpoints;
                                    break;
                                case DEATH: 
                                    $logdata['players'][$source_name]['fights'][$current_fight_id]['sum'][$target_type]['death_count']++;
                                    break;
                                case FIGHT_START: 
                                    // should for any reason a new FIGHT_START occur while the current_fight has no end_id, set it's end to the last id
                                    if(isset($current_fight_source_name) && ! isset($logdata['players'][$current_fight_source_name]['fights'][$current_fight_id]['end_id'])) {
                                        $logdata['players'][$current_fight_source_name]['fights'][$current_fight_id]['end_id'] = $id-1;
                                        $logdata['players'][$current_fight_source_name]['fights'][$current_fight_id]['end_timestamp'] = $previous_timestamp;
                                    }
                                    $current_fight_id = $id;
                                    $current_fight_source_name = $source_name;
                                    $logdata['players'][$source_name]['fights'][$current_fight_id]['start_id'] = $id;
                                    $logdata['players'][$source_name]['fights'][$current_fight_id]['start_timestamp'] = $timestamp;
                                    break;
                                case FIGHT_END: 
                                    $logdata['players'][$source_name]['fights'][$current_fight_id]['end_id'] = $id;
                                    $logdata['players'][$source_name]['fights'][$current_fight_id]['end_timestamp'] = $timestamp;
                                    $this->infight = -1;
                                    break;
                            }
                        
                            if(isset($current_fight_id)) {
                                $logdata['players'][$source_name]['fights'][$current_fight_id]['sum']['threat'] += $threat;
                                $logdata['players'][$source_name]['fights'][$current_fight_id]['sum']['crit'] += $crit;
                            }
                            
                            $previous_timestamp = $timestamp;
                        }
                        if($target_type == 'player') {
                            switch($effect_id) {
                                case HEAL: 
                                    $logdata['players'][$target_name]['fights'][$current_fight_id]['sum']['heal_received'] += $hitpoints;
                                    break;
                                case DAMAGE: 
                                    $logdata['players'][$target_name]['fights'][$current_fight_id]['sum']['damage_received'] += $hitpoints;
                                    break;
                            }
                        }
                                                
                        $row['fight_id'] = $current_fight_id;
                        
                        $column_sql = '';
                        $values_sql = '';
                        foreach($row as $key => $value) {
                            $column_sql .= $key.', ';
                            if($key == 'timestamp') {
                                $values_sql .= "from_unixtime(".mysql_escape_string($value)."), ";
                            } else {
                                $values_sql .= "'".mysql_escape_string($value)."', ";
                            }
                            
                        }
                        $column_sql = preg_replace('/, $/', '', $column_sql);
                        $values_sql = preg_replace('/, $/', '', $values_sql);
                        sql_query("insert into data (".$column_sql.") values (".$values_sql.")");
                    }
                    
                    if($this->infight == -1) {
                        // now, after the FIGHT_END line has been parsed, set infight to 0, to wait for the next FIGHT_START
                        $this->infight=0;
                        unset($current_fight_id);
                        unset($current_fight_source_name);
                    }
                    unset($row);
                }
            }
            fclose($logfile_handle);
            
            // last fight's end might not be in the logfile (due to maybe ragequit)
            // set it's end to the last logline then
            if(! isset($logdata['players'][$current_fight_source_name]['fights'][$current_fight_id]['end_id'])) {
                $logdata['players'][$current_fight_source_name]['fights'][$current_fight_id]['end_id'] = $this->end_id;
                $logdata['players'][$current_fight_source_name]['fights'][$current_fight_id]['end_timestamp'] = $this->end_timestamp;
            }
            
            $this->players = $logdata['players'];
            
            if($archive_dir) {
                $files = glob($archive_dir.'/*');
                foreach($files as $extracted_file) {
                    @unlink($extracted_file);
                }
                @rmdir($archive_dir);
            }
        }
        
        function read_loglines($start_line='', $end_line='') {
            if(! $start_line) {
                $start_line = $this->start_id;
            }
            if(! $end_line) {
                $end_line = $this->end_id;
            }
            
            if($end_line > $start_line+PARSER_MAX_FETCH) {
                $end_line = $start_line+PARSER_MAX_FETCH;
            }
            
            $language = $_SESSION['language'];
            if(! $language) {
                $language = 'de';
            }
            
            $res = sql_query("select
                d.line_no, unix_timestamp(d.timestamp),
                d.source_id, d.source_type, coalesce(nullif(s.".$language.", ''), nullif(s.de, ''), nullif(s.en, ''), d.source_name),
                d.target_id, d.target_type, coalesce(nullif(t.".$language.", ''), nullif(t.de, ''), nullif(t.en, ''), d.target_name),
                d.hitpoints, 
                d.hit_type_id, coalesce(nullif(h.".$language.", ''), nullif(h.de, ''), nullif(h.en, '')), 
                d.crit, d.threat,
                d.ability_id, coalesce(nullif(a.".$language.", ''), nullif(a.de, ''), nullif(a.en, '')),
                d.effect_id, coalesce(nullif(e.".$language.", ''), nullif(e.de, ''), nullif(e.en, '')),
                d.effect_type_id, coalesce(nullif(et.".$language.", ''), nullif(et.de, ''), nullif(et.en, ''))
                from data d
                    left join ability a on (a.id = d.ability_id)
                    left join effect e on (e.id = d.effect_id)
                    left join effect_type et on (et.id = d.effect_type_id)
                    left join actor s on (s.id = d.source_id)
                    left join actor t on (t.id = d.target_id)
                    left join hit_type h on (h.id = d.hit_type_id)
                where d.line_no between ".$start_line." and ".$end_line."
                    and d.logfile_id = ".$this->log_id."
                order by d.id asc");

             while(list($line_no, $timestamp, $source_id, $source_type, $source_name, $target_id, $target_type, $target_name, 
                    $hitpoints, $hit_type_id, $hit_type_name, $crit, $threat, $ability_id, $ability_name, $effect_id, $effect_name, 
                    $effect_type_id, $effect_type_name) = sql_fetch_row($res)) {
                $this->loglines[$line_no]['timestamp']        = $timestamp;
                $this->loglines[$line_no]['source_id']        = $source_id;
                $this->loglines[$line_no]['source_type']      = $source_type;
                $this->loglines[$line_no]['source_name']      = $source_name;
                $this->loglines[$line_no]['target_id']        = $target_id;
                $this->loglines[$line_no]['target_type']      = $target_type;
                $this->loglines[$line_no]['target_name']      = $target_name;
                $this->loglines[$line_no]['hitpoints']        = $hitpoints;
                $this->loglines[$line_no]['hit_type_id']      = $hit_type_id;
                $this->loglines[$line_no]['hit_type_name']    = $hit_type_name;
                $this->loglines[$line_no]['crit']             = $crit;
                $this->loglines[$line_no]['threat']           = $threat;
                $this->loglines[$line_no]['ability_id']       = $ability_id;
                $this->loglines[$line_no]['ability_name']     = $ability_name;
                $this->loglines[$line_no]['effect_id']        = $effect_id;
                $this->loglines[$line_no]['effect_name']      = $effect_name;
                $this->loglines[$line_no]['effect_type_id']   = $effect_type_id;
                $this->loglines[$line_no]['effect_type_name'] = $effect_type_name;
            }
        }                     
                              
        function gather_logdata($start_id='', $end_id='') {
            global $logfiles; 
                              
            if($start_id=='') {
                $start_id = $this->start_id;
            }
            if($end_id=='') {
                $end_id = $this->end_id;
            }
        
            for($id=$start_id; $id<=$end_id; $id++) {
                $logline = $this->loglines[$id];
                if($logline) {
                    $timestamp          = $logline['timestamp'];
                    $source_id          = $logline['source_id'];
                    $source_type        = $logline['source_type'];
                    $source_name        = $logline['source_name'];
                    $target_id          = $logline['target_id'];
                    $target_type        = $logline['target_type'];
                    $target_name        = $logline['target_name'];
                    $hitpoints          = $logline['hitpoints'];
                    $damage_type        = $logline['hit_type_name'];
                    $crit               = $logline['crit'];
                    $threat             = $logline['threat'];
                    $ability_id         = $logline['ability_id'];
                    $ability_name       = $logline['ability_name'];
                    $effect_id          = $logline['effect_id'];
                    $effect_name        = $logline['effect_name'];
                    $effect_type_id     = $logline['effect_type_id'];
                    $effect_type_name   = $logline['effect_type_name'];
                    
                    $hit_type_name      = $logline['hit_type_name'];
                    $hit_type_id        = $logline['hit_type_id'];
                    
                    $logdata[$id]['timestamp'] = $timestamp;
                    $logdata[$id]['source_id'] = $source_id;
                    $logdata[$id]['source_type'] = $source_type;
                    $logdata[$id]['source_name'] = $source_name;
                    $logdata[$id]['target_id'] = $target_id;
                    $logdata[$id]['target_type'] = $target_type;
                    $logdata[$id]['target_name'] = $target_name;
                    $logdata[$id]['hitpoints'] = $hitpoints;
                    $logdata[$id]['damage_type'] = $damage_type;
                    $logdata[$id]['hit_type_id'] = $hit_type_id;
                    $logdata[$id]['hit_type_name'] = $hit_type_name;
                    $logdata[$id]['crit'] = $crit;
                    $logdata[$id]['threat'] = $threat;
                    $logdata[$id]['ability_id'] = $ability_id;
                    $logdata[$id]['ability_name'] = $ability_name;
                    $logdata[$id]['effect_id'] = $effect_id;
                    $logdata[$id]['effect_name'] = $effect_name;
                    $logdata[$id]['effect_type_id'] = $effect_type_id;
                    $logdata[$id]['effect_type_name'] = $effect_type_name;
                    
                    switch($effect_id) {
                        case DAMAGE:
                            $logdata[$id]['damage'] = $hitpoints;
                            $logdata['base']['damage'] += $hitpoints;
                            break;
                        case HEAL:
                            $logdata[$id]['heal'] = $hitpoints;
                            $logdata['base']['heal'] += $hitpoints;
                            break;
                    }
                    
                    // count hit, crit, miss...
                    // damage_type has no id in the logs: translate de, fr.. to en
                    $hitorshit = "";
                    switch($hit_type_id) {
                        case MISS:
                            $hitorshit = 'miss';
                            break;
                        case DODGE:
                            $hitorshit = 'dodge';
                            break;
                        case IMMUNE:
                            $hitorshit = 'immune';
                            break;
                        case PARRY:
                            $hitorshit = 'parry';
                            break;
                        case DEFLECT:
                            $hitorshit = 'deflect';
                            break;
                        //case RESIST:
                        //    $hitorshit = 'resist';
                        //    break;
                        default:
                            // if damage_type doesn't start with "-" and it's no crit, it's a normal hit
                            if(!preg_match('/^-/', $damage_type)) {
                                if(!$crit) {
                                    $hitorshit = 'hit';
                                }
                            } else {
                                // new damage_type detected
                                // implement some notification here
                            }
                            break;
                    }
                    $logdata[$id][$hitorshit]++;
                }
            }
            $this->logdata = $logdata;
        }
    }
?>