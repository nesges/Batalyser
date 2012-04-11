<?
    global $version;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso8859-1">
        <title>Batalyser - SWTOR Combat Analyser (v<?=$version?>)</title>

        <link type="text/css" href="css/custom-theme/jquery-ui-1.8.18.custom.css" rel="stylesheet">
        <link type="text/css" href="css/ColReorder.css" rel="stylesheet">
        <link type="text/css" href="css/ColumnFilterWidgets.css" rel="stylesheet">

        <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
        <script type="text/javascript" src="js/jquery-ui-1.8.18.custom.min.js"></script>
        <script type="text/javascript" src="js/jquery.dataTables.js"></script>
        <script type="text/javascript" src="js/ColReorderWithResize.js"></script>
        <script type="text/javascript" src="js/ColumnFilterWidgets.js"></script>
        
        <style type="text/css">
            html, body {
                background: #5b5b5b url(/forum/styles/batalyser/theme/images/outerbg.gif) top left repeat; 
            }
            body {
                margin: 25px;
                font: 12px "Trebuchet MS", sans-serif;
                color:white;
            }
            * {
                font: 12px "Trebuchet MS", sans-serif;
            }
            #logo {
                background-image:url(/images/batalyser.png);
                background-position:center center;
                background-repeat:no-repeat; 
                height:110px;
                width:100%;
                position:fixed;
                bottom: 50px;
                background-position:center top;
            }
            hr.spacer {
                margin:25px;
                border:none;
                border-top:1px solid silver;
            }
            .dataTable {
                border-collapse: separate;
                border-spacing: 1px;
            }
            .dataTable td {
                padding: 2px;
            }
            .dataTable td.col2 {
                background-color:#eeeeee;
            }
            .dataTable tfoot tr td {
                font-weight: bolder;
                background-color: #999999;
                color:white;
            }
            .dataTable td {
                text-align:right;
            }
        </style>
    </head>
    <body>
        <div id='logo'></div>