<?
    $query_string = $_SERVER['QUERY_STRING'];
    if(preg_match('/pietitle=([^&]+)/', $query_string, $matches)) {
        $pietitle=$matches[1];
        $query_string = preg_replace('/\&?pietitle=([^&]+)/', '', $query_string);
    } else {
        $pietitle='';
    }
    if(preg_match('/pieheight=([^&]+)/', $query_string, $matches)) {
        $pieheight=$matches[1];
        $query_string = preg_replace('/\&?pieheight=([^&]+)/', '', $query_string);
    } else {
        $pieheight=300;
    }
    if(preg_match('/piewidth=([^&]+)/', $query_string, $matches)) {
        $piewidth=$matches[1];
        $query_string = preg_replace('/\&?piewidth=([^&]+)/', '', $query_string);
    } else {
        $piewidth=400;
    }
?>
<html>
    <head>
        <script type='text/javascript' src='https://www.google.com/jsapi'></script>
        
        <script type='text/javascript'>
            google.load('visualization', '1', {packages:['corechart']});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = new google.visualization.DataTable();
                data.addColumn('string', 'label');
                data.addColumn('number', 'value');
                data.addRows([
                <?
                    $pairs = explode('&', $query_string);
                    foreach($pairs as $pair) {
                        list($label, $value) = explode('=', $pair);
                        if(!$value) {
                            $value=0;
                        }
                        $html .= "['".urldecode($label)."', ".$value."],";
                    }
                    $html = preg_replace('/,$/', '', $html);
                    print $html;
                ?>
                ]);
                var options = {
                    title: '<?=urldecode($pietitle)?>',
                    is3D: true,
                    backgroundColor: 'none'
                };
                var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
                chart.draw(data, options);
            }
        </script>
    </head>
    <body>
        <div id='chart_div' style='width: <?=$piewidth?>px; height: <?=$pieheight?>px;'></div>
    </body>
</html>
<?
    $memreal = memory_get_usage(true);
                
    $fh = fopen('benchmarks', 'a');
    fwrite($fh, sprintf("% 10sB % 6sKB % 3sMB % 6s piechart_google.php\n", 
        $memreal, 
        round($memreal/1024,0), 
        round($memreal/(1024*1024),0), 
        $_SESSION['log_id']));
    fclose($fh);
?>