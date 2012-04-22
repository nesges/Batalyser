<?
    session_start();
    header("Content-type: text/plain");
    include("include/init.php");
    
    if($_SESSION['user_id'] != 2) {
        die("ERROR 101");
    }

    if(! $_GET['file']) {
        $cachefilename = "cache/serialized_parser_".$_GET['logfile']."_".$version;
    } else {
        $cachefilename = 'cache/'.$_GET['file'];
    }
    print $cachefilename;
    $object = unserialize(file_get_contents($cachefilename));
    print_r($object);
?>