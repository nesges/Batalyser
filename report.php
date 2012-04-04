<?
    // TODO
    //  Heilerstats
    //  Tankstats
    //  Enemy HitMissCrit
    //  Incoming Damage

    session_start();
    
    global $guil, $version;

    // debug for user Marduc only
    if($_SESSION['user_id'] == 2) {
        // error_reporting(E_ALL);
    }

    include("include/class.benchmark.php");
    $benchmark = new Benchmark("benchmarks");
    
    $sql_debug=0;
    $sql_layer_database_mode='new';
    $sql_layer_database=12;
    include("../../../sql_layer.php");
    unset($db); // no need to keep logindata in memory
    
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

    // limit upload to 524288 Bytes (512kB) / 1048576 Bytes (1MB)
    // in the upload form there are additional mentions of MAX_FILE_SIZE
    define("MAX_FILE_SIZE", 1048576);
    ini_set("upload_max_filesize", MAX_FILE_SIZE);
    ini_set("post_max_size", MAX_FILE_SIZE);

    $languages['de'] = 'Deutsch';
    $languages['en'] = 'Englisch';
    $languages['fr'] = 'Französisch';
    $languages['other'] = 'Andere';

    // user_id=1 is our demo-user
    $disable_ui_element="";
    if($_SESSION['user_id']==1) {
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

    $cacherenew=0;
    if(isset($_GET['cacherenew'])) {
        $cacherenew = $_GET['cacherenew'];
    }

    $message="";
    $errormessage="";
    $login_message="";

    if(isset($_GET['message'])) {
        $message = $_GET['message'];
    }

    // ops which don't need a database connection
    switch($op) {
        case "setopt":
            if(isset($_GET['logfile'])) {
                $_SESSION['log_id'] = $_GET['logfile'];
            }
            if(isset($_GET['min_fight_duration'])) {
                $_SESSION['min_fight_duration'] = $_GET['min_fight_duration'];
            }
            if(isset($_GET['prefered_language'])) {
                $_SESSION['language'] = $_GET['prefered_language'];
            }
            if(isset($_GET['min_logrange'])) {
                $_SESSION['min_logrange'] = $_GET['min_logrange'];
            }
            if(isset($_GET['max_logrange'])) {
                $_SESSION['max_logrange'] = $_GET['max_logrange'];
            }

            if(! isset($_SESSION['min_fight_duration'])) {
                $_SESSION['min_fight_duration'] = 30;
            }
            if(! isset($_SESSION['language'])) {
                $_SESSION['language'] = 'de';
            }

            // check if cache for this query is available
            if(isset($_SESSION['user_id']) && isset($_SESSION['log_id']) && isset($_SESSION['min_fight_duration']) && isset($_SESSION['language'])) {
                if(! isset($_SESSION['min_logrange']) || ! isset($_SESSION['max_logrange'])) {
                    $logrange = 'full';
                } else {
                    $logrange = $_SESSION['min_logrange'].'-'.$_SESSION['max_logrange'];
                }
                $cache_stats_filename = 'cache/stats_'.$_SESSION['user_id'].'_'.$_SESSION['log_id'].'_'.$_SESSION['min_fight_duration'].'_'.$_SESSION['language'].'_'.$logrange;
                if(file_exists($cache_stats_filename) && !$cacherenew==1) {
                    readfile($cache_stats_filename);
                    exit();
                }
            }
            break;
        case "session_reset":
            $_SESSION = array();
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();

        case "demo":
            $_SESSION['user_id'] = 1;
            $_SESSION['user_name'] = "Demo";
            $_SESSION['user_email'] = "";
            break;
        case "login":
            $login_user = mysql_escape_string($_POST['username']);
            $login_pass = mysql_escape_string($_POST['password']);

            include("include/class.PasswordHash.php");
            $t_hasher = new PasswordHash(8, TRUE);

            $res = sql_query("select user_id, username, user_email, user_password
                from forum_users
                where username = '$login_user'");
            list($id, $name, $mail, $database_password) = sql_fetch_row($res);
            if($check = $t_hasher->CheckPassword($login_pass, $database_password)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $mail;
            } else {
                $login_message = "Falsche Benutzerdaten. Versuch's nochmal :)";
            }
            unset($t_hasher);
            break;
        case "logdelete":
            if($_SESSION['user_id'] != 1) {
                $log_id_list = join(',', $_POST['delete_logfile']);
                sql_query("delete from logfile
                    where id in (".$log_id_list.")
                        and uploader_id = ".$_SESSION['user_id']);
                sql_query("delete from data
                    where logfile_id in (".$log_id_list.")
                        and uploader_id = ".$_SESSION['user_id']);
                $_SESSION['log_id']=0;
            }
            header("Location: ".$_SERVER['PHP_SELF']."?options=1");
            exit();
        case "logupload":
            if($_SESSION['user_id'] && $_SESSION['user_id']!=1) {
                if($_FILES['logfile']['tmp_name']) {
                    @mkdir('upload/'.$_SESSION['user_id']);
                    $filename = 'upload/'.$_SESSION['user_id'].'/'.basename($_FILES['logfile']['name']);
                    if(move_uploaded_file($_FILES['logfile']['tmp_name'], $filename)) {
                        sql_query("insert into logfile (uploader_id, filename, timestamp)
                            values (".$_SESSION[user_id].", '".$filename."', now())");
                        $res = sql_query("select max(id) from logfile where uploader_id = ".$_SESSION[user_id]);
                        list($logfile_id) = sql_fetch_row($res);
                        $_SESSION['log_id'] = $logfile_id;
                
                        $parser = new Parser($filename, $logfile_id);
                        $parser->read_loglines();
                        $parser->gather_logdata();
                        if(count($parser->players) > 3) {
                            $playerlist = join(', ', array_slice(array_keys($parser->players), 0, 3)).'...';
                        } else {
                            $playerlist = join(', ', array_keys($parser->players));
                        }
                        if(! $parser->fight_count) {
                            sql_query("delete from logfile where id=".$logfile_id);
                            
                            $errormessage = "Das Log enthielt keine Kampfdaten.";
                            $errormessage_returnto = "document.location.href=\"?op=noop\"";
                        } else {
                            $notes = '['.date("d.m. H:i", $parser->start_timestamp).'-'.date("H:i", $parser->end_timestamp).'] '.$playerlist.': '.$parser->fight_count.' Kämpfe, '.(count($parser->actors)-1).' Gegner';
                            sql_query("update logfile set notes='".$notes."' where id='".$logfile_id."'");
                
                            // insert new effects, abilities etc.
                            if($parser->language) {
                                if($parser->abilities) {
                                    foreach($parser->abilities as $key => $value) {
                                        if($key && $value) {
                                            $__value = mysql_escape_string($value);
                                            sql_query("insert into ability (id, ".$parser->language.") values (".$key.", '".$__value."')
                                                ON DUPLICATE KEY UPDATE $parser->language='".$__value."'");
                                        }
                                    }
                                }
                                unset($parser->abilities);
                                if($parser->actors) {
                                    foreach($parser->actors as $key => $value) {
                                        if($key && $value) {
                                            $__value = mysql_escape_string($value);
                                            sql_query("insert into actor (id, ".$parser->language.") values (".$key.", '".$__value."')
                                                ON DUPLICATE KEY UPDATE $parser->language='".$__value."'");
                                        }
                                    }
                                }
                                unset($parser->actors);
                                if($parser->effects) {
                                    foreach($parser->effects as $key => $value) {
                                        if($key && $value) {
                                            $__value = mysql_escape_string($value);
                                            sql_query("insert into effect (id, ".$parser->language.") values (".$key.", '".$__value."')
                                                ON DUPLICATE KEY UPDATE $parser->language='".$__value."'");
                                        }
                                    }
                                }
                                unset($parser->effects);
                                if($parser->effect_types) {
                                    foreach($parser->effect_types as $key => $value) {
                                        if($key && $value) {
                                            $__value = mysql_escape_string($value);
                                            sql_query("insert into effect_type (id, ".$parser->language.") values (".$key.", '".$__value."')
                                                ON DUPLICATE KEY UPDATE $parser->language='".$__value."'");
                                        }
                                    }
                                }
                                unset($parser->effects_types);
                                if($parser->hit_types) {
                                    foreach($parser->hit_types as $key => $value) {
                                        if($key && $value) {
                                            $__value = mysql_escape_string($value);
                                            sql_query("insert into hit_type (id, ".$parser->language.") values (".$key.", '".$__value."')
                                                ON DUPLICATE KEY UPDATE $parser->language='".$__value."'");
                                        }
                                    }
                                }
                                unset($parser->hit_types);
                            }
                            $parser->serialize_self();
                            header("Location: ".$_SERVER['PHP_SELF']."?op=setopt");
                            exit();
                        }
                    } else {
                        $errormessage = "Die Datei konnte nicht hochgeladen werden.";
                        $errormessage_returnto = "document.location.href=\"?op=noop\"";
                    }
                } else {
                    $errormessage = "Du hast keine Datei zum Upload ausgewählt oder sie war zu groß.";
                    $errormessage_returnto = "document.location.href=\"?op=noop\"";
                }
            }
            break;
        case "bugreport":
            if($_POST['bugreport']) {
                
                $subject = $_POST['subject'].' ('.preg_replace('#/?batalyser/?#', '', dirname($_SERVER['REQUEST_URI'])).' v'.$version.')';
                
                sql_query("insert into bugreport (subject, message, user_id, logfile_id) 
                    values ('".$subject."', '".$_POST['bugreport']."', '".$_SESSION['user_id']."', '".$_SESSION['log_id']."')");
                $res = sql_query("select max(id) from bugreport where user_id='".$_SESSION['user_id']."'");
                list($bugreport_id) = sql_fetch_row($res);

                ob_start();
                print "\n\n\nSESSION: ";
                print_r($_SESSION);
                $environmental = ob_get_clean();
                mail(
                    'marduc@batalyser.net',
                    'Batalyser-Bugreport: '.$subject,
                    $_POST['bugreport'].$environmental,
                    'From: '.$_POST['email']
                );
                $phpbb_bridge_url = 'http://'.dirname($_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']).'/phpbb.php';
                header('Location: '.$phpbb_bridge_url.'?op=bugreport&id='.$bugreport_id.'&returnto='.base64_encode($_SERVER['PHP_SELF'].'?op=setopt&message='.urlencode('Vielen Dank für deine Nachricht! Du findest sie im Forum unter [post_url]')));
            } else {
                header("Location: ".$_SERVER['PHP_SELF']."?message=Du hast vergessen eine Nachricht einzugeben.");
            }
            exit();
            break;
    }

    if($_SESSION['user_id']) {
        // select users logfiles
        $res = sql_query("select id, UNIX_TIMESTAMP(timestamp), notes, filename, uploader_id
            from logfile
            where uploader_id = ".$_SESSION['user_id']."
            order by timestamp desc");
        while(list($id, $timestamp, $notes, $filename, $uploader_id) = sql_fetch_row($res)) {
            if(file_exists($filename)) {
                $logfiles[$id]['timestamp'] = $timestamp;
                $logfiles[$id]['notes'] = $notes;
                $logfiles[$id]['filename'] = $filename;
                $logfiles[$id]['uploader_id'] = $uploader_id;
            } else {
                sql_query("delete from logfile where id=".$id);
            }
        }
    }
    
    // ops needing database and user
    switch($op) {
        case "pixelmap":
            if(isset($_GET['dim_x'])) {
                $dim_x = $_GET['dim_x'];
            } else {
                $dim_x = 1024;
            }
            if(isset($_GET['dim_y'])) {
                $dim_y = $_GET['dim_y'];
            } else {
                $dim_y = 300;
            }
            if(isset($_GET['eventtext'])) {
                $eventtext = $_GET['eventtext'];
            } else {
                $eventtext = 1;
            }
            $conditions=array();
            if(isset($_GET['conditions_lvalue'])) {
                for($c=0; $c<count($_GET['conditions_lvalue']); $c++) {
                    $conditions[$_GET['conditions_lvalue'][$c]] = $_GET['conditions_rvalue'][$c];
                }
            }

            header("Content-type: image/png");
            $cache_filename_pixelmap = cachefilename_pixelmap($_GET['min_id'], $_GET['max_id'], $_GET['section'], $_GET['secondary_sections'], $_GET['char'], $conditions, $eventtext, $dim_x, $dim_y);
            if(file_exists($cache_filename_pixelmap) && !$cacherenew=1) {
                readfile($cache_filename_pixelmap);
            } else {
                pixelmap($_GET['min_id'], $_GET['max_id'], $_GET['section'], $_GET['secondary_sections'], $_GET['char'], $conditions, $eventtext, $dim_x, $dim_y);
            }

            exit();
        case "piechart":
            header("Content-type: image/png");
            piechart($_GET['values'], $_GET['labels']);
            exit();
    }

    ob_start();
    include("header.php");

    if($errormessage) {
        if(!$errormessage_returnto) {
            $errormessage_returnto = 'history.back()';
        }
        print "<div id='dialog_error'><p>$errormessage</p><center><button onClick='".$errormessage_returnto."'>Zurück</button></center></div>";
        endofpage();
    }

    if($message) {
        $message = str_replace('&amp;', '&', $message);
        $message = preg_replace('#(https?://\S+)#', '<a href="$1">$1</a>', $message);
        print "<div id='dialog_message'><p>".$message."</p><center><button onClick='document.location.href=\"".$_SERVER['PHP_SELF']."?op=setopt\"'>Zurück</button></center></div>";
        endofpage();
    }

    if(! $_SESSION['user_id']) {
        print "<div id='dialog_login'>
                <p>Batalyser nutzt die Foren-Accounts zur Userverwaltung. Melde Dich bitte mit deinem Foren-Login an.</p>
                <form action='' method='POST'>
                    <table>
                        <tr><td>Login:</td>     <td><input type='text' name='username'></td></tr>
                        <tr><td>Passwort:</td>  <td><input type='password' name='password'></td></tr>
                        <tr><td colspan='2' align='center'><input type='submit' value='Anmelden'></td></tr>
                    </table>";
        if($login_message) {
            print "<p style='color:red; text-align:center'>".$login_message."</p>";
        }
        print "<input type='hidden' name='op' value='login'>

                </form>
                <p><a href='/forum/ucp.php?mode=register'>Registrieren</a> <a href='?op=demo'>Demo</a></p>
            </div>";
            endofpage();
    }

    // at this point we have a logged in user and a chosen logfile
    // let's go

    print "<div id='navbar' style='float:left'>
            <button onClick='document.location.href=\"/forum\"'>Forum</button>
        </div>
        <div style='float:left; margin-left:25px'>
            <b>Logfile:</b>    ".$logfiles[$_SESSION['log_id']]['notes']." |
            <b>Mindest-Kampfdauer:</b>     ".$_SESSION['min_fight_duration']."s |
            <b>Sprache:</b>                ".$languages[$_SESSION['language']]."
        </div>
        <div style='text-align:right'>
            Eingeloggt als: ".$_SESSION['user_name']."
            <button onClick='document.location.href=\"?op=session_reset\"'>".guil('logoff')."</button>
            <button id='button_open_dialog_options'>".guil('options')."</button>
            <button id='button_open_dialog_help'>?</button>
        </div>

        <div id='dialog_options'>
            <div id='accordion_options'>
                <h3><a href='#'>".guil('view')."</a></h3>
                <div>
                    <form action='' method='GET'>
                        <table width='100%'>
                            <tr>
                                <td nowrap='nowrap'>Logfile anzeigen:</td>
                                <td colspan='2'><select name='logfile'>";
                                foreach($logfiles as $logfile_id => $logfile) {
                                    $selected = "";
                                    if($logfile_id == $_SESSION['log_id']) {
                                        $selected = " selected='selected' ";
                                    }
                                    print "<option $selected value='".$logfile_id."'>".$logfile['notes']."</option>";
                                }
                                print "</select></td>
                            </tr>
                            <tr>
                                <td nowrap='nowrap'>Mindest-Kampfdauer (Sekunden):</td>
                                <td width='70%'><div id='min_fight_duration_slider'></div></td>
                                <td><input type='text' id='min_fight_duration_slider_value' name='min_fight_duration' value='' style='width:3em' readonly='readonly'></td>
                            </tr>
                            <!--tr>
                                <td nowrap='nowrap'>Logausschnitt:</td>
                                <td width='70%'><div id='logrange_slider'></div></td>
                                <td>
                                    <input type='text' id='logrange_slider_value1' name='min_logrange' value='".(isset($_SESSION['min_logrange'])?$_SESSION['min_logrange']:0)."' style='width:5em' readonly='readonly'>
                                    -
                                    <input type='text' id='logrange_slider_value2' name='max_logrange' value='".(isset($_SESSION['max_logrange'])?$_SESSION['max_logrange']:100)."' style='width:5em' readonly='readonly'>
                                </td>
                            </tr-->
                            <tr>
                                <td>Bevorzugte Sprache:</td>
                                <td>
                                    <select name='prefered_language'>";
                                        foreach($languages as $short => $long) {
                                            $selected = "";
                                            if($short == $_SESSION['language']) {
                                                $selected = "selected='selected'";
                                            }
                                            print "<option $selected value='$short'>$long</option>";
                                        }
                                    print "</select>
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan='3' align='right'><input type='submit' value='Log mit diesen Einstellungen ansehen'></td>
                            </tr>
                        </table>
                        <input type='hidden' name='op' value='setopt'>
                    </form>
                </div>

                <h3><a href='#'>Upload</a></h3>
                <div>
                    <form action='' method='POST' enctype='multipart/form-data' name='uploadform'>
                        <input type='hidden' name='MAX_FILE_SIZE' value='".MAX_FILE_SIZE."'><!-- 512 kB -->
                        <p>Logfile hochladen (max. ".sprintf("%s", 1024 * (MAX_FILE_SIZE / pow(1024, floor((strlen(MAX_FILE_SIZE) - 1) / 3))))."kB): <input type='file' name='logfile'></p>
                        <p style='text-align:right'><input type='submit' value='Upload starten' id='button_start_upload' onClick='document.uploadform.submit()' $disable_ui_element>";
                        if($_SESSION['user_id']==1) {
                            print "<div style='text-align:right'><small>In der Demo ist der Upload deaktiviert.</small></div>";
                        }
                        print "
                        <input type='hidden' name='op' value='logupload'>
                    </form>
                </div>";

            if($logfiles) {
                print "<h3><a href='#'>Vorhandene Logs</a></h3>
                <div>
                    <p>Deine Logfiles:</p>
                    <form action='' method='POST'>
                        <table class='dataTableAutoWidth' id='datatable_optionsLogfiles'>
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Datum</th>
                                    <th>von</th>
                                    <th></th>
                                    <th>bis</th>
                                    <th>Chars</th>
                                    <th>Kämpfe</th>
                                    <th>Gegner</th>
                                    <th>Dateiname</th>
                                    <th>Upload</th>
                                    <th>Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>";
                foreach($logfiles as $logfile_id => $logfile) {
                    print "<tr>
                                <td><input type='checkbox' name='delete_logfile[]' value='".$logfile_id."'></td>";
                    preg_match('/\[(\d\d\.\d\d\.) (\d\d:\d\d)-(\d\d:\d\d)\] (.*?): (\d+) Kämpfe, (\d+) Gegner/', $logfile['notes'], $matches);
                    print "
                                <td>".$matches[1]."</td>
                                <td>".$matches[2]."</td>
                                <td>-</td>
                                <td>".$matches[3]."</td>
                                <td>".$matches[4]."</td>
                                <td>".$matches[5]."</td>
                                <td>".$matches[6]."</td>
                                <td>".preg_replace('#/?upload(/'.$_SESSION['user_id'].')?/?#', '', $logfile['filename'])."</td>
                                <td>".date('Y-m-d H:i:s', $logfile['timestamp'])."</td>
                                <td><a href='?op=setopt&logfile=".$logfile_id."'>[Anzeigen]</a> <a href='?op=logdownload&logfile=".$logfile_id."'>[Download]</a></td>
                            </tr>";
                }
                print "</tbody>
                        </table>
                        <input type='hidden' name='op' value='logdelete'>
                        <p style='text-align:left'><input type='submit' value='Gewählte Logfiles löschen' $disable_ui_element></p>";
                        if($_SESSION['user_id']==1) {
                            print "<div style='text-align:left'><small>In der Demo ist das Löschen deaktiviert.</small></div>";
                        }
                print "</form>
                </div>";
            }
            print "<h3><a href='#'>Bugreport senden</a></h3>
                <div>
                    <p>Batalyser ist noch in einem sehr frühen Entwicklungsstatium, es ist daher sehr wahrscheinlich, dass du Bugs finden wirst. Wenn dem so ist, freu ich mich über deine Meldung!</p>
                    <p>Deine Nachricht wird automatisch ins <a href='http://batalyser.net/forum/viewforum.php?f=4'>Bugreports-Forum</a> gepostet. Wenn du im Forum angemeldet bist unter deinem Namen, ansonsten als Gast.
                    Es werden automatisch bestimmte Session-Daten ermittelt (mit welchem User bist du angemeldet, welches Log schaust du grade an, welche Optionen sind gesetzt..) und mir zugeschickt, diese werden aber nicht im Forum veröffentlicht.</p>
                    <form action='' method='POST'>
                        <table>
                            <tr><td valign='top'>Eine kurze Überschrift:</td><td><input type='text' name='subject' maxlength='40' size='40'></td></tr>
                            <tr><td valign='top'>Beschreibe hier den Fehler:</td><td><textarea name='bugreport' cols='70' rows='6'></textarea></td></tr>
                            <tr><td>Deine E-Mail-Adresse (optional):</td><td><input type='text' name='email' size='70' value='".($_SESSION['user_email'])."'></tr>
                            <tr><td colspan='2' align='right'>Vielen Dank! <input type='submit'></td></tr>
                        </table>
                        <input type='hidden' name='op' value='bugreport'>
                    </form>
                </div>
            </div><!-- accordion options -->
        </div><!-- dialog options -->

        <div id='dialog_upload'>
            <p>Der Upload wird gestartet. Im Anschluss wird das Combatlog geparst. Das kann einige Minuten dauern, bitte hab ein wenig Geduld.</p>
            <p>Wenn der Parser fertig ist, wird dieses Fenster geschlossen und der Einstellungsdialog angezeigt.</p>
        </div>

        <div id='dialog_help'>
            <img src='http://geekandpoke.typepad.com/.a/6a00d8341d3df553ef014e8b8f1c5b970d-800wi' alt=''>
        </div>";

    if(! $_SESSION['min_fight_duration'] || !$_SESSION['log_id']) {
        endofpage();
    }

    print flushandcache(1);
    // at this point we have a logged in user, a chosen logfile and a minimum fight duration
    // we are able to gather logdata now
    
    $benchmark->snapshot('before_loading_parser');

    if(!isset($parser)) {
        $parser = new Parser($logfiles[$_SESSION['log_id']]['filename'], $_SESSION['log_id']);
    }
    $benchmark->snapshot('after_loading_parser');

    if(count($parser->players)<1) {
        header("Location: ?op=setopt&logfile=&message=Das Logfile enthält keine Chardaten");
        exit();
    }

    unset($res);
    
    print "<div class='accordion'>";
    foreach(array_keys($parser->players) as $char) {
        if(!$char || !$parser->players[$char]['fights']) {
            break;
        }
        $_char = preg_replace('/[^a-z0-9-_]/i', '_', $char);
        $fights_displayed=0;
        $fights_hidden=0;
        $fight_nr=0;

        $min_id = $parser->players[$char]['min_id'];
        $max_id = $parser->players[$char]['max_id'];
       
        $summary_duration = 0;
        $summary_damage   = 0;
        $summary_threat   = 0;
        $summary_healed   = 0;
        
        print "<h2><a name='stats_for_".$_char."'>Damagestats für ".$char." (".$fights_displayed." ".($fights_displayed==1?"Kampf":"Kämpfe")." angezeigt, ".$fights_hidden." versteckt)</a></h2>
                    <div> <!-- empty div that fixes accordion width bug -->
                        <div class='accordion'>";
        
        foreach($parser->players[$char]['fights'] as $fight) {
            $fight_start_id = $fight['start_id'];
            $fight_start_timestamp = $fight['start_timestamp'];
            $fight_end_id = $fight['end_id'];
            $fight_end_timestamp = $fight['end_timestamp'];
            
            if(!$min_timestamp) {
                $min_timestamp = $fight['start_timestamp'];
            }
            $max_timestamp = $fight['end_timestamp'];
            
            $fight_nr++;
            $single_fight_duration = $fight_end_timestamp - $fight_start_timestamp;

            if($single_fight_duration >= $_SESSION['min_fight_duration']) {
                $fights_displayed++;
                
                // tabs are able to fetch logdata themselves, but this is rather cpu-intensive
                // so prefetch single-fight-data
                unset($parser->logdata);
                $parser->read_loglines($fight_start_id, $fight_end_id);
                $parser->gather_logdata();
                unset($parser->loglines);
                
                $tabs = array();
                $tabs[] = new Tab_DpsHpsTps_per_Target(
                        'fight'.$fight_nr.'-'.preg_replace('/\s/', '', $_char).'-damage',
                        $char,
                        $fight_start_id,
                        $fight_end_id
                    );
                $benchmark->snapshot('fight_'.$fight_nr.'_tab_1');
                $tabs[] = new Tab_Char_DpsTps_per_Ability(
                        'fight'.$fight_nr.'-'.preg_replace('/\s/', '', $_char).'-dmgthreatabilities',
                        $char,
                        $fight_start_id,
                        $fight_end_id
                    );
                $benchmark->snapshot('fight_'.$fight_nr.'_tab_2');
                $tabs[] = new Tab_Char_HpsTps_per_Ability(
                        'fight'.$fight_nr.'-'.preg_replace('/\s/', '', $_char).'-healthreatabilities',
                        $char,
                        $fight_start_id,
                        $fight_end_id
                    );
                $benchmark->snapshot('fight_'.$fight_nr.'_tab_3');
                $tabs[] = new Tab_Enemies_Damage_to_Char(
                        'fight'.$fight_nr.'-enemies-vs-'.preg_replace('/\s/', '', $_char).'-sources',
                        $char,
                        $fight_start_id,
                        $fight_end_id
                    );
                $benchmark->snapshot('fight_'.$fight_nr.'_tab_4');
                $tabs[] = new Tab_Full_Fight_Stats(
                        'fight'.$fight_nr.'-'.$_char.'-fullfight-stats',
                        $char,
                        $fight_start_id,
                        $fight_end_id
                    );
                $benchmark->snapshot('fight_'.$fight_nr.'_tab_5');
                $tabs[] = new Tab_Full_Fight_Graphs(
                        'fight'.$fight_nr.'-'.$_char.'-fullfight-graphs',
                        $char,
                        $fight_start_id,
                        $fight_end_id
                    );
                $benchmark->snapshot('fight_'.$fight_nr.'_tab_6');
                // Daten in Tabs ausgeben
    
                // Dauer lesbar machen
                $__time = $single_fight_duration;
                $hours = floor($__time / (60*60));
                $__time = $__time - $hours * (60*60);
                $minutes = floor($__time / (60));
                $__time = $__time - $minutes * 60;
                $seconds = $__time;

                $fight_title  = "Kampf ".$fight_nr;
                $fight_title .= ": Dauer ".sprintf('%s:%02s:%02s',$hours,$minutes,$seconds).", ";
                $fight_title .= "am ".date('d.m.', $fight_start_timestamp)." von ".date('H:i:s', $fight_start_timestamp)." bis ".date('H:i:s', $fight_end_timestamp);
                $fight_title .= " [".round($fight['sum']['damage'] / $single_fight_duration, 2)." DPS ";
                $fight_title .= "| ".round($fight['sum']['threat'] / $single_fight_duration, 2)." TPS ";
                $fight_title .= "| ".round($fight['sum']['healed'] / $single_fight_duration, 2)." HPS ] ";
                if($fight['sum']['damage']>0) {
                    $fight_title .= $fight['sum']['damage']." Schaden verursacht, ";
                }
                if($fight['sum']['healed']>0) {
                    $fight_title .= $fight['sum']['healed']." Punkte geheilt, ";
                }
                $fight_title .= $fight['sum']['threat']." Bedrohung erzeugt.";

                print "<h3><a href='#'>".$fight_title."</a></h3>
                    <div>
                        <div class='tabs'>
                            <ul>";
                                foreach($tabs as $tab) {
                                    print $tab->nameplate();
                                }
                                // $output_accordion_page .= "<li><a href='ajax_tab.php?tab=Tab_DpsHpsTps_per_Target&char=".$char."&min_id=".$fight['start_id']."&max_id=".$fight['end_id']."'>bla</a></li>";
                print "     </ul>";

                foreach($tabs as $tab) {
                    print $tab->tabcontent();
                    print flushandcache();
                }
                print "</div>
                    </div>";
                    
                // collect data for summary tab
                $summary_duration += $single_fight_duration;
                $summary_damage += $fight['sum']['damage'];
                $summary_threat += $fight['sum']['threat'];
                $summary_healed += $fight['sum']['healed'];
                
                $benchmark->snapshot('fight_'.$fight_nr.'_printed');
            } else {
                $fights_hidden++;
            }
        }

        // Gesamt
        if($fights_displayed<1 && $fight_nr>0) {
            print "Tipp: Wenn keine einzelnen Kämpfe angezeigt werden, dann prüfe ob du in den Optionen eine zu hohe Mindest-Kampfdauer gewählt hast.";
        }

        $tabs = array();
        $benchmark->snapshot('before_summary');
        
        // regarding the eval bullshit below:
        //   I'd rather put all tabs into an array to loop thru and call their nameplate and tabcontent methods
        //   but doing so eats memory. lots.
        //   using eval frees memory used by each tab object immediately
        $tabs = array('Tab_DpsHpsTps_per_Target', 'Tab_Char_DpsTps_per_Ability', 'Tab_Char_HpsTps_per_Ability', 'Tab_Enemies_Damage_to_Char', 'Tab_Full_Fight_Stats', 'Tab_Full_Fight_Graphs');
        //$tabs = array('Tab_DpsHpsTps_per_Target', 'Tab_Char_DpsTps_per_Ability', 'Tab_Char_HpsTps_per_Ability', 'Tab_Enemies_Damage_to_Char');

        $summary_title  = "Alle Kämpfe (".$fights_displayed."): ";
        $summary_title .= "Dauer ".seconds_to_readable($summary_duration)." (aktiv), ";
        if($summary_duration) {
            $summary_title .= "am ".date('d.m.', $min_timestamp)." zwischen ".date('H:i:s', $min_timestamp)." und ".date('H:i:s', $max_timestamp);
            $summary_title .= " [".round($summary_damage / $summary_duration, 2)." DPS ";
            $summary_title .= "| ".round($summary_threat / $summary_duration, 2)." TPS ";
            $summary_title .= "| ".round($summary_healed / $summary_duration, 2)." HPS ] ";
            if($summary_damage>0) {
                $summary_title .= $summary_damage." Schaden verursacht, ";
            }
            if($summary_healed>0) {
                $summary_title .= $summary_healed." Punkte geheilt, ";
            }
            $summary_title .= $summary_threat." Bedrohung erzeugt.";
        }
        
        unset($parser->logdata);
        $parser->read_loglines();
        $parser->gather_logdata();
        unset($parser->loglines);
        
        $tabs = array();
        $tabs[] = new Tab_DpsHpsTps_per_Target(
                'sum-'.preg_replace('/\s/', '', $_char).'-damage',
                $char,
                $min_id,
                $max_id
            );
        
        unset($parser->logdata);
        $parser->read_loglines();
        $parser->gather_logdata();
        unset($parser->loglines);
        $tabs[] = new Tab_Char_DpsTps_per_Ability(
                'sum-'.preg_replace('/\s/', '', $_char).'-dmgthreatabilities',
                $char,
                $min_id,
                $max_id
            );
        
        unset($parser->logdata);
        $parser->read_loglines();
        $parser->gather_logdata();
        unset($parser->loglines);
        $tabs[] = new Tab_Char_HpsTps_per_Ability(
                'sum-'.preg_replace('/\s/', '', $_char).'-healthreatabilities',
                $char,
                $min_id,
                $max_id
            );
        unset($parser->logdata);
        $parser->read_loglines();
        $parser->gather_logdata();
        unset($parser->loglines);
        $tabs[] = new Tab_Enemies_Damage_to_Char(
                'sum-enemies-vs-'.preg_replace('/\s/', '', $_char).'-sources',
                $char,
                $min_id,
                $max_id
            );
       
        print "<h3><a href='#'>".$summary_title."</a></h3>
            <div>
                <div>
                    <div class='tabs'>
                        <ul>";
                            foreach($tabs as $tab) {
                                $benchmark->snapshot($tab->name);
                                print $tab->nameplate();
                            }
                print " </ul>";
                
                foreach($tabs as $tab) {
                    print $tab->tabcontent();
                    print flushandcache();
                }
                print "</div>
            </div>
        </div>";
        unset($tabs);

        print "</div>"; // empty div that fixes accordion width bug
        print "</div>"; // accordion
        
    } // foreach $char
    print "</div>"; // accordion

    sql_logout();
    unset($guil);
    unset($output_accordion_page);
    unset($parser);
    $benchmark->snapshot('sum_printed');

    endofpage();

    function cachefilename_pixelmap($start_id, $end_id, $section, $snd_sections, $char, $conditions, $eventtext=1, $dim_x=1500, $dim_y=300) {
        if(isset($snd_sections) && is_array($snd_sections)) {
            $snd_sections_list = join('_', $snd_sections);
        } else {
            $snd_sections_list = 'none';
        }
        if(isset($conditions) && is_array($conditions)) {
            $conditions_list = md5(join('', $conditions));
        } else {
            $conditions_list = 'none';
        }

        return 'cache/pixelmap_'.$_SESSION['user_id'].'_'.$_SESSION['log_id'].'_'.$start_id.'-'.$end_id.'_'.$char.'_'.$conditions_list.'_'.$section.'_'.$snd_sections_list.$dim_x.'x'.$dim_y.'_'.$eventtext;
    }

    function pixelmap($start_id, $end_id, $section, $snd_sections, $char, $conditions, $eventtext=1, $dim_x=1500, $dim_y=300) {
        global $cacherenew;
        
        $parser = new Parser($logfiles[$_SESSION['log_id']]['filename'], $_SESSION['log_id']);
        $parser->read_loglines($_GET['min_id'], $_GET['max_id']);
        $parser->gather_logdata();

        $cache_filename = cachefilename_pixelmap($start_id, $end_id, $section, $snd_sections, $char, $conditions, $eventtext, $dim_x, $dim_y);
        if(file_exists($cache_filename) && !$cacherenew==1) {
            readfile($cache_filename);
            exit();
        }

        $s1overall = 0;
        if(preg_match('/(.*?)_overall$/', $section, $matches)) {
            $s1overall = 1;
            $section = $matches[1];
        }

        $section_operation="";
        if(preg_match('#^(.*?)([*/+-])(.*)$#', $section, $matches)) {
            $section_factor1 = $matches[1];
            $section_operation = $matches[2];
            $section_factor2 = $matches[3];
        }

        $min_y = 0;
        $max_y = 0;
        $max_y_overall = 0;
        $last_fetch = $start_id;
        for($id=$start_id; $id <= $end_id; $id++) {
            if($id >= $last_fetch + PARSER_MAX_FETCH) {
                $last_fetch +=PARSER_MAX_FETCH;
                unset($parser->loglines);
                unset($parser->logdata);
                $parser->read_loglines($id, $id+PARSER_MAX_FETCH);
                $parser->gather_logdata();
            }
            $conditions_complied=1;
            foreach($conditions as $lvalue => $rvalue) {
                if($rvalue == $char) {
                    if(!preg_match('/^'.$char.'(:.+)?/', $parser->logdata[$id][$lvalue])) {
                        $conditions_complied=0;
                        break;
                    }
                } else {
                    if($parser->logdata[$id][$lvalue]!=$rvalue) {
                        $conditions_complied=0;
                        break;
                    }
                }
            }
            if($conditions_complied) {
                switch($section_operation) {
                    case "+": $line_value = $parser->logdata[$id][$section_factor1] + $parser->logdata[$id][$section_factor2]; break;
                    case "-": $line_value = $parser->logdata[$id][$section_factor1] - $parser->logdata[$id][$section_factor2]; break;
                    case "*": $line_value = $parser->logdata[$id][$section_factor1] * $parser->logdata[$id][$section_factor2]; break;
                    case "/": $line_value = $parser->logdata[$id][$section_factor1] / $parser->logdata[$id][$section_factor2]; break;
                    default:  $line_value = $parser->logdata[$id][$section];
                }
                if($s1overall) {
                    $max_y_overall = $max_y_overall + $line_value;
                    $max_y = max($max_y, $max_y_overall);
                    $min_y = min($min_y, $max_y_overall);
                } else {
                    $max_y = max($max_y, $line_value);
                    $min_y = min($min_y, $line_value);
                }
            }
        }
        $max_x = $end_id-$start_id;
        $x_offset = 50;
        $y_offset = 30;
        if($max_y) {
            $x_faktor = ($dim_x - $x_offset) / ($max_x+1);
            $y_faktor = ($dim_y - $y_offset) / ($max_y+1);
            
            $image = imagecreatetruecolor ($dim_x+50, $dim_y);
            $color['damage-heal']         = imagecolorallocate($image, 255,   0,   0);
            $color['damage_received']     = imagecolorallocate($image, 180,   0,   0);
            $color['threat']              = imagecolorallocate($image, 255,   0,   0);
            $color['threat_overall']      = imagecolorallocate($image, 150,   0,   0);
            $color['damage']              = imagecolorallocate($image, 0,   150,   0);
            $color['damage_dealt']        = imagecolorallocate($image, 0,   150,   0);
            $color['heal']                = imagecolorallocate($image, 0,     0, 255);
            $color['heal_received']       = imagecolorallocate($image, 0,     0, 255);
            $color['red']                 = imagecolorallocate($image, 255,   0,   0);
            $color['black']               = imagecolorallocate($image, 0,     0,   0);
            $color['white']               = imagecolorallocate($image, 255, 255, 255);
            
            // white bg
            imagefilledrectangle($image, 0, 0, $dim_x+50, $dim_y, $color['white']);
            // title
            $title = $section;
            foreach($conditions as $lvalue => $rvalue) {
                $title .= " ".$lvalue."=".$rvalue;
            }
            $title .= " [".date('H:i:s', $parser->logdata[$start_id]['timestamp'])." - ".date('H:i:s', $parser->logdata[$end_id]['timestamp'])."]";
            imagestring($image, 5, $x_offset+2, $dim_y-$y_offset+15, $title, $color[$section]);
            // rulers
            imageline($image, 0, $dim_y-$y_offset,     $dim_x, $dim_y-$y_offset,  $color['black']);
            imageline($image, $x_offset-1, $dim_y,     $x_offset-1, 0,            $color['black']);
            // ruler x-axis
            imagestring($image, 2, $x_offset+2, $dim_y-($y_offset), 'time(s)', $color['black']);
            for($r=$max_x/20; $r<=$max_x; $r+=$max_x/20) {
                if(round($r)>0) {
                    imagestring($image, 2, $x_offset + ($x_faktor*ceil($r)), $dim_y-($y_offset), ceil($r), $color['black']);
                }
            }
            
            // ruler y-axis
            imagestringup($image, 2, $x_offset - 14, $dim_y-($y_offset+2), $section, $color['black']);
            for($r=0; $r<=$max_y; $r+=$max_y/15) {
                imagestring($image, 2, 1, $dim_y-$y_offset-($y_faktor*ceil($r)), ceil($r), $color['black']);
            }
            
            // section-graph
            if(isset($snd_sections)) {
                $draw_sections = $snd_sections;
                $draw_sections[] = $section;
            } else {
                $draw_sections = array($section);
            }
            
            $s2=0;
            $line_value = array();
            foreach($draw_sections as $draw_section) {
                $t=0;
                $last_offset=1;
                $sum = 0;
                $s2overall = 0;
                $draw_section_operation = "";
            
                if($draw_section == $section) {
                    $s2overall = $s1overall;
                } elseif(preg_match('/(.*?)_overall$/', $draw_section)) {
                    $s2overall = 1;
                    $draw_section = $matches[1];
                }
            
                if(preg_match('#^(.*?)([*/+-])(.*)$#', $draw_section, $matches)) {
                    $draw_section_factor1 = $matches[1];
                    $draw_section_operation = $matches[2];
                    $draw_section_factor2 = $matches[3];
                }
            
                $last_fetch = $start_id;
                $parser->read_loglines($start_id, $end_id);
                $parser->gather_logdata();
                for($id=$start_id; $id <= $end_id; $id++) {
                    if($id >= $last_fetch + PARSER_MAX_FETCH) {
                        $last_fetch +=PARSER_MAX_FETCH;
                        unset($parser->loglines);
                        unset($parser->logdata);
                        $parser->read_loglines($id, $id+PARSER_MAX_FETCH);
                        $parser->gather_logdata();
                    }
                    $conditions_complied=1;
                    foreach($conditions as $lvalue => $rvalue) {
                        if($parser->logdata[$id][$lvalue]!=$rvalue) {
                            $conditions_complied=0;
                            break;
                        }
                    }
                    switch($draw_section_operation) {
                        case "+": $line_value[$id] = $parser->logdata[$id][$draw_section_factor1] + $parser->logdata[$id][$draw_section_factor2]; break;
                        case "-": $line_value[$id] = $parser->logdata[$id][$draw_section_factor1] - $parser->logdata[$id][$draw_section_factor2]; break;
                        case "*": $line_value[$id] = $parser->logdata[$id][$draw_section_factor1] * $parser->logdata[$id][$draw_section_factor2]; break;
                        case "/": $line_value[$id] = $parser->logdata[$id][$draw_section_factor1] / $parser->logdata[$id][$draw_section_factor2]; break;
                        default:  $line_value[$id] = $parser->logdata[$id][$draw_section];
                    }
                    if($line_value>0 && $conditions_complied) {
                        if($s2overall) {
                            $y_position_1 = ($dim_y - $y_offset - $sum * $y_faktor);
                            $sum += $line_value[$id];
                            $y_position_2 = ($dim_y - $y_offset - $sum * $y_faktor);
                        } else {
                            $y_position_1 = ($dim_y - $y_offset - $line_value[$id-$last_offset] * $y_faktor);
                            $y_position_2 = ($dim_y - $y_offset - $line_value[$id] * $y_faktor);
                        }
                        imageline($image,
                            $x_offset + (($t-$last_offset)*$x_faktor)+$x_faktor,  $y_position_1,
                            $x_offset + ($t*$x_faktor)+$x_faktor,                 $y_position_2,
                            $color[$draw_section]);
                        imagestring($image, 1,
                            $x_offset + ($t*$x_faktor)+$x_faktor - 2,             $y_position_2 -4,
                            'o', $color[$draw_section]);
                        $last_offset=1;
                    } else {
                        $last_offset++;
                    }
                    $t++;
                }
                if($draw_section != $section) {
                    imagestringup($image, 1, $dim_x + ($s2*9), $dim_y-($y_offset+16), $draw_section, $color[$draw_section]);
                    $s2++;
                }
            }
            
            // events
            $t=0;
            $last_fetch = $start_id;
            $parser->read_loglines($start_id, $end_id);
            $parser->gather_logdata();
            for($id=$start_id; $id <= $end_id; $id++) {
                if($id >= $last_fetch + PARSER_MAX_FETCH) {
                    $last_fetch +=PARSER_MAX_FETCH;
                    unset($parser->loglines);
                    unset($parser->logdata);
                    $parser->read_loglines($id, $id+PARSER_MAX_FETCH);
                    $parser->gather_logdata();
                }
                switch($parser->logdata[$id]['effect_id']) {
                    case DEATH:
                        if($parser->logdata[$id]['target_name'] == $char) {
                            // char dead
                            // x
                            imagestring($image, 3, $x_offset + ($t*$x_faktor) - 2+$x_faktor, ($dim_y - $y_offset - $parser->logdata[$id][$section] * $y_faktor), 'X', $color['red']);
                            // dashed line
                            imagedashedline($image, $x_offset + ($t*$x_faktor)+$x_faktor, ($dim_y - $y_offset - $parser->logdata[$id][$section] * $y_faktor) ,
                                                    $x_offset + ($t*$x_faktor)+$x_faktor, ($dim_y - $y_offset), $color['red']);
                            if($eventtext==1) {
                                // char name
                                $x_position = $x_offset + ($t*$x_faktor) + 2+$x_faktor;
                                if($x_position > $dim_x - 40) {
                                    $x_position -= 40;
                                }
                                imagestring($image, 1, $x_position, ($dim_y - $y_offset - 13), $parser->logdata[$id]['target_name'], $color['red']);
                            }
                        } elseif($parser->logdata[$id]['source_name'] == $char) {
                            // npc dead
                            // x
                            imagestring($image, 3, $x_offset + ($t*$x_faktor) - 2 +$x_faktor, ($dim_y - $y_offset - $parser->logdata[$id][$section] * $y_faktor - 9), 'x', $color['black']);
                            // dashed line
                            imagedashedline($image, $x_offset + ($t*$x_faktor)+$x_faktor, ($dim_y - $y_offset - $parser->logdata[$id][$section] * $y_faktor) ,
                                                    $x_offset + ($t*$x_faktor)+$x_faktor, ($dim_y - $y_offset), $color['black']);
                            if($eventtext==1) {
                                // npc name
                                $x_position = $x_offset + ($t*$x_faktor) + 2+$x_faktor;
                                if($x_position > $dim_x - 40) {
                                    $x_position -= 40;
                                }
                                imagestring($image, 1, $x_position, ($dim_y - $y_offset - 13), $parser->logdata[$id]['target_name'], $color['black']);
                            }
                        }
                }
                $t++;
            }
        } else {
            $image = imagecreatetruecolor ($dim_x+$x_offset, 20);
            $color['white']               = imagecolorallocate($image, 255, 255, 255);
            $color['black']               = imagecolorallocate($image, 0,     0,   0);
            imagefilledrectangle($image, 0, 0, $dim_x+$x_offset, 20, $color['white']);
            imagestring($image, 3, 1, 1, 'keine Daten für '.$section, $color['black']);
        }
        imagepng($image, $cache_filename);
        imagedestroy($image);
        readfile($cache_filename);
    }

    function piechart($values, $labels) {
        global $cacherenew;

        $dim_x = 150;
        $dim_y = 150;
        $y_offset = 30;

        $cache_filename = 'cache/piechart_'.$_SESSION['user_id'].'_'.$_SESSION['log_id'].'_'.join('_', $values).'_'.join('_', $labels).'_'.$dim_x.'x'.$dim_y;
        if(file_exists($cache_filename) && !$cacherenew==1) {
            readfile($cache_filename);
            exit();
        }

        $image = imagecreatetruecolor ($dim_x, $dim_y + $y_offset);
        $color[0] =       imagecolorallocate($image, 0,  200,  0); // hit
        $color[1] =       imagecolorallocate($image, 0,  255,  0); // crit
        $color[2] =       imagecolorallocate($image, 255,  0,  0); // miss
        $color[3] =       imagecolorallocate($image, 200,  0,  0); // dodge
        $color[4] =       imagecolorallocate($image, 150,  0,  0); // parry
        $color[5] =       imagecolorallocate($image, 100,  0,  0); // deflect
        $color[6] =       imagecolorallocate($image, 255,204,  0); // immune
        $color[7] =       imagecolorallocate($image, 0,    0,255);
        $color[8] =       imagecolorallocate($image, 0,    0,150);
        $color['black'] = imagecolorallocate($image, 0,    0,  0);
        $color['white'] = imagecolorallocate($image, 255,255,255);

        // white bg
        imagefilledrectangle($image, 0, 0, $dim_x, $dim_y+$y_offset, $color['white']);

        $sum=array_sum($values);
        $start=0;
        $v=0;
        foreach($values as $v => $value) {
            $end = (360 / $sum) * $value + $start;
            if($start != $end) {
                imagefilledarc($image, $dim_x/2, $dim_y/2, $dim_x, $dim_y, $start, $end, $color[$v], IMG_ARC_PIE);
            }
            $start = $end;
        }
        if(count($labels) < 5) {
            foreach($labels as $l => $label) {
                //imagestring($image, 2, $l * ($dim_x/count($labels)), $dim_y+1, $label, $color[$l]);
            }
        } else {
            foreach($labels as $l => $label) {
                imagestring($image, 1, (ceil(($l+1)/2)-1) * ($dim_x/(count($labels)/2)), $dim_y+1 + (($l%2)*12), $label, $color[$l]);
            }
        }

        imagepng($image, $cache_filename);
        imagedestroy($image);
        readfile($cache_filename);
    }

    // GUILanguage translation
    function guil($term) {
        global $guil;
        
        if($guil[$_SESSION['language']][$term]) {
            return $guil[$_SESSION['language']][$term];
        } elseif($guil['de'][$term]) {
            return $guil['de'][$term];
        } else {
            return "TRANSLATION_MISSING";
        }
    }
    
    function seconds_to_readable($seconds) {
        $time = $seconds;
        $hours = floor($time / (60*60));
        $time = $time - $hours * (60*60);
        $minutes = floor($time / (60));
        $time = $time - $minutes * 60;
        $seconds = $time;

        return sprintf('%s:%02s:%02s',$hours,$minutes,$seconds);
    }

    function endofpage() {
        global $cache_stats_filename, $openOptions, $logfiles, $benchmark;

        include("footer.php");

        print flushandcache();

        $benchmark->snapshot('endofpage');
        $benchmark->summary();
        if($fh = fopen('benchmarks-size2mem', 'a')) {
            $filename = $logfiles[$_SESSION['log_id']]['filename'];
            $file_size = filesize($filename);
            $peak_mem = memory_get_peak_usage();
            if($file_size) {
                $quotient = $peak_mem/$file_size;
            }
            fwrite($fh, sprintf("% 3s % 8s % 8s % 5.2f %s\n", $_SESSION['log_id'], $peak_mem, $file_size, $quotient, $filename));
            fclose($fh);
        }
        exit();
    }
    
    function flushandcache($first=0) {
        global $cache_stats_filename;
        
        $mode = 'a';
        if($first) {
            $mode = 'w';
        }

        $html = ob_get_flush();
        if($cache_stats_filename) {
            if($cache_handle = fopen($cache_stats_filename, $mode)) {
                fwrite($cache_handle, $html);
                fclose($cache_handle);
            }
        }
        ob_start();
    }
?>