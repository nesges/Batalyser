<?
    session_start();
    header("Content-type: text/plain");
    include("include/constants.php");
    include("include/language.de.php");
    include("include/class.parser.php");
    
    if($_SESSION['user_id'] != 2) {
        die("ERROR 101");
    }
    
    $cachefilename = 'cache/'.$_GET['file'];
    $object = unserialize(file_get_contents($cachefilename));
    print_r($object);
?>