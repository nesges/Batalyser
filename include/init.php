<?
    $sql_debug=0;
    $sql_layer_database_mode='new';
    $sql_layer_database=12;
    include("../../../sql_layer.php");
    unset($db); // no need to keep logindata in memory
    
    include("include/constants.php");
    include("include/class.parser.php");
    
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

    // GUILanguage translation
    function guil($term) {
        global $guil;
        
        if(!$guil) {
            include_once("include/language.de.php");
            if($_SESSION['language'] != 'de' && file_exists("include/language.".$_SESSION['language'].".php")) {
                include_once("include/language.".$_SESSION['language'].".php");
            }
        }

        if($guil[$_SESSION['language']][$term]) {
            return $guil[$_SESSION['language']][$term];
        } elseif($guil['de'][$term]) {
            return $guil['de'][$term];
        } else {
            // notification to start with, likely to be removed later on
            // mail('nesges@gmail.com', 'Batalyser: Missing translation for "'.$term.'"', '');
            return "TRANSLATION_MISSING ($term) [".$SESSION['language']."]";
        }
    }
?>
