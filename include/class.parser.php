<?
    class Parser {
        var $logfile;
        var $log_id;
        var $line_count;
        var $language;
        var $start_timestamp;
        var $end_timestamp;
        var $fight_count;
        var $enemy_count;
        var $players;
        var $loglines; // is actually a temp global var, which doesn't need to be serialized
        var $logdata;
        
        // may be created without params to unserialize a parser identified by $_SESSION['log_id']
        function Parser($logfile='', $log_id='') {
            global $version, $cacherenew;
            
            $cache_parser_filename = 'cache/serialized_parser_'.$_SESSION['log_id'].'_'.$version;
            if(file_exists($cache_parser_filename) && !$cacherenew) {
                $unserialized = unserialize(file_get_contents($cache_parser_filename));
                // now copy everything from unserialized to $this
                $this->logfile         = $unserialized->logfile;
                $this->log_id          = $unserialized->log_id;
                $this->line_count      = $unserialized->line_count;
                $this->language        = $unserialized->language;
                $this->start_timestamp = $unserialized->start_timestamp;
                $this->end_timestamp   = $unserialized->end_timestamp;
                $this->fight_count     = $unserialized->fight_count;
                $this->players         = $unserialized->players;
                $this->logdata         = $unserialized->logdata;
                
                unset($unserialized);
            } else {
                $this->logfile = 'upload/'.$_SESSION['user_id'].'/'.basename($logfile); // make sure only files in the upload directory are processed
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
                $this->loglines = array();
                
                $actors = array();
                $abilities = array();
                $effects = array();
                $effect_types = array();
                $hit_types = array();
                
                $log = file($this->logfile);
                
                $line_no = 0;
                foreach($log as $line) {
                    $matches = array();
                    $month   = "";
                    $day     = "";
                    $year    = "";
                    $time    = "";
                
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
                        
                        // don't put anything into loglines before infight is not set
                        
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
                                    $this->infight = -1; // -1 for the last row to put in loglines; all further checks go for exactly infight!=0
                                }
                            }
                            
                            if($this->infight!=0) {
                                $effects[$effect_id] = $effect_name;
                                    $this->loglines[$line_no]['effect_id'] = $effect_id;
                                    $this->loglines[$line_no]['effect_name'] = $effect_name;
                                
                                if($effect_type_id) {
                                    $effect_types[$effect_type_id] = $effect_type;
                                    $this->loglines[$line_no]['effect_type_id'] = $effect_type_id;
                                    $this->loglines[$line_no]['effect_type_name'] = $effect_type_name;
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
                                
                                if($source_playerflag == '@') {
                                    if(preg_match('/:/', $source_name)) {
                                        // companions have an id. But since it's not unique for every players companion, we better ignore it
                                        $this->loglines[$line_no]['source_type'] = 'companion';
                                    } else {
                                        $this->loglines[$line_no]['source_type'] = 'player';
                                        $this->players[$source_name]++;
                                    }
                                } else {
                                    $this->loglines[$line_no]['source_type'] = 'npc';
                                    $this->loglines[$line_no]['source_id'] = $source_id;
                                    $actors[$source_id] = $source_name;
                                }
                                $this->loglines[$line_no]['source_name'] = $source_name;
                            }
                            
                            //target
                            $matches_target = array();
                            if(preg_match('/(@?)([^\{]*)(?:\s*\{(.*?)\})?/', $target_raw, $matches_target)) {
                                $target_playerflag = $matches_target[1];
                                $target_name = trim($matches_target[2]);
                                $target_id = $matches_target[3];
                                
                                if($target_playerflag == '@') {
                                    if(preg_match('/:/', $target_name)) {
                                        // companions have an id. But since it's not unique for every players companion, we better ignore it
                                        $this->loglines[$line_no]['target_type'] = 'companion';
                                    } else {
                                        $this->loglines[$line_no]['target_type'] = 'player';
                                        $this->players[$target_name]++;
                                    }
                                } else {
                                    $this->loglines[$line_no]['target_type'] = 'npc';
                                    $this->loglines[$line_no]['target_id'] = $target_id;
                                    $actors[$target_id] = $target_name;
                                }
                                $this->loglines[$line_no]['target_name'] = $target_name;
                            }
                            
                            // ability
                            $matches_ability = array();
                            if(preg_match('/([^\{]*)(?:\s*\{(.*?)\})?/', $ability_raw, $matches_ability)) {
                                $ability_name = trim($matches_ability[1]);
                                $ability_id = $matches_ability[2];
                                
                                $this->loglines[$line_no]['ability_name'] = $ability_name;
                                $this->loglines[$line_no]['ability_id'] = $ability_id;
                                $abilities[$ability_id] = $ability_name;
                            }
                            
                            // hitpoints, hit_type and crit
                            $matches_hitpoints = array();
                            if(preg_match('/(\d+)(\*?)(?: (.*?) \{(.*?)\})?/', $hitpoints_raw, $matches_hitpoints)) {
                                $hitpoints      = $matches_hitpoints[1];
                                $crit           = $matches_hitpoints[2]=="*"?1:0;
                                $hit_type_name  = trim($matches_hitpoints[3]);
                                $hit_type_id    = $matches_hitpoints[4];
                                
                                $this->loglines[$line_no]['hitpoints'] = $hitpoints;
                                $this->loglines[$line_no]['crit'] = $crit;
                                $this->loglines[$line_no]['hit_type_name'] = $hit_type_name;
                                $this->loglines[$line_no]['hit_type_id'] = $hit_type_id;
                            }
                            
                            // threat
                            $this->loglines[$line_no]['threat'] = $threat;
                            
                            if($month && $year && $day) {
                                $timestamp = strtotime("$year-$month-$day $time");
                            } else {
                                $timestamp = strtotime(date('Y-m-d')." $time");
                            }
                            $this->loglines[$line_no]['timestamp'] = $timestamp;
                            if(! $this->start_timestamp) {
                                $this->start_timestamp = $timestamp;
                            }
                            $this->end_timestamp = $timestamp;
                
                            $this->line_count = ++$line_no;
                        } elseif($this->infight== -1) {
                            // now, after the FIGHT_END line has been parsed, set infight to 0, to wait for the next FIGHT_START
                            $this->infight=1;
                        }
                    }
                }
                
                $this->enemy_count = count($actors) - 1;
                
                arsort($this->players);
                $this->gather_logdata();
                
                // selfserizalization
                if($cachefile = fopen($cache_parser_filename, 'w')) {
                    fwrite($cachefile, serialize($this));
                    fclose($cachefile);
                }
            }
        }
        
        function gather_logdata() {
            global $logfiles;
        
            unset($current_fight_id);
            unset($current_fight_source_name);
        
            $damage_received_overall = array();
            
            foreach($this->loglines as $id => $logline) {
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
                    case IMMUNE:          // ??? needs confirmation
                        $hitorshit = 'immune';
                        break;
                    case PARRY:
                        $hitorshit = 'parry';
                        break;
                    case DEFLECT:
                        $hitorshit = 'deflect';
                        break;
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
                
                if($source_type == 'npc') {
                    switch($effect_id) {
                        case DEATH: 
                            // if current_fight_source_name dies, there's no FIGHT_END effect logged
                            if($target_name == $current_fight_source_name && isset($current_fight_id) && !isset($logdata['players'][$current_fight_source_name]['fights'][$current_fight_id]['end_id'])) {
                                $logdata['players'][$current_fight_source_name]['fights'][$current_fight_id]['end_id'] = $id;
                                $logdata['players'][$current_fight_source_name]['fights'][$current_fight_id]['end_timestamp'] = $logdata[$id]['timestamp'];
                                unset($current_fight_id);
                                unset($current_fight_source_name);
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
                            //$logdata['players'][$target_name]['fights'][$current_fight_id]['sum']['heal_received'] += $hitpoints;
                            break;
                        case DAMAGE: 
                            $logdata['players'][$source_name]['fights'][$current_fight_id]['sum']['damage'] += $hitpoints;
                            //$logdata['players'][$target_name]['fights'][$current_fight_id]['sum']['damage_received'] += $hitpoints;
                            break;
                        case DEATH: 
                            $logdata['players'][$source_name]['fights'][$current_fight_id]['sum'][$target_type]['death_count']++;
                            break;
                        case FIGHT_START: 
                            // should for any reason a new FIGHT_START occur while the current_fight has no end_id, set it's end to the last id
                            if(isset($current_fight_source_name) && ! isset($logdata['players'][$current_fight_source_name]['fights'][$current_fight_id]['end_id'])) {
                                $logdata['players'][$current_fight_source_name]['fights'][$current_fight_id]['end_id'] = $id-1;
                                $logdata['players'][$current_fight_source_name]['fights'][$current_fight_id]['end_timestamp'] = $logdata[$id-1]['timestamp'];
                            }
                            $current_fight_id = $id;
                            $current_fight_source_name = $source_name;
                            $logdata['players'][$source_name]['fights'][$current_fight_id]['start_id'] = $id;
                            $logdata['players'][$source_name]['fights'][$current_fight_id]['start_timestamp'] = $timestamp;
                            break;
                        case FIGHT_END: 
                            $logdata['players'][$source_name]['fights'][$current_fight_id]['end_id'] = $id;
                            $logdata['players'][$source_name]['fights'][$current_fight_id]['end_timestamp'] = $timestamp;
                            unset($current_fight_id);
                            unset($current_fight_source_name);
                            break;
                    }

                    $logdata['players'][$source_name]['fights'][$current_fight_id]['sum']['threat'] += $threat;
                    $logdata['players'][$source_name]['fights'][$current_fight_id]['sum']['crit'] += $crit;
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
            }
            // last fight's end might not be in the logfile (due to maybe ragequit)
            // set it's end to the last logline then
            if(! isset($logdata['players'][$current_fight_source_name]['fights'][$current_fight_id]['end_id'])) {
                $logdata['players'][$current_fight_source_name]['fights'][$current_fight_id]['end_id'] = $logdata['base']['max_id'];
                $logdata['players'][$current_fight_source_name]['fights'][$current_fight_id]['end_timestamp'] = $logdata[$logdata['base']['max_id']]['timestamp'];
            }
            
            $this->logdata = $logdata;
            unset($this->loglines); // not needed anymore
        }
    }
?>