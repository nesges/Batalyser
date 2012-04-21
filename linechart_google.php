<?
    $query_string = $_SERVER['QUERY_STRING'];
    if(preg_match('/charttitle=([^&]+)/', $query_string, $matches)) {
        $charttitle=$matches[1];
        $query_string = preg_replace('/\&?charttitle=([^&]+)/', '', $query_string);
    } else {
        $charttitle='';
    }
    if(preg_match('/chartheight=([^&]+)/', $query_string, $matches)) {
        $chartheight=$matches[1];
        $query_string = preg_replace('/\&?chartheight=([^&]+)/', '', $query_string);
    } else {
        $chartheight=300;
    }
    if(preg_match('/chartwidth=([^&]+)/', $query_string, $matches)) {
        $chartwidth=$matches[1];
        $query_string = preg_replace('/\&?chartwidth=([^&]+)/', '', $query_string);
    } else {
        $chartwidth=800;
    }
    
    global $guil, $version;
    include_once("include/init.php");
    
    $res = sql_query("select filename from logfile where id=".$_GET['log_id']);
    list($filename) = sql_fetch_row($res);

    $parser = new Parser($filename, $_GET['log_id']);
    $parser->read_loglines($_GET['min_id'], $_GET['max_id']);
    $parser->gather_logdata();
?>
<html>
    <head>
        <script type='text/javascript' src='https://www.google.com/jsapi'></script>
        
        <script type='text/javascript'>
            google.load('visualization', '1.0', {packages:['controls']});
            google.setOnLoadCallback(drawDashboard);
            function drawDashboard() {
                var data = google.visualization.arrayToDataTable([
                <?
                    for($id=$_GET['min_id']; $id <= $_GET['max_id']; $id++) {
                        if($id >= $last_fetch + PARSER_MAX_FETCH) {
                            $last_fetch +=PARSER_MAX_FETCH;
                            unset($parser->loglines);
                            unset($parser->logdata);
                            $parser->read_loglines($id, $id+PARSER_MAX_FETCH);
                            $parser->gather_logdata();
                        }

                        if($parser->logdata[$id]['timestamp']) {
                            foreach($_GET['section'] as $section) {
                                if($parser->logdata[$id][$section]) {
                                    $value[$section] = $parser->logdata[$id][$section];
                                    $lastvalue[$section] = $parser->logdata[$id][$section];
                                } else {
                                    if(!$lastvalue[$section]) {
                                        $lastvalue[$section]=0;
                                    }
                                    $value[$section] = $lastvalue[$section];
                                }
                            }
                            $php_data[$id] = array($parser->logdata[$id]['timestamp'] - $parser->start_timestamp);
                            $php_data[$id] = array_merge($php_data[$id], $value);
                        }
                    }
                    # print json_encode($php_data);
                    print "['Time', '".join("', '", $_GET['section'])."']";
                    foreach($php_data as $row) {
                        print ",[";
                        print join(',', $row);
                        print "]";
                    }
                ?>
                ]);
                
                var dashboard = new google.visualization.Dashboard(document.getElementById('dashboard_div'));
                
                var slider = new google.visualization.ControlWrapper({
                    'controlType': 'ChartRangeFilter',
                    'containerId': 'slider_div',
                    'options': {
                        'filterColumnLabel': 'Time',
                        'ui': {
                            'chartOptions': {
                                'chartArea': {'height': '30%'}
                            }
                        }
                    },
                    'state': {
                        'range': {
                            'start': 0,
                            'end': 50
                        }
                    }
                });
                var options = {};
                var chart = new google.visualization.ChartWrapper({
                    'chartType': 'LineChart',
                    'containerId': 'chart_div',
                    'options': {
                            'curveType': 'none',
                            'title': '<?=$charttitle?>',
                            'height': 400,
                    }
                });
                    
                dashboard.bind(slider, chart);
                dashboard.draw(data);
            }
        </script>
    </head>
    <body>
        <div id='dashboard_div'>
            <div id='chart_div'></div>
            <div id='slider_div'></div>
        </div>
    </body>
</html>