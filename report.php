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
    include("include/class.logindialog.php");
    include("include/class.optionsdialog.php");
    include("include/class.messagedialog.php");
    include("include/class.infomessagedialog.php");
    include("include/class.errormessagedialog.php");
    include("include/class.helpdialog.php");
    
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
            $res2 = sql_query("select charname, char_id from logfile_char where logfile_id='".$id."' and char_id>0");
            while(list($charname, $char_id) = sql_fetch_row($res2)) {
                $logfiles[$id]['chars'][$c]['name'] = $charname;
                $logfiles[$id]['chars'][$c]['id'] = $char_id;
                $c++;
            }
        }
        
        // load chars
        $res = sql_query("select c.id, c.name, c.faction_id, c.level, c.gender, g.name, r.".$_SESSION['language'].", s.name, cl.".$_SESSION['language'].", c.class_id, c.server_id, c.race_id, c.guild_id
            from `char` c
                join guild g on (c.guild_id = g.id)
                join race r on (c.race_id = r.id)
                join server s on (c.server_id = s.id)
                join class cl on (c.class_id = cl.class_id)
                where user_id='".$_SESSION['user_id']."'");
        while(list($char_id, $charname, $faction_id, $charlevel, $chargender, $guildname, $race, $server, $classname, $classid, $serverid, $raceid, $guildid) = sql_fetch_row($res)) {
            $userchars[$char_id]['name'] = $charname;
            $userchars[$char_id]['faction'] = $faction_id=='r'?guil('republic'):guil('empire');
            $userchars[$char_id]['level'] = $charlevel;
            $userchars[$char_id]['gender'] = $chargender=='m'?guil('male'):guil('female');
            $userchars[$char_id]['gender_id'] = $chargender;
            $userchars[$char_id]['guild'] = $guildname;
            $userchars[$char_id]['guild_id'] = $guildid;
            $userchars[$char_id]['race'] = $race;
            $userchars[$char_id]['race_id'] = $raceid;
            $userchars[$char_id]['server'] = $server;
            $userchars[$char_id]['server_id'] = $serverid;
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
            $res2 = sql_query("select charname, char_id from logfile_char where logfile_id='".$id."' and char_id>0");
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
                        } else {
                            $notes = '['.date("d.m. H:i", $parser->start_timestamp).'-'.date("H:i", $parser->end_timestamp).'] '.$playerlist.': '.$parser->fight_count.' Kämpfe, '.(count($parser->actors)-1).' Gegner';
                            sql_query("update logfile set notes='".$notes."' where id='".$logfile_id."'");

                            // insert new effects, abilities etc.
                            if($parser->language) {
                                if($parser->players) {
                                    foreach(array_keys($parser->players) as $charname) {
                                        if($charname) {
                                            sql_query("insert into logfile_char (logfile_id, charname) values ('".$logfile_id."', '".mysql_escape_string($charname)."')
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
                    }
                } else {
                    $errormessage = guil('nofilechosenorfiletolarge');
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
                                    '".$faction_id."', '".$_POST['charclass']."', '".$_POST['charlevel']."', '".$_POST['chargender']."', '".$_POST['charrace']."')
                                    on duplicate key update 
                                        name='".$_POST['charname']."',
                                        server_id='".$_POST['server']."',
                                        guild_id='".$guild_id."',
                                        faction_id='".$faction_id."',
                                        class_id='".$_POST['charclass']."',
                                        level='".$_POST['charlevel']."',
                                        gender='".$_POST['chargender']."',
                                        race_id='".$_POST['charrace']."'
                                    ")) {
                                $message = "Der Charakter ".$_POST['charname']." wurde angelegt.";
                                header("Location: ".$_SERVER['PHP_SELF'].'?op=noop&message='.$message);
                                exit();
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
        case "deletechar":
            if($_SESSION['user_id'] && !$demo) {
                $res = sql_query("select user_id from `char` where id='".$_GET['charid']."'");
                list($chars_user_id) = sql_fetch_row($res);
                if($chars_user_id == $_SESSION['user_id']) {
                    if(sql_query("delete from `char` where id='".$_GET['charid']."'")) {
                        header("Location: ".$_SERVER['PHP_SELF'].'?op=noop&message=Character wurde gelöscht');
                        exit();
                    } else {
                        $errormessage = "Character konnte nicht gelöscht werden.";
                    }
                    $errormessage = "Keine Berechtigung zum löschen des Characters.";
                }
                $errormesssage = "Bitte melde dich an um einen Charakter zu löschen.";
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
    }

    ob_start();
    include("header.php");
    
    $dialogs = array();

    if($errormessage) {
        $dialogs[] = new ErrorMessageDialog($errormessage);
    }
    if($message) {
        $message = str_replace('&amp;', '&', $message);
        $message = preg_replace('#(https?://\S+)#', '<a href="$1">$1</a>', $message);
        $dialogs[] = new InfoMessageDialog($message);
    }

    if($_SESSION['user_id']) {
        if($_SESSION['log_id']) {
            $dialogs[] = new CharassignDialog();
        }
        $dialogs[] = new OptionsDialog();
    } else {
        if(!$logfiles[$_SESSION['log_id']]['public']) {
            $dialogs[] = new LoginDialog($login_message);
        }
    }
    $dialogs[] = new HelpDialog();

    // only allow own and public flagged logs to be viewed
    if($logfiles && isset($_SESSION['log_id'])) {
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

    // let's go

    print "<div id='navbar' style='float:left'>
            <button onClick='document.location.href=\"/forum\"'>".guil('forum')."</button>";
    foreach($dialogs as $dialog) {
        if(!$dialog->nobutton) {
            print $dialog->htmlskeleton_button();
        }
    }
    print "</div>";

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
        <div id='dialog_upload'>".guil('dialog_upload')."</div>";

    foreach($dialogs as $dialog) {
        print $dialog->htmlskeleton();
    }

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
    
    // loginfo
    if($_SESSION['log_id']) {
        $html = "<div>";
        if($logfiles[$_SESSION['log_id']]['public']) {
            $html .= " <p><a style='color:red' href='?op=setopt&logfile=".$_SESSION['log_id']."'>".guil('publicurl')."</a></p>";
        }
        $html .= "<p>
                <b>".guil('logfile').":</b> ".$logfiles[$_SESSION['log_id']]['notes']."<br>
                <b>".guil('filename').":</b> ".preg_replace('#upload/\d+/#', '', $logfiles[$_SESSION['log_id']]['filename'])."<br>
                <b>".guil('uploaddate').":</b> ".date('Y-m-d H:i:s', $logfiles[$_SESSION['log_id']]['timestamp'])."<br>
                <b>".guil('assignedchars').":</b> ";
        if($logfiles[$_SESSION['log_id']]['chars']) {
            $logchars = array();
            foreach($logfiles[$_SESSION['log_id']]['chars'] as $logchar) {
                $logchars[] = $logchar['name'];
            }
            if(count($logchars)>0) {
                $html .= join(', ', $logchars);
            } else {
                $html .= "<i>".guil('none')."</i>";
            }
        } else {
            $html .= "<i>".guil('none')."</i>";
        }
        $html .= "</p>
            <p><big>".guil('options').":</big><br>
                <b>".guil('minfightduration').":</b> ".$_SESSION['min_fight_duration']."<br>
                <b>".guil('preferedlanguage').":</b> ".$languages[$_SESSION['language']]."
            </p>
        </div>";

        $guildmemberslog = array();
        if($userchars) {
            foreach($userchars as $userchar_id => $userchar) {
                $userguildids[] = $userchar['guild_id'];
            }

            $res = sql_query("SELECT unix_timestamp(l.timestamp), l.notes, l.id, c.name, c.guild_id, g.name
                FROM `char` c, logfile_char lc, logfile l, guild g
                    where c.guild_id in (".join(',',$userguildids).")
                        and c.id = lc.char_id
                        and l.id = lc.logfile_id
                        and g.id = c.guild_id
                        and public=1
                        and c.id not in (".join(',', array_keys($userchars)).")
                    order by timestamp desc");
            while(list($guildmemberslog_timestamp, $guildmemberslog_notes, $guildmemberslog_id, $guildmemberslog_charname, $guildmemberslog_guild_id, $guildmemberslog_guildname) = sql_fetch_row($res)) {
                $guildmemberslog[$guildmemberslog_id]['timestamp'] = $guildmemberslog_timestamp;
                $guildmemberslog[$guildmemberslog_id]['notes'] = $guildmemberslog_notes;
                $guildmemberslog[$guildmemberslog_id]['charname'] = $guildmemberslog_charname;
                $guildmemberslog[$guildmemberslog_id]['guild_id'] = $guildmemberslog_guild_id;
                $guildmemberslog[$guildmemberslog_id]['guildname'] = $guildmemberslog_guildname;
            }
        }
        
        if(count($guildmemberslog) > 0) {
            $html .= "
            <big>".guil('guildiespubliclogs').":</big>
            <table class='dataTableSimple'>
                <thead>
                    <tr>
                        <th>".guil('uploaddate')."</th>
                        <th>Character</th>
                    </tr>
                </thead>
                <tbody>";
        
            foreach($guildmemberslog as $gmlog_id => $gmlog) {
                $html .= "<tr>
                        <td>".date('Y-m-d H:i:s', $gmlog['timestamp'])."</td>
                        <td><a title='<big>".htmlentities($gmlog['guildname'])."</big><br>".htmlentities($gmlog['notes'])."' href='?op=setopt&logfile=".$gmlog_id."'>".$gmlog['charname']."</a></td>
                    </tr>";
            }
            $html .= "</tbody></table>";
        }
        
        $dialog = new Dialog('loginfo', 'Loginfo', $html, 0, 1);
        $dialog->nobutton = 1;
        $dialog->position = "['right', 'bottom']";
        $dialog->width = 400;
        $dialogs[] = $dialog;
        print $dialog->htmlskeleton();
        
        unset($html);
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
                    if(count($fight['target']) > 0) {
                        foreach($fight['target'] as $target_id => $target_count) {
                            if($target_count > $main_target_count) {
                                $main_target_id = $target_id;
                                $main_target_count = $target_count;
                            }
                        }
                        if($main_target_id) {
                            if(is_numeric($main_target_id)) {
                                $res = sql_query("select coalesce(".$_SESSION['language'].", de) from actor where id=".$main_target_id);
                                list($main_target_name) = sql_fetch_row($res);
                            } else {
                                $main_target_name = $main_target_id;
                            }
                        }
                    }

                    $fight_title  = guil('fight')." ".$fight_nr
                        .": ".date('H:i:s', $fight['start_timestamp'])
                        ." | ".sprintf('%s:%02s:%02s',$hours,$minutes,$seconds)
                        ." | ".htmlentities($main_target_name);
                    
                    $fight_tooltip= '<big>'.guil('fight').' '.$fight_nr.': '.$main_target_name.'</big>'
                        .'<br>'.guil('duration').': '.sprintf('%s:%02s:%02s',$hours,$minutes,$seconds)
                        .'<br>'.guil('at')." ".date('d.m.', $fight['start_timestamp'])." ".guil('from')." ".date('H:i:s', $fight['start_timestamp'])." ".guil('to')." ".date('H:i:s', $fight['end_timestamp'])
                        .'<br>';
                    if($fight['sum']['damage']>0) {
                        $fight_tooltip .= "<br>".$fight['sum']['damage']." ".guil('damagedone')." (".round($fight['sum']['damage'] / $single_fight_duration, 2)." DPS)";
                    }
                    if($fight['sum']['healed']>0) {
                        $fight_tooltip .= "<br>".$fight['sum']['healed']." ".guil('healdone')." (".round($fight['sum']['healed'] / $single_fight_duration, 2)." HPS)";
                    }
                    if($fight['sum']['threat']>0) {
                        $fight_tooltip .= "<br>".$fight['sum']['threat']." ".guil('threatdone')." (".round($fight['sum']['threat'] / $single_fight_duration, 2)." TPS)";
                    }
                    if($fight['sum']['heal_received']>0) {
                        $fight_tooltip .= "<br>".$fight['sum']['heal_received']." ".guil('healreceived')." (".round($fight['sum']['heal_received'] / $single_fight_duration, 2)." HPS)";
                    }

                    $fight_tab_links .= "<h3><a title='".htmlentities($fight_tooltip, ENT_QUOTES)."' href='ajax_accordion.php?char=".$char."&log_id=".$_SESSION['log_id']."&fight=".$fight['start_id']."'>".$fight_title."</a></h3><div></div>";
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
