<?
    session_start();
    if(!$_SESSION['user_id']) {
        die("101");
    }
    
    
    
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
    
    // since this script is to be called from the main script, we always have a cached serialized parser
    $parser = new Parser();
    $logdata = $parser->logdata;
    
    switch($_GET['tab']) {
        case 'Tab_DpsHpsTps_per_Target':
            $tab = new Tab_DpsHpsTps_per_Target('ajaxtab_needs_no_name', $_GET['char'], $_GET['min_id'], $_GET['max_id']);
            print $tab->tabcontent();
            break;
    }
?>