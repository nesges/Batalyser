<?
    session_start();
    if(!$_SESSION['user_id'] && !$_SESSION['log_id']) {
        die("Session expired. Please reload.");
    }
    
    $char = str_replace('/', '_insertionattempt_', $_GET['char']);
    $fightnr = str_replace('/', '_insertionattempt_', $_GET['fight']);
    $log_id = str_replace('/', '_insertionattempt_', $_GET['log_id']);
    $reload = $_GET['reload'];
    
    include_once("include/init.php");
    
    header('content-type: text/html; charset=iso-8859-1');
    $cache_filename = 'cache/accordion_'.$char.'_'.$log_id.'_'.$fightnr.'_'.$_SESSION['language'].'_'.$_SESSION['charclass'][$char];
    if(file_exists($cache_filename) && !$cacherenew) {
        readfile($cache_filename);
        exit();
    }
    ob_start();

    include("include/class.tab.php");
    include("include/class.tab_dpshpstps_per_target.php");
    include("include/class.tab_char_dpstps_per_ability.php");
    include("include/class.tab_char_hpstps_per_ability.php");
    include("include/class.tab_enemies_damage_to_char.php");
    include("include/class.tab_full_fight_stats.php");
    include("include/class.tab_full_fight_graphs.php");
    
    // since this script is to be called from the main script, we always have a cached serialized parser
    $res = sql_query("select filename from logfile where id=".$log_id);
    list($filename) = sql_fetch_row($res);

    $parser = new Parser($filename, $log_id);
    
    if(isset($fightnr) && $fightnr != '') {
        $fight = $parser->players[$char]['fights'][$fightnr];
        $parser->read_loglines($fight['start_id'], $fight['end_id']);
        
        $start_id = $fight['start_id'];
        $start_timestamp = $fight['start_timestamp'];
        $end_id = $fight['end_id'];
        $end_timestamp = $fight['end_timestamp'];
        
        $tabname_prefix = 'fight'.$fightnr.'-';
    } else {
        $parser->read_loglines();
        
        $start_id = $parser->start_id;
        $start_timestamp = $parser->start_timestamp;
        $end_id = $parser->end_id;
        $end_timestamp = $parser->end_timestamp;
        
        $tabname_prefix = 'sum-';
    }
    $parser->gather_logdata();
    
    $_char = preg_replace('/[^a-zA-Z0-9_-]/', '', $char);

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
            'dataTable_ajaxLoaded',
            ($fightnr?0:1)
        );
    if(isset($fightnr) && $fightnr != '') {
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

    $tabs_printed=0;
    print "<div>
            <div class='tabs'>
                <ul>";
                    foreach($tabs as $tab) {
                        if($tab->data || $tab->html) {
                            print $tab->nameplate();
                            $tabs_printed++;
                        }
                    }
    print "     </ul>";

    foreach($tabs as $tab) {
        print $tab->tabcontent();
    }
    print '</div>
        </div>';

    // there seem to be serverload-issues preventing tabs to collect data
    // this is a simple workarround to at least reinitialise such broken tabs
    // iow: try again (3 times)
    if($tabs_printed == 0) {
        if($reload < 3) {
            sleep(3);
            header("Location: ".$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&reload='.($reload+1));
        } else {
            // we tried to reload trice, but weren't succesfull to load data afterall
            // at least these accordions aren't cached, so the user cann reload manually
            ob_end_clean();
            print "Die Daten konnten nicht geladen werden. Mit einem Klick auf die Überschrift der Sektion kannst du ein erneutes Laden veranlassen.";
        }
    } else {
        // everything went fine -> print and cache
        $html = ob_get_flush();
        if($cache_filename && $tabs_printed > 3) {
            if($cache_handle = fopen($cache_filename, "w")) {
                fwrite($cache_handle, $html);
                fclose($cache_handle);
            }
        }
    }
?>