<?
    global $guil;
    
    $sql_debug=0;
    $sql_layer_database_mode='new';
    $sql_layer_database=12;
    include("../../../sql_layer.php");
    unset($db); // no need to keep logindata in memory
    
    include("include/constants.php");
    include("include/class.parser.php");
    //include("include/class.benchmark.php");
    
    // limit upload to 524288 Bytes (512kB) / 1048576 Bytes (1MB)
    // in the upload form there are additional mentions of MAX_FILE_SIZE
    define("MAX_FILE_SIZE", 1048576*3);
    ini_set("upload_max_filesize", MAX_FILE_SIZE);
    ini_set("post_max_size", MAX_FILE_SIZE);

    $languages['de'] = 'Deutsch';
    $languages['en'] = 'Englisch';
    $languages['fr'] = 'Französisch';
    $languages['other'] = 'Andere';
    
    $cacherenew=0;
    if(isset($_GET['cacherenew'])) {
        $cacherenew = $_GET['cacherenew'];
    }

    // user_id=1 is our demo-user
    $demo = 0;
    if($_SESSION['user_id']==1) {
        $demo = 1;
    }
    
    // user_id=2 is Marduc
    $admin = 0;
    if($_SESSION['user_id']==2) {
        $admin = 1;
    }

    // debug for admin only
    if($admin) {
        // error_reporting(E_ALL);
    }

    $disable_ui_element="";
    if($demo) {
        $disable_ui_element = "disabled='disabled'";
    }

    $op="";
    if(isset($_POST['op'])) {
        $op = $_POST['op'];
    } elseif(isset($_GET['op'])) {
        $op = $_GET['op'];
    }

    if(!$op && !$_SESSION['log_id']) {
        $openOptions = 1;
    } else {
        $openOptions = 0;
    }
    if(isset($_POST['options'])) {
        $openOptions = $_POST['options'];
    } elseif(isset($_GET['options'])) {
        $openOptions = $_GET['options'];
    }
    
    // check for saved settings
    if(isset($_COOKIE['language'])) {
        $_SESSION['language'] = $_COOKIE['language'];
    }
    if(isset($_COOKIE['min_fight_duration'])) {
        $_SESSION['min_fight_duration'] = $_COOKIE['min_fight_duration'];
    }

    $message="";
    $errormessage="";
    $login_message="";

    if(isset($_GET['message'])) {
        $message = $_GET['message'];
    }

    // GUILanguage translation
    function guil($term) {
        global $guil;
        
        if(!isset($guil) || !$guil[$_SESSION['language']][$term]) {
            include("include/language.de.php");
            if($_SESSION['language'] != 'de' && file_exists("include/language.".$_SESSION['language'].".php")) {
                include("include/language.".$_SESSION['language'].".php");
            }
        }

        if($guil[$_SESSION['language']][$term]) {
            return $guil[$_SESSION['language']][$term];
        } elseif($guil['de'][$term]) {
            return $guil['de'][$term];
        } else {
            return "TRANSLATION_MISSING ($term) ".$errmsg.$_SESSION['language'];
        }
    }
?>
