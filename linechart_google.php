<?
    global $guil, $version;
    include_once("include/init.php");
    
    $cache_filename = "cache/linechart_".$_GET['log_id']."_".$_GET['min_id']."_".$_GET['max_id']."_".md5($_SERVER['QUERY_STRING']);
    if(file_exists($cache_filename) && !$cacherenew) {
        readfile($cache_filename);
        exit();
    }
    ob_start();
    
    $res = sql_query("select filename from logfile where id=".$_GET['log_id']);
    list($filename) = sql_fetch_row($res);

    $index = $_GET['index'];
    if(!$index) {
        $index = 'time';
    }

    $parser = new Parser($filename, $_GET['log_id']);
    $parser->read_loglines($_GET['min_id'], $_GET['max_id']);
    $parser->gather_logdata();
?>
<html>
    <head>
        <link type="text/css" href="css/custom-theme/jquery-ui-1.8.18.custom.css" rel="stylesheet">
        <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
        <script type="text/javascript" src="js/jquery-ui-1.8.18.custom.min.js"></script>
        <style>
            * {
                font: 12px "Trebuchet MS", sans-serif;
            }
        </style>
        
        <script type='text/javascript' src='https://www.google.com/jsapi'></script>
        <script type='text/javascript'>
            google.load('visualization', '1.0', {packages:['controls']});
            google.setOnLoadCallback(drawDashboard);
            var dashboard;
            var chart;
            var data;
            var view;
            function drawDashboard() {
                $('button').button();
                data = new google.visualization.DataTable();
                <?
                    if($index=='time') {
                        print "data.addColumn('timeofday', 'ID');";
                    } else {
                        print "data.addColumn('number', 'ID');";
                    }
                ?>
                data.addColumn({type:'string', role:'annotation'});
                data.addColumn({type:'string', role:'annotation'});
                <?
                    $sectionlabel = array();
                    if($_GET['section'][0]=='allhealers') {
                        $charttitle = 'All Healers (healing you)';
                        unset($_GET['section'][0]);
                        $s=0;
                        foreach(array_keys($parser->players) as $player) {
                            if($parser->players[$player]['fights'][$_GET['min_id']]['sum']['healed']>0) {
                                $_GET['section'][$s] = 'heal';
                                $_GET['cond'][$s++][] = 'source_name='.$player;
                            }
                        }
                    }
                    
                    for($s=0; $s<count($_GET['section']); $s++) {
                        $section = $_GET['section'][$s];
                        switch($section) {
                            case "hpprogress":
                                list($lvalue, $rvalue) = explode('=', $_GET['cond'][$s][0]);
                                $sectionlabel[$s] = "HP Progress (".$rvalue.")";
                                break;
                            default:    
                                $sectionlabel[$s] = ucfirst($section);
                                if($_GET['overall'][$s]) {
                                    $sectionlabel[$s] .= ' overall';
                                }
                                if($_GET['cond'][$s]) {                            
                                    foreach($_GET['cond'][$s] as $condition) {
                                        list($lvalue, $rvalue) = explode('=', $condition);
                                        $sectionlabel[$s] .= ' ('.$lvalue.'='.$rvalue.')';
                                    }
                                }
                        }
                        print "data.addColumn('number', '".$sectionlabel[$s]."'); data.addColumn({type:'string',role:'tooltip'});";
                    }
                    if(!$charttitle) {
                        $charttitle = $sectionlabel[0];
                    }
                ?>
                data.addRows([
                <?
                    $first_id_with_data=-1;
                    $end_id_with_data=-1;
                    $last_timestamp_annotation=-5;
                    for($id=$_GET['min_id']; $id <= $_GET['max_id']; $id++) {
                        if($id >= $last_fetch + PARSER_MAX_FETCH) {
                            $last_fetch +=PARSER_MAX_FETCH;
                            unset($parser->loglines);
                            unset($parser->logdata);
                            $parser->read_loglines($id, $id+PARSER_MAX_FETCH);
                            $parser->gather_logdata();
                        }

                        if($parser->logdata[$id]['timestamp']) {
                            for($s=0; $s<count($_GET['section']); $s++) {
                                $section = $_GET['section'][$s];
                                switch($section) {
                                    case "hpprogress":
                                        list($lvalue, $rvalue) = explode('=', $_GET['cond'][$s][0]);
                                        $char = $rvalue;
                                        if($parser->logdata[$id]['target_name'] == $char) {
                                            if($parser->logdata[$id]['damage'] || $parser->logdata[$id]['heal']) {
                                                if($first_id_with_data<0) {
                                                    $first_id_with_data = $id;
                                                }
                                                if($end_id_with_data<0 && $id > $first_id_with_data+100 ) {
                                                    $end_id_with_data = $id;
                                                }
                                                if($parser->logdata[$id]['damage']) {
                                                    $section_value = $parser->logdata[$id]['damage'];
                                                    $overall[$sectionlabel[$s]] -= $parser->logdata[$id]['damage'];
                                                    $tooltip_pre = "[DMG] ";
                                                } else {
                                                    $section_value = $parser->logdata[$id]['heal'];
                                                    $overall[$sectionlabel[$s]] += $parser->logdata[$id]['heal'];
                                                    $tooltip_pre = "[Heal] ";
                                                }
                                                $value[$sectionlabel[$s]] = $overall[$sectionlabel[$s]];
                                                $value[$sectionlabel[$s].'-tooltip'] = "'".$tooltip_pre.$parser->logdata[$id]['ability_name']."'";
                                                $value[$sectionlabel[$s].'-tooltip'] = "'".$tooltip_pre.$parser->logdata[$id]['ability_name'].": ".$section_value." [".addslashes($parser->logdata[$id]['source_name'])." > ".addslashes($parser->logdata[$id]['target_name'])."]'";
                                            } else {
                                                $value[$sectionlabel[$s]] = 'null';
                                                $value[$sectionlabel[$s].'-tooltip'] = 'null';
                                            }
                                        }
                                        break;
                                    default:
                                        $section_value = $parser->logdata[$id][$section];
                                        $conditions_complied=1;
                                        if($_GET['cond'][$s]) {                            
                                            foreach($_GET['cond'][$s] as $condition) {
                                                list($lvalue, $rvalue) = explode('=', $condition);
                                                if($parser->logdata[$id][$lvalue]!=$rvalue) {
                                                    $conditions_complied=0;
                                                    break;
                                                }
                                            }
                                        }
                                        
                                        if($section_value && $conditions_complied) {
                                            if($first_id_with_data<0) {
                                                $first_id_with_data = $id;
                                            }
                                            if($end_id_with_data<0 && $id > $first_id_with_data+130 ) {
                                                $end_id_with_data = $id;
                                            }
                                            if($_GET['overall'][$s]) {
                                                $overall[$sectionlabel[$s]] += $section_value;
                                                $value[$sectionlabel[$s]] = $overall[$sectionlabel[$s]];
                                            } else {
                                                $value[$sectionlabel[$s]] = $section_value;
                                            }
                                            
                                            $value[$sectionlabel[$s].'-tooltip'] = "'".$parser->logdata[$id]['ability_name'].": ".$section_value." ".$section." [".addslashes($parser->logdata[$id]['source_name'])." > ".addslashes($parser->logdata[$id]['target_name'])."]'";
                                        } else {
                                            $value[$sectionlabel[$s]] = 'null';
                                            $value[$sectionlabel[$s].'-tooltip'] = 'null';
                                        }
                                }
                            }
                            
                            $php_data[$id] = array($id);
                            
                            switch($parser->logdata[$id]['effect_id']) {
                                case DEATH:
                                    array_push($php_data[$id], "'Tod: ".addslashes($parser->logdata[$id]['target_name'])."'");
                                    break;
                                case REVIVAL:
                                    array_push($php_data[$id], "'Rez: ".addslashes($parser->logdata[$id]['target_name'])."'");
                                    break;
                                default:
                                    array_push($php_data[$id], "null");
                            }
                            
                            if($index=='time') {
                                $ts = $parser->logdata[$id]['timestamp'];
                                array_push($php_data[$id], "'".date("H:i:s", $ts)."'");
                            } else {
                                if(ceil($parser->logdata[$id]['timestamp']) > ceil($last_timestamp_annotation)) {
                                    $last_timestamp_annotation = $parser->logdata[$id]['timestamp'];
                                    array_push($php_data[$id], "'".date("H:i:s", $parser->logdata[$id]['timestamp'])."'");
                                } else {
                                    array_push($php_data[$id], "''");
                                }
                            }
                            $php_data[$id] = array_merge($php_data[$id], $value);
                        }
                    }

                    if($index=='time') {
                        // merge all values to their timestamp
                        unset($first_id_with_data);
                        unset($end_id_with_data);
                        $current_timestamp = $php_data[min(array_keys($php_data))][2];
                        for($p=$_GET['min_id']; $p <= $_GET['max_id']; $p++) {
                            foreach($sectionlabel as $section) {
                                if($php_data[$p][$section]!='unset') {
                                    if($php_data[$p][$section]=='null') {
                                        $php_data[$p][$section]=0;
                                    }
                                    $p_tooltip = preg_replace('/^\'(.*?)\'$/', '$1', $php_data[$p][$section.'-tooltip']);
                                    for($q=$p+1; $q<=$_GET['max_id']; $q++) {
                                        if($current_timestamp != $php_data[$q][2]) {
                                            break;
                                        }
                                        if($php_data[$q][$section]!='null') {
                                            $php_data[$p][0] = '['.str_replace("'", '', str_replace(':', ',', $php_data[$q][2])).',0]';
                                            if(!$first_id_with_data) {
                                                $first_id_with_data = $php_data[$p][0];
                                            }
                                            if(!$end_id_with_data) {
                                                preg_match('/\[(\d+),(\d+),(\d+),\d+\]/', $php_data[$p][0], $matches);
                                                $current_secs = $matches[1]*60*60 + $matches[2]*60 + $matches[3];
                                                
                                                preg_match('/\[(\d+),(\d+),(\d+),\d+\]/', $first_id_with_data, $matches);
                                                $first_id_secs = $matches[1]*60*60 + $matches[2]*60 + $matches[3];
                                                
                                                if($current_secs > $first_id_secs + 30) {
                                                    $end_id_with_data = $php_data[$p][0];
                                                }
                                            }
                                            if(preg_match('/^HP Progress/', $section)) {
                                                $php_data[$p][$section] = $php_data[$q][$section];
                                            } else {
                                                $php_data[$p][$section] += $php_data[$q][$section];
                                            }
                                            $q_tooltip = preg_replace('/^\'(.*?)\'$/', '$1', $php_data[$q][$section.'-tooltip']);
                                            if($q_tooltip != 'null') {
                                                if($p_tooltip == 'null') {
                                                    $p_tooltip = $q_tooltip;
                                                } else {
                                                    $p_tooltip .= "\u000D\u000A".$q_tooltip;
                                                }
                                            }
                                            $php_data[$p][$section.'-tooltip'] = "'".$p_tooltip."'";
                                            $php_data[$q][$section]='null';
                                            $php_data[$q][$section.'-tooltip']='null';
                                        }
                                    }
                                }
                            }
                            $p=$q-1;
                            $current_timestamp = $php_data[$q][2];
                        }
                    }
                    
                    foreach($php_data as $row) {
                        if($index=='time') {
                            if(preg_match('/\[\d+,\d+,\d+,\d+\]/', $row[0])) {
                                $line[] = "[".join(',', $row)."]";
                            } elseif($row[1] != 'null') {
                                // event-annotations
                                $row[0] = '['.str_replace("'", '', str_replace(':', ',', $row[2])).',0]';
                                $line[] = "[".join(',', $row)."]";
                            }
                        } else {
                            $line[] = "[".join(',', $row)."]";
                        }
                    }
                    $linecount = count($line);
                    if($line) {
                        print join(',', $line);
                    }
                    
                    if($end_id_with_data<0) {
                        $end_id_with_data = max(array_keys($php_data));
                    }
                ?>
                ]);
                <?
                    if($linecount > 0) {
                ?>
                view = new google.visualization.DataView(data);
                view.hideColumns([2]);
                
                dashboard = new google.visualization.Dashboard(document.getElementById('dashboard_div'));
                
                var slider = new google.visualization.ControlWrapper({
                    'controlType': 'ChartRangeFilter',
                    'containerId': 'slider_div',
                    'options': {
                        'filterColumnIndex': 0,
                        'ui': {
                            'chartType': 'LineChart',
                            'chartOptions': {
                                'chartArea': {'height': '30%'},
                                'interpolateNulls': true
                            },
                            'snapToData': false
                        }
                    }
                    <?
                        if($first_id_with_data && $end_id_with_data) {
                    ?>
                    ,
                    'state': {
                        'range': {
                            'start': <?=$first_id_with_data?>,
                            'end': <?=($end_id_with_data)?>
                        }
                    }
                    <?
                        }
                    ?>
                });
                chart = new google.visualization.ChartWrapper({
                    'chartType': 'LineChart',
                    'containerId': 'chart_div',
                    'options': {
                            'curveType': 'none',
                            'title': '<?=$charttitle?>',
                            'height': 400,
                            'interpolateNulls': true,
                            'pointSize': 2,
                            'annotation': {
                                2: {'style':'line'},
                                1: {'style':'line'}
                            },
                            'legend': {
                                'position': 'bottom'
                            },
                            <?
                                if(!preg_match('/^All Healers/', $charttitle)) {
                                    $color = array();
                                    for($s=0; $s<count($_GET['section']); $s++) {
                                        $section = $_GET['section'][$s];
                                        switch($section) {
                                            case 'hpprogress': $color[$s] = "'green'"; break;
                                            case 'damage': $color[$s] = "'red'"; break;
                                            case 'heal': $color[$s] = "'blue'"; break;
                                            case 'threat': $color[$s] = "'orange'"; break;
                                            default: $color[$s] = "'black'"; break;
                                        }
                                    }
                                    print "'colors': [".join(',', $color)."],";
                                }
                            ?>
                            'hAxis': {
                                'gridlines': {
                                    'count': null
                                }
                            }
                    }
                });
                    
                dashboard.bind(slider, chart);
                dashboard.draw(view);
            }
            
            var hidden = new Array();
            hidden[2] = 1;
            function toggleColumn(column) {
                if(hidden[column]) {
                    var displayedColumns = view.getViewColumns();
                    displayedColumns.push(column);
                    displayedColumns.sort();
                    view.setColumns(displayedColumns);
                    hidden[column] = 0;
                } else {
                    view.hideColumns([column]);
                    hidden[column] = 1;
                }
                dashboard.draw(view);
                <?
                    }  // if linecount > 0
                ?>
            }
        </script>
    </head>
    <body>
        <?
            if($linecount > 0) {
        ?>
        <div id='dashboard_div' style='background-color:white'>
            <button onClick='dashboard.draw(view)'>Resize</button>
            <?
                if($index=='time') {
                    $newindex = 'id';
                    $buttontext = 'ID-Index';
                } else {
                    $newindex = 'time';
                    $buttontext = 'Time-Index';
                }
                $indexButtonURL = preg_replace('/([?&])index=(time|id)&?/', '$1', $_SERVER['QUERY_STRING']);
                $indexButtonURL = $_SERVER['PHP_SELF'].'?'.$indexButtonURL.'&index='.$newindex;
                $indexButtonURL = preg_replace('/&+/', '&', $indexButtonURL);
                print "<button onClick='document.location.href=\"".$indexButtonURL."\"'>".$buttontext."</button>";
            ?>
            <div style='float:right'>
                <button onClick='toggleColumn(2)'>Timestamps</button>
                <button onClick='toggleColumn(1)'>Events</button>
                <?
                    for($s=1; $s<count($_GET['section']); $s++) {
                        $section = $_GET['section'][$s];
                        print "<button onClick='toggleColumn(".(1+($s+1)*2)."); toggleColumn(".(2+($s+1)*2).")'>".$sectionlabel[$s]."</button>";
                    }
                ?>
            </div>
            <div id='chart_div'></div>
            <div id='slider_div'></div>
        </div>
        <?
            } else {
                print "no data found";
            }
        ?>
    </body>
</html>
<?
    $html = ob_get_flush();
    if($cache_filename) {
        if($cache_handle = fopen($cache_filename, "w")) {
            fwrite($cache_handle, $html);
            fclose($cache_handle);
        }
    }
?>