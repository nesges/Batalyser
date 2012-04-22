<?
    session_start();

    include("include/init.php");
    include("include/class.tab.php");
    include("include/class.tab_dpshpstps_per_target.php");
    include("include/class.tab_char_dpstps_per_ability.php");
    include("include/class.tab_char_hpstps_per_ability.php");
    include("include/class.tab_enemies_damage_to_char.php");
    include("include/class.tab_full_fight_stats.php");
    include("include/class.tab_full_fight_graphs.php");
    include("include/class.dialog.php");
    include("include/class.charmanagerdialog.php");
    include("include/class.charassigndialog.php");
    
    // session starters
    switch($op) {
        case "setopt":
            if(isset($_GET['logfile'])) {
                $_SESSION['log_id'] = $_GET['logfile'];
            }
            if(isset($_GET['min_fight_duration'])) {
                $_SESSION['min_fight_duration'] = $_GET['min_fight_duration'];
                setcookie('min_fight_duration', $_GET['min_fight_duration'], time()+60*60*24*30);
            }
            if(isset($_GET['prefered_language'])) {
                $_SESSION['language'] = $_GET['prefered_language'];
                setcookie('language', $_GET['prefered_language'], time()+60*60*24*30);
            }

            if(! isset($_SESSION['min_fight_duration'])) {
                $_SESSION['min_fight_duration'] = 21;
            }
            if(! isset($_SESSION['language'])) {
                $_SESSION['language'] = 'de';
            }
            break;
        case "noop":
            unset($_SESSION['log_id']);
            break;
        case "demo":
            $_SESSION['user_id'] = 1;
            $_SESSION['user_name'] = "Demo";
            $_SESSION['user_email'] = "";
            break;
        case "login":
            $_SESSION['language'] = $_POST['language'];
            setcookie('language', $_POST['language'], time()+60*60*24*30);
            
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
                $login_message = guil('login_wrongcredentials');
            }
            unset($t_hasher);
            break;
        case "session_reset":
            $_SESSION = array();
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        case "setlanguage":
            $_SESSION['language'] = $_GET['language'];
            setcookie('language', $_GET['language'], time()+60*60*24*30);
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
    }
    
    // load userdata from db
    if($_SESSION['user_id']) {
        // load logfiles
        $res = sql_query("select l.id, UNIX_TIMESTAMP(l.timestamp), l.notes, l.filename, l.uploader_id, l.public
            from logfile l
            where l.uploader_id = '".$_SESSION['user_id']."'
            order by l.timestamp desc");
        while(list($id, $timestamp, $notes, $filename, $uploader_id, $public) = sql_fetch_row($res)) {
            $logfiles[$id]['timestamp'] = $timestamp;
            $logfiles[$id]['notes'] = $notes;
            $logfiles[$id]['filename'] = $filename;
            $logfiles[$id]['uploader_id'] = $uploader_id;
            $logfiles[$id]['public'] = $public;
            
            $c=0;
            $res2 = sql_query("select charname, char_id from logfile_char where logfile_id='".$id."'");
            while(list($charname, $char_id) = sql_fetch_row($res2)) {
                $logfiles[$id]['chars'][$c]['name'] = $charname;
                $logfiles[$id]['chars'][$c]['id'] = $char_id;
                $c++;
            }
        }
        
        // load chars
        $res = sql_query("select c.id, c.name, c.faction_id, c.level, c.gender, g.name, r.".$_SESSION['language'].", s.name, cl.".$_SESSION['language'].", c.class_id
            from `char` c
                join guild g on (c.guild_id = g.id)
                join race r on (c.race_id = r.id)
                join server s on (c.server_id = s.id)
                join class cl on (c.class_id = cl.class_id)
                where user_id='".$_SESSION['user_id']."'");
        while(list($char_id, $charname, $faction_id, $charlevel, $chargender, $guildname, $race, $server, $classname, $classid) = sql_fetch_row($res)) {
            $userchars[$char_id]['name'] = $charname;
            $userchars[$char_id]['faction'] = $faction_id=='r'?guil('republic'):guil('empire');
            $userchars[$char_id]['level'] = $charlevel;
            $userchars[$char_id]['gender'] = $chargender=='m'?guil('male'):guil('female');
            $userchars[$char_id]['guild'] = $guildname;
            $userchars[$char_id]['race'] = $race;
            $userchars[$char_id]['server'] = $server;
            $userchars[$char_id]['class'] = $classname;
            $userchars[$char_id]['class_id'] = $classid;
        }
    }

    // load logfile data (for public logfile)
    if($_SESSION['log_id']) {
        $res = sql_query("select l.id, UNIX_TIMESTAMP(l.timestamp), l.notes, l.filename, l.uploader_id, l.public
            from logfile l
            where l.id = '".$_SESSION['log_id']."'");
        while(list($id, $timestamp, $notes, $filename, $uploader_id, $public) = sql_fetch_row($res)) {
            $logfiles[$id]['timestamp'] = $timestamp;
            $logfiles[$id]['notes'] = $notes;
            $logfiles[$id]['filename'] = $filename;
            $logfiles[$id]['uploader_id'] = $uploader_id;
            $logfiles[$id]['public'] = $public;
            
            $c=0;
            $res2 = sql_query("select charname, char_id from logfile_char where logfile_id='".$id."'");
            while(list($charname, $char_id) = sql_fetch_row($res2)) {
                $logfiles[$id]['chars'][$c]['name'] = $charname;
                $logfiles[$id]['chars'][$c]['id'] = $char_id;
                $c++;
            }
        }
    }


    switch($op) {
        case "logdelete":
            if(!$demo) {
                $log_id_list = mysql_escape_string(join(',', $_POST['delete_logfile']));
                // check for user_id
                $res = sql_query("select id, filename from logfile where id in (".$log_id_list.")
                    and uploader_id = ".$_SESSION['user_id']);
                while(list($id, $filename) = sql_fetch_row($res)) {
                    $delete_ids[] = $id;
                    @unlink('cache/serialized_parser_'.$id.'_'.$version);
                    foreach(glob("cache/accordion_*".$id."_*") as $cache_file) {
                        @unlink($cache_file);
                    }
                    foreach(glob("cache/pixelmap_*".$id."_*") as $cache_file) {
                        @unlink($cache_file);
                    }
                    @unlink($filename);
                }
                $log_id_list = join(',', $delete_ids);
                sql_query("delete from logfile
                    where id in (".$log_id_list.")");
                sql_query("delete from data
                    where logfile_id in (".$log_id_list.")");
                $_SESSION['log_id']=0;
            }
            header("Location: ".$_SERVER['PHP_SELF']."?logfile=&options=1");
            exit();
        case "logdownload":
            $res = sql_query("select filename from logfile where id=".mysql_escape_string($_GET['logfile']));
            list($filename) = sql_fetch_row($res);
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Type: application/force-download");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=".$filename);
            header("Content-Transfer-Encoding: binary");
            readfile($filename);
            exit();
        case "logpublicize":
            sql_query("update logfile set public=1 where id = '".mysql_escape_string($_GET['logfile'])."' and uploader_id='".$_SESSION['user_id']."'");
            header("Location: ".$_SERVER['PHP_SELF']."?op=noop&message=".guil('logpublicizedunder')." ".urlencode('http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?op=setopt&logfile=".$_GET['logfile']));
            exit();
        case "logdepublicize":
            sql_query("update logfile set public=0 where id = '".mysql_escape_string($_GET['logfile'])."' and uploader_id='".$_SESSION['user_id']."'");
            header("Location: ".$_SERVER['PHP_SELF']."?op=noop&message=".guil('logdepublicized'));
            exit();
        case "logupload":
            if($_SESSION['user_id'] && !$demo) {
                if($_FILES['logfile']['tmp_name']) {
                    @mkdir('upload/'.$_SESSION['user_id']);
                    $filename = 'upload/'.$_SESSION['user_id'].'/'.basename($_FILES['logfile']['name']);
                    if(move_uploaded_file($_FILES['logfile']['tmp_name'], $filename)) {
                        sql_query("insert into logfile (uploader_id, filename, timestamp, public, mergeable)
                            values (".$_SESSION[user_id].", '".$filename."', now(), ".($_POST['publicize']?1:0).", ".($_POST['mergeable']?1:0).")");
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

                            $errormessage = guil('logfilecontainsnodata');
                            $errormessage_returnto = "document.location.href=\"?op=noop\"";
                        } else {
                            $notes = '['.date("d.m. H:i", $parser->start_timestamp).'-'.date("H:i", $parser->end_timestamp).'] '.$playerlist.': '.$parser->fight_count.' Kämpfe, '.(count($parser->actors)-1).' Gegner';
                            sql_query("update logfile set notes='".$notes."' where id='".$logfile_id."'");

                            // insert new effects, abilities etc.
                            if($parser->language) {
                                if($parser->players) {
                                    foreach(array_keys($parser->players) as $charname) {
                                        if($charname) {
                                            sql_query("insert into logfile_char (logfile_id, charname) values ('".$logfile_id."', '".$charname."')
                                                on duplicate key update char_id=0");
                                        }
                                    }
                                }
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
                            header("Location: ".$_SERVER['PHP_SELF']."?opendialog=charassign");
                            exit();
                        }
                    } else {
                        $errormessage = guil('filenotuploaded');
                        $errormessage_returnto = "document.location.href=\"?op=noop\"";
                    }
                } else {
                    $errormessage = guil('nofilechosenorfiletolarge');
                    $errormessage_returnto = "document.location.href=\"?op=noop\"";
                }
            }
            break;
        case "createchar":
            if($_SESSION['user_id'] && !$demo) {
                $res = sql_query("select id from server where id='".$_POST['server']."'");
                list($server_id_found) = sql_fetch_row($res);
                if($server_id_found) {
                    $res = sql_query("select id from guild where name like '".$_POST['guild']."' and server_id='".$_POST['server']."'");
                    list($guild_id) = sql_fetch_row($res);
                    if(!$guild_id) {
                        sql_query("insert into guild (name, server_id) values ('".$_POST['guild']."', '".$_POST['server']."')");
                        $res = sql_query("select id from guild where name like '".$_POST['guild']."' and server_id='".$_POST['server']."'");
                        list($guild_id) = sql_fetch_row($res);
                    }
                    if($guild_id) {
                        $res = sql_query("select faction_id, ".$_SESSION['language']." from class where class_id='".$_POST['charclass']."'");
                        list($faction_id, $classname) = sql_fetch_row($res);
                        if($faction_id) {
                            if(sql_query("insert into `char` (name, user_id, server_id, guild_id, faction_id, class_id, level, gender, race_id)
                                values ('".$_POST['charname']."', '".$_SESSION['user_id']."', '".$_POST['server']."', '".$guild_id."', 
                                    '".$faction_id."', '".$_POST['charclass']."', '".$_POST['charlevel']."', '".$_POST['chargender']."', '".$_POST['charrace']."')")) {
                                $message = "Der Charakter ".$_POST['charname']." wurde angelegt.";
                            } else {
                                $errormessage = "Der Charakter ".$_POST['charname']." konnte nicht angelegt werden.";
                            }
                        } else {
                            $errormessage = "Die Klasse konnte nicht gefunden werden.";
                        }
                    } else {
                        $errormessage = "Gilde konnte nicht angelegt werden.";
                    }
                } else {
                    $errormessage = "Server nicht gefunden";
                }
            } else {
                $errormesssage = "Bitte melde dich an um einen Charakter anzulegen.";
            }
            break;
        case "bugreport":
            if($_POST['bugreport']) {

                $subject = $_POST['subject'].' ('.preg_replace('#/?batalyser/?#', '', dirname($_SERVER['REQUEST_URI'])).' v'.$version.')';

                sql_query("insert into bugreport (subject, message, user_id, logfile_id)
                    values ('".$subject."', '".mysql_escape_string($_POST['bugreport'])."', '".$_SESSION['user_id']."', '".$_SESSION['log_id']."')");
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
                header('Location: '.$phpbb_bridge_url.'?op=bugreport&id='.$bugreport_id.'&returnto='.base64_encode($_SERVER['PHP_SELF'].'?op=setopt&message='.urlencode(guil('thanksforyourbugreport').' [post_url]')));
            } else {
                header("Location: ".$_SERVER['PHP_SELF']."?message=".guil('bugreportwithoutmessage'));
            }
            exit();
            break;
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
            header("Location: piechart.php?".$_SERVER['QUERY_STRING']);
            exit();
        case "charassign":
            sql_query("delete from logfile_char where logfile_id=".$_SESSION['log_id']);
            foreach($_POST['selectchar'] as $logchar => $userchar_id) {
                if($userchar_id>=0) {
                    sql_query("insert into logfile_char values (".$_SESSION['log_id'].", '".$logchar."', '".$userchar_id."')");
                }
            }
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
    }

    ob_start();
    include("header.php");
    
    $dialogs = array();
    if($_SESSION['log_id'] && $_SESSION['user_id']) {
        $dialogs[] = new CharassignDialog();
    }

    if($errormessage) {
        if(!$errormessage_returnto) {
            $errormessage_returnto = 'document.location.href=\'?op=noop\'';
        }
        print "<div id='dialog_error'><p>$errormessage</p><center><button onClick='".$errormessage_returnto."'>".guil('back')."</button></center></div>";
        include("footer.php");
    }

    if($message) {
        $message = str_replace('&amp;', '&', $message);
        $message = preg_replace('#(https?://\S+)#', '<a href="$1">$1</a>', $message);
        print "<div id='dialog_message'><p>".$message."</p><center><button onClick='document.location.href=\"".$_SERVER['PHP_SELF']."?op=noop\"'>".guil('back')."</button></center></div>";
        include("footer.php");
    }

    // only allow own and public flagged logs to be viewed
    if($logfiles) {
        foreach($logfiles as $logfile_id => $logfile) {
            if($logfile_id == $_SESSION['log_id']) {
                if($logfile['uploader_id'] != $_SESSION['user_id'] && !$logfile['public']) {
                    header("Location: ?op=setopt&logfile=&message=".guil('notthelogyourelookingfor'));
                    exit();
                } else {
                    break;
                }
            }
        }
    }
        
    if(! $_SESSION['user_id'] && !$logfiles[$_SESSION['log_id']]['public']) {
        print "<div id='dialog_login'>
                <p>".guil('dialog_login_useforumacc')."</p>
                <form action='' method='POST'>
                    <table>
                        <tr><td>".guil('username').":</td>        <td><input type='text' name='username'></td></tr>
                        <tr><td>".guil('password').":</td>        <td><input type='password' name='password'></td></tr>
                        <tr><td>".guil('preferedlanguage').":</td><td>
                            <select name='language' onChange='document.location.href=\"?op=setlanguage&language=\"+this.value'>";
        foreach(array('de' => 'Deutsch', 'en' => 'English') as $short => $long) {
            if($_SESSION['language'] == $short) {
                $selected = "selected='selected'";
            } else {
                $selected = "";
            }
            print "<option value='".$short."' ".$selected.">".$long."</option>";
        }
        print "</select>
                        </td></tr>
                        <tr><td colspan='2' align='center'><input type='submit' value='".guil('login')."'></td></tr>
                    </table>";
        if($login_message) {
            print "<p style='color:red; text-align:center'>".$login_message."</p>";
        }
        print "<input type='hidden' name='op' value='login'>

                </form>
                <p><a href='/forum/ucp.php?mode=register'>".guil('register')."</a> <a href='?op=demo'>".guil('startdemo')."</a></p>
            </div>";
            include("footer.php");
    }

    // let's go

    print "<div id='navbar' style='float:left'>
            <button onClick='document.location.href=\"/forum\"'>".guil('forum')."</button>";
    foreach($dialogs as $dialog) {
        print $dialog->htmlskeleton();
        print $dialog->buttonskeleton();
    }
    print "</div>";

    if($_SESSION['log_id']) {
        print "<div style='float:left; margin-left:25px'>
            <b>".guil('logfile').":</b>                 ".$logfiles[$_SESSION['log_id']]['notes']." |
            <b>".guil('minfightduration_short').":</b>  ".$_SESSION['min_fight_duration']." |
            <b>".guil('preferedlanguage_short').":</b>  ".$languages[$_SESSION['language']];
        if($logfiles[$_SESSION['log_id']]['public']) {
            print " | <a style='color:red' href='?op=setopt&logfile=".$_SESSION['log_id']."'>".guil('publicurl')."</a>";
        }
        print "</div>";
    }
    print "<div style='text-align:right'>";
    if($_SESSION['user_id']) {
        print guil('loggedinas').": ".$_SESSION['user_name']."
            <button onClick='document.location.href=\"?op=session_reset\"'>".guil('logoff')."</button>";
    } else {
        print "
            <button onClick='document.location.href=\"?op=session_reset\"'>".guil('login')."</button>
            <button onClick='document.location.href=\"/forum/ucp.php?mode=register\"'>".guil('register')."</button>";
    }
    print "<button id='button_open_dialog_options'>".guil('options')."</button>
            <button id='button_open_dialog_help'>?</button>
        </div>
        
        <div id='dialog_misc' style='padding:0'><iframe style='margin:0' src='' frameborder='0' width='800' height='600' scrolling='no'></iframe></div>

        <div id='dialog_options'>
            <div id='accordion_options'>
                <h3><a href='#'>".guil('view')."</a></h3>
                <div>
                    <form action='' method='GET'>
                        <table width='100%'>
                            <tr>
                                <td nowrap='nowrap'>".guil('view_chooselogfile').":</td>
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
                                <td nowrap='nowrap'>".guil('minfightduration').":</td>
                                <td width='70%'><div id='min_fight_duration_slider'></div></td>
                                <td><input type='text' id='min_fight_duration_slider_value' name='min_fight_duration' value='' style='width:3em' readonly='readonly'></td>
                            </tr>
                            <tr>
                                <td>".guil('preferedlanguage').":</td>
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
                                <td colspan='3' align='right'><input type='submit' value='".guil('viewlogwiththissettings')."'></td>
                            </tr>
                        </table>
                        <input type='hidden' name='op' value='setopt'>
                    </form>
                </div>";

    if($_SESSION['user_id']) {
        print "<h3><a href='#'>".guil('upload')."</a></h3>
                <div>
                    <form action='' method='POST' enctype='multipart/form-data' name='uploadform'>
                        <input type='hidden' name='MAX_FILE_SIZE' value='".MAX_FILE_SIZE."'>

                        <table>
                            <tr>
                                <td colspan='2'>".guil('zipfilenotice')."</td>
                            </tr>
                            <tr>
                                <td colspan='2'>
                                    ".guil('logfile').": (max. ".sprintf("%s", 1024 * (MAX_FILE_SIZE / pow(1024, floor((strlen(MAX_FILE_SIZE) - 1) / 3))))."kB): <input type='file' name='logfile'>
                                </td>
                            </tr>
                            <tr>
                                <td>".guil('logpublicize')."</td>
                                <td><input type='checkbox' name='publicize' checked='checked'></td>
                            </tr>
                            <!--tr>
                                <td>".guil('logmergeable')."</td>
                                <td><input type='checkbox' name='mergeable' checked='checked'></td>
                            </tr-->
                        </table>
                        <p style='text-align:right'><input type='submit' value='".guil('startupload')."' id='button_start_upload' onClick='document.uploadform.submit()' $disable_ui_element>";
                        if($demo) {
                            print "<div style='text-align:right'><small>".guil('upload_demonotice')."</small></div>";
                        }
                        print "
                        <input type='hidden' name='op' value='logupload'>
                    </form>
                </div>";
        }

            if($logfiles && $_SESSION['user_id']) {
                print "<h3><a href='#'>".guil('availablelogs')."</a></h3>
                <div>
                    <p>".guil('yourlogs').":</p>
                    <form action='' method='POST'>
                        <table class='dataTableAutoWidth' id='datatable_optionsLogfiles'>
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>".guil('date')."</th>
                                    <th>".guil('from')."</th>
                                    <th></th>
                                    <th>".guil('to')."</th>
                                    <th>".guil('chars')."</th>
                                    <th>".guil('fights')."</th>
                                    <th>".guil('enemies')."</th>
                                    <th>".guil('filename')."</th>
                                    <th>".guil('upload')."</th>
                                    <th>".guil('actions')."</th>
                                </tr>
                            </thead>
                            <tbody>";
                foreach($logfiles as $logfile_id => $logfile) {
                    // exclude public logs
                    if($logfile['uploader_id']==$_SESSION['user_id']) {
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
                                    <td>
                                        <table cellspacing='0' cellpadding='0'>
                                            <tr>
                                                <td><span class='ui-icon ui-icon-play' onClick='document.location.href=\"?op=setopt&logfile=".$logfile_id."\"' title='".guil('view')."'></span></td>";
                        if(!$logfile['public']) {
                            print "<td><span class='ui-icon ui-icon-unlocked' onClick='document.location.href=\"?op=logpublicize&logfile=".$logfile_id."\"' title='".guil('publicize')."'></span></td>";

                        } else {
                            print "<td><span class='ui-icon ui-icon-locked' onClick='document.location.href=\"?op=logdepublicize&logfile=".$logfile_id."\"' title='".guil('depublicize')."'></span></td>";
                        }
                        print "<td><span class='ui-icon ui-icon-disk' onClick='document.location.href=\"?op=logdownload&logfile=".$logfile_id."\"' title='".guil('download')."'></span></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>";
                    }
                }
                print "</tbody>
                        </table>
                        <input type='hidden' name='op' value='logdelete'>
                        <p style='text-align:left'><input type='submit' value='".guil('deletechosenlogs')."' $disable_ui_element></p>";
                        if($demo) {
                            print "<div style='text-align:left'><small>".guil('delete_demonotice')."</small></div>";
                        }
                print "</form>
                </div>";
            }
            if($_SESSION['user_id']) {               
                print "<h3><a href='#'>".guil('yourchars')."</a></h3>
                    <div>";
                if(count($userchars)>0) {
                    print "<table class='dataTableSimple'>
                                <thead>
                                    <tr>
                                        <th>".guil('name')."</th>
                                        <th>".guil('class')."</th>
                                        <th>".guil('level')."</th>
                                        <th>".guil('race')."</th>
                                        <th>".guil('gender')."</th>
                                        <th>".guil('faction')."</th>
                                        <th>".guil('guild')."</th>
                                        <th>".guil('server')."</th>
                                    </tr>
                                </thead>
                                <tbody>";
                    foreach($userchars as $char_id => $char) {
                        print "<tr>
                                <td>".$char['name']."</td>
                                <td>".$char['class']."</td>
                                <td>".$char['level']."</td>
                                <td>".$char['race']."</td>
                                <td>".$char['gender']."</td>
                                <td>".$char['faction']."</td>
                                <td>".$char['guild']."</td>
                                <td>".$char['server']."</td>
                            </tr>";
                    }
                    print "</tbody></table>";
                }
                print "<p>".guil('createnewchar').":</p>
                        <form action='' method='POST'>
                            <table>
                                <tr><td>".guil('name').":</td><td><input type='text' name='charname' size='50'></td></tr>
                                <tr><td>".guil('class').":</td><td>
                                    <select name='charclass'>";
                $res = sql_query("select class_id, parent_class_id, ".$_SESSION['language']." from class order by ".$_SESSION['language']);
                while(list($class_id, $parent_class_id, $class_name) = sql_fetch_row($res)) {
                    print "<option value='".$class_id."'>".$class_name."</option>";
                }
                print "</select>
                                </td></tr>
                                <tr><td>".guil('guild').":</td><td><input type='text' name='guild' size='50'></td></tr>
                                <tr><td>".guil('server').":</td><td>
                                    <select name='server'>";
                $res = sql_query("select id, name from server order by name");
                while(list($server_id, $server_name) = sql_fetch_row($res)) {
                    print "<option value='".$server_id."'>".$server_name."</option>";
                }
                print "</select>
                                </td></tr>
                                <tr><td>".guil('level').":</td><td><input type='text' name='charlevel' value='50' size='3'></td></tr>
                                <tr><td>".guil('race').":</td><td><select name='charrace'>";
                $res = sql_query("select id, ".$_SESSION['language']." from race order by ".$_SESSION['language']);
                while(list($race_id, $race_name) = sql_fetch_row($res)) {
                    print "<option value='".$race_id."'>".$race_name."</option>";
                }
                print "</select></td></tr>
                            <tr><td>".guil('gender').":</td><td><select name='chargender'><option value='m'>".guil('male')."</option><option value='f'>".guil('female')."</option></select></td></tr>
                            </table>
                            <input type='hidden' name='op' value='createchar'>
                            <p style='text-align:left'><input type='submit' value='".guil('createnewchar')."' $disable_ui_element></p>
                        </form>
                    </div>";
            }
            
            print "<h3><a href='#'>".guil('sendbugreport')."</a></h3>
                <div>
                    ".guil('bugreport_explanation')."
                    <form action='' method='POST'>
                        <table>
                            <tr><td valign='top'>".guil('bugreport_shorttitle').":</td><td><input type='text' name='subject' maxlength='40' size='40'></td></tr>
                            <tr><td valign='top'>".guil('bugreport_description').":</td><td><textarea name='bugreport' cols='70' rows='6'></textarea></td></tr>
                            <tr><td>".guil('bugreport_yourmail').":</td><td><input type='text' name='email' size='70' value='".($_SESSION['user_email'])."'></tr>
                            <tr><td colspan='2' align='right'>".guil('thankyou')." <input type='submit'></td></tr>
                        </table>
                        <input type='hidden' name='op' value='bugreport'>
                    </form>
                </div>
            </div><!-- accordion options -->
        </div><!-- dialog options -->

        <div id='dialog_upload'>".guil('dialog_upload')."</div>

        <div id='dialog_help'>
            <img src='http://geekandpoke.typepad.com/.a/6a00d8341d3df553ef014e8b8f1c5b970d-800wi' alt=''>
        </div>";

    if(! $_SESSION['min_fight_duration'] || !$_SESSION['log_id']) {
        include("footer.php");
    }

    // at this point we have a logged in user, a chosen logfile and a minimum fight duration
    // we are able to gather logdata now

    if(!isset($parser)) {
        $parser = new Parser($logfiles[$_SESSION['log_id']]['filename'], $_SESSION['log_id']);
    }

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

        $fight_tab_links = '';

        foreach($parser->players[$char]['fights'] as $fight) {
            $fight_start_id = $fight['start_id'];
            $fight_end_id = $fight['end_id'];

            if(!isset($min_timestamp)) {
                $min_timestamp = $fight['start_timestamp'];
            }
            $max_timestamp = $fight['end_timestamp'];

            $fight_nr++;
            if($fight['end_timestamp']>0 && $fight['start_timestamp']>0) {
                $single_fight_duration = $fight['end_timestamp'] - $fight['start_timestamp'];

                // collect data for summary tab
                $summary_duration += $single_fight_duration;
                $summary_damage += $fight['sum']['damage'];
                $summary_threat += $fight['sum']['threat'];
                $summary_healed += $fight['sum']['healed'];

                if($single_fight_duration >= $_SESSION['min_fight_duration'] 
                        && $fight['sum']['damage'] + $fight['sum']['threat'] + $fight['sum']['healed'] > 0) {
                    
                    $fights_displayed++;

                    // Dauer lesbar machen
                    $__time = $single_fight_duration;
                    $hours = floor($__time / (60*60));
                    $__time = $__time - $hours * (60*60);
                    $minutes = floor($__time / (60));
                    $__time = $__time - $minutes * 60;
                    $seconds = $__time;

                    $main_target_id=0;
                    $main_target_count=0;
                    foreach($fight['target'] as $target_id => $target_count) {
                        if($target_count > $main_target_count) {
                            $main_target_id = $target_id;
                            $main_target_count = $target_count;
                        }
                    }
                    $res = sql_query("select coalesce(".$_SESSION['language'].", de) from actor where id=".$main_target_id);
                    list($main_target_name) = sql_fetch_row($res);

                    $fight_title  = guil('fight')." ".$fight_nr;
                    $fight_title .= ": ".$main_target_name." | ".guil('duration')." ".sprintf('%s:%02s:%02s',$hours,$minutes,$seconds).", ";
                    $fight_title .= guil('at')." ".date('d.m.', $fight['start_timestamp'])." ".guil('from')." ".date('H:i:s', $fight['start_timestamp'])." ".guil('to')." ".date('H:i:s', $fight['end_timestamp']);
                    $fight_title .= " [".round($fight['sum']['damage'] / $single_fight_duration, 2)." DPS ";
                    $fight_title .= "| ".round($fight['sum']['threat'] / $single_fight_duration, 2)." TPS ";
                    $fight_title .= "| ".round($fight['sum']['healed'] / $single_fight_duration, 2)." HPS ] ";
                    if($fight['sum']['damage']>0) {
                        $fight_title .= $fight['sum']['damage']." ".guil('damagedone').", ";
                    }
                    if($fight['sum']['healed']>0) {
                        $fight_title .= $fight['sum']['healed']." ".guil('healdone').", ";
                    }
                    $fight_title .= $fight['sum']['threat']." ".guil('threatdone').".";

                    $fight_tab_links .= "<h3><a href='ajax_accordion.php?char=".$char."&log_id=".$_SESSION['log_id']."&fight=".$fight['start_id']."'>".$fight_title."</a></h3><div></div>";
                } else {
                    $fights_hidden++;
                }
            }
        }
        
        print "<h2><a name='stats_for_".$_char."'>".guil('damagestatsfor')." ".$char." (".$fights_displayed." ".($fights_displayed==1?guil('fight'):guil('fights'))." ".guil('shown').", ".$fights_hidden." ".guil('hidden').")</a>";
        print "</h2>
                    <div>
                        <div class='accordion_ajax'>";
        print $fight_tab_links;

        // Gesamt
        if($fights_displayed<1 && $fight_nr>0) {
            print guil('tip_toolowminfightduration'); "Tipp: Wenn keine einzelnen Kämpfe angezeigt werden, dann prüfe ob du in den Optionen eine zu hohe Mindest-Kampfdauer gewählt hast.";
        }

        $summary_title  = guil('logsummary').": ";
        $summary_title .= guil('duration')." ".seconds_to_readable($summary_duration)." (".guil('active')."), ";
        if($summary_duration) {
            $summary_title .= guil('at')." ".date('d.m.', $min_timestamp)." ".guil('between')." ".date('H:i:s', $min_timestamp)." ".guil('and')." ".date('H:i:s', $max_timestamp);
            $summary_title .= " [".round($summary_damage / $summary_duration, 2)." DPS ";
            $summary_title .= "| ".round($summary_threat / $summary_duration, 2)." TPS ";
            $summary_title .= "| ".round($summary_healed / $summary_duration, 2)." HPS ] ";
            if($summary_damage>0) {
                $summary_title .= $summary_damage." ".guil('damagedone').", ";
            }
            if($summary_healed>0) {
                $summary_title .= $summary_healed." ".guil('healdone').", ";
            }
            $summary_title .= $summary_threat." ".guil('threatdone').".";
        }
        print "<h3><a href='ajax_accordion.php?char=".$char."&log_id=".$_SESSION['log_id']."'>".$summary_title."</a></h3><div><img src='../../images/loading.gif' alt='Loading...'> Loading...</div>";

        print "</div>"; // empty div that fixes accordion width bug
        print "</div>"; // accordion
    } // foreach $char
    print "</div>"; // accordion



    sql_logout();
    unset($guil);
    unset($output_accordion_page);
    unset($parser);

    include("footer.php");

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

        return 'cache/pixelmap_'.$_SESSION['log_id'].'_'.$start_id.'-'.$end_id.'_'.$char.'_'.$conditions_list.'_'.$section.'_'.$snd_sections_list.$dim_x.'x'.$dim_y.'_'.$eventtext;
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

    function seconds_to_readable($seconds) {
        $time = $seconds;
        $hours = floor($time / (60*60));
        $time = $time - $hours * (60*60);
        $minutes = floor($time / (60));
        $time = $time - $minutes * 60;
        $seconds = $time;

        return sprintf('%s:%02s:%02s',$hours,$minutes,$seconds);
    }
?>
