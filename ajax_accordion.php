<?
    session_start();
    if(!$_SESSION['user_id']) {
        die("101");
    }
    
    $cacherenew = 1;
    
    $char = str_replace('/', '_insertionattempt_', $_GET['char']);
    $fightnr = str_replace('/', '_insertionattempt_', $_GET['fight']);
    $log_id = str_replace('/', '_insertionattempt_', $_GET['log_id']);
    
    header('content-type: text/html; charset=iso-8859-1');
    $cache_filename = 'cache/accordion_'.$char.'_'.$log_id.'_'.$fightnr;
    if(file_exists($cache_filename) && !$cacherenew) {
        readfile($cache_filename);
        exit();
    }
    ob_start();
    
    include("include/constants.php");
    include("include/language.de.php");
    include("include/class.parser.php");
    include("include/class.tab.php");
    include("include/class.tab_dpshpstps_per_target.php");
    include("include/class.tab_char_dpstps_per_ability.php");
    include("include/class.tab_char_hpstps_per_ability.php");
    include("include/class.tab_enemies_damage_to_char.php");
    include("include/class.tab_full_fight_stats.php");
    include("include/class.tab_full_fight_graphs.php");
    
    $sql_debug=0;
    $sql_layer_database_mode='new';
    $sql_layer_database=12;
    include("../../../sql_layer.php");
    unset($db); // no need to keep logindata in memory
    
    // since this script is to be called from the main script, we always have a cached serialized parser
    $res = sql_query("select filename from logfile where id=".$log_id);
    list($filename) = sql_fetch_row($res);

    $parser = new Parser($filename, $log_id);
    
    if($fightnr) {
        $fight = $parser->players[$char]['fights'][$fightnr];
        $parser->read_loglines($fight['start_id'], $fight['end_id']);
        
        $start_id = $fight['start_id'];
        $start_timestamp = $fight['start_timestamp'];
        $end_id = $fight['end_id'];
        $end_timestamp = $fight['end_timestamp'];
        
        $tabname_prefix = 'fight'.$fight_nr.'-';
    } else {
        $parser->read_loglines();
        
        $start_id = $parser->start_id;
        $start_timestamp = $parser->start_timestamp;
        $end_id = $parser->end_id;
        $end_timestamp = $parser->end_timestamp;
        
        $tabname_prefix = 'sum-';
    }
    $parser->gather_logdata();
    
    $tabs = array();
    $tabs[] = new Tab_DpsHpsTps_per_Target(
            $tabname_prefix.preg_replace('/\s/', '', $_char).'-damage',
            $char,
            $start_id,
            $end_id,
            'dataTable_ajaxLoaded'
        );
    $tabs[] = new Tab_Char_DpsTps_per_Ability(
            $tabname_prefix.preg_replace('/\s/', '', $_char).'-dmgthreatabilities',
            $char,
            $start_id,
            $end_id,
            'dataTable_ajaxLoaded'
        );
    $tabs[] = new Tab_Char_HpsTps_per_Ability(
            $tabname_prefix.preg_replace('/\s/', '', $_char).'-healthreatabilities',
            $char,
            $start_id,
            $end_id,
            'dataTable_ajaxLoaded'
        );
    $tabs[] = new Tab_Enemies_Damage_to_Char(
            $tabname_prefix.'enemies-vs-'.preg_replace('/\s/', '', $_char).'-sources',
            $char,
            $start_id,
            $end_id,
            'dataTable_ajaxLoaded'
        );
    if($fightnr) {
        $tabs[] = new Tab_Full_Fight_Stats(
                $tabname_prefix.$_char.'-fullfight-stats',
                $char,
                $start_id,
                $end_id,
                1,
                'dataTableFullFightStats_ajaxLoaded'
            );
        $tabs[] = new Tab_Full_Fight_Graphs(
                $tabname_prefix.$_char.'-fullfight-graphs',
                $char,
                $start_id,
                $end_id
            );
    }
    
    print "<div>
            <div class='tabs'>
                <ul>";
                    foreach($tabs as $tab) {
                        print $tab->nameplate();
                    }
    print "     </ul>";

    foreach($tabs as $tab) {
        print $tab->tabcontent();
    }
    print "</div>
        </div>";
        
    $html = ob_get_flush();
    if($cache_filename) {
        if($cache_handle = fopen($cache_filename, "w")) {
            fwrite($cache_handle, $html);
            fclose($cache_handle);
        }
    }
?>