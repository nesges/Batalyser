<?
    class OptionsDialog extends Dialog {
        function OptionsDialog($message='') {
            global $demo, $logfiles, $languages, $openOptions, $userchars;
            
            parent::Dialog('options', guil('dialog_options_title'), '', 1, 1);
            
            $disable_ui_element="";
            if($demo) {
                $disable_ui_element = "disabled='disabled'";
            }

            $html = "<div id='accordion_options'>
                        <h3><a href='#'>".guil('view')."</a></h3>
                        <div>
                            <form action='' method='GET'>
                                <table width='100%'>
                                    <tr>
                                        <td nowrap='nowrap'>".guil('view_chooselogfile').":</td>
                                        <td colspan='2'><select name='logfile'>";
                                        if($logfiles) {
                                            foreach($logfiles as $logfile_id => $logfile) {
                                                $selected = "";
                                                if(isset($_SESSION['log_id']) && $logfile_id == $_SESSION['log_id']) {
                                                    $selected = " selected='selected' ";
                                                }
                                                $html .= "<option $selected value='".$logfile_id."'>".$logfile['notes']."</option>";
                                            }
                                        }
                                        $html .= "</select></td>
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
                                                    $html .= "<option $selected value='$short'>$long</option>";
                                                }
                                            $html .= "</select>
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
                $html .= "<h3><a href='#'>".guil('upload')."</a></h3>
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
                                    $html .= "<div style='text-align:right'><small>".guil('upload_demonotice')."</small></div>";
                                }
                                $html .= "
                                <input type='hidden' name='op' value='logupload'>
                            </form>
                        </div>";
                }
        
                if($logfiles && $_SESSION['user_id']) {
                    $html .= "<h3><a href='#'>".guil('availablelogs')."</a></h3>
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
                            $html .= "<tr>
                                        <td><input type='checkbox' name='delete_logfile[]' value='".$logfile_id."'></td>";
                            preg_match('/\[(\d\d\.\d\d\.) (\d\d:\d\d)-(\d\d:\d\d)\] (.*?): (\d+) Kämpfe, (\d+) Gegner/', $logfile['notes'], $matches);
                            $html .= "
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
                                $html .= "<td><span class='ui-icon ui-icon-unlocked' onClick='document.location.href=\"?op=logpublicize&logfile=".$logfile_id."\"' title='".guil('publicize')."'></span></td>";
        
                            } else {
                                $html .= "<td><span class='ui-icon ui-icon-locked' onClick='document.location.href=\"?op=logdepublicize&logfile=".$logfile_id."\"' title='".guil('depublicize')."'></span></td>";
                            }
                            $html .= "<td><span class='ui-icon ui-icon-disk' onClick='document.location.href=\"?op=logdownload&logfile=".$logfile_id."\"' title='".guil('download')."'></span></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>";
                        }
                    }
                    $html .= "</tbody>
                            </table>
                            <input type='hidden' name='op' value='logdelete'>
                            <p style='text-align:left'><input type='submit' value='".guil('deletechosenlogs')."' $disable_ui_element></p>";
                            if($demo) {
                                $html .= "<div style='text-align:left'><small>".guil('delete_demonotice')."</small></div>";
                            }
                    $html .= "</form>
                    </div>";
                }
                if($_SESSION['user_id']) {               
                    $html .= "<h3><a href='#'>".guil('yourchars')."</a></h3>
                        <div>";
                    if(count($userchars)>0) {
                        $html .= "<table class='dataTableSimple'>
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
                                            <th>".guil('actions')."</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                        foreach($userchars as $char_id => $char) {
                            $html .= "<tr>
                                    <td>".$char['name']."</td>
                                    <td>".$char['class']."</td>
                                    <td>".$char['level']."</td>
                                    <td>".$char['race']."</td>
                                    <td>".$char['gender']."</td>
                                    <td>".$char['faction']."</td>
                                    <td>".$char['guild']."</td>
                                    <td>".$char['server']."</td>
                                    <td>
                                            <table cellspacing='0' cellpadding='0'>
                                                <tr>
                                                    <td><span class='ui-icon ui-icon-pencil' onClick='fillform_editchar(\"".htmlentities($char['name'])."\",\"".$char['class_id']."\",\"".htmlentities($char['guild'])."\",\"".$char['server_id']."\",\"".$char['level']."\",\"".$char['race_id']."\",\"".$char['gender_id']."\")' title='".guil('edit')."'></span></td>
                                                    <td><span class='ui-icon ui-icon-trash' onClick='document.location.href=\"?op=deletechar&charid=".$char_id."\"' title='".guil('delete')."'></span></td>
                                                </tr>
                                            </table>
                                        </td>
                                </tr>";
                        }
                        $html .= "</tbody></table>";
                    }
                    $html .= "<p>".guil('createnewchar').":</p>
                            <script type='text/javascript'>
                                function fillform_editchar(charname, charclass, guild, server, level, race, gender) {
                                    $('#editchar input[name|=\"charname\"]').val(charname);
                                    $('#editchar input[name|=\"guild\"]').val(guild);
                                    $('#editchar input[name|=\"charlevel\"]').val(level);
                                    $('#editchar select[name|=\"charclass\"]').val(charclass);
                                    $('#editchar select[name|=\"server\"]').val(server);
                                    $('#editchar select[name|=\"charrace\"]').val(race);
                                    $('#editchar select[name|=\"chargender\"]').val(gender);
                                }
                            </script>
                            <form id='editchar' action='' method='POST'>
                                <table>
                                    <tr><td>".guil('name').":</td><td><input type='text' name='charname' size='50'></td></tr>
                                    <tr><td>".guil('class').":</td><td>
                                        <select name='charclass'>";
                    $res = sql_query("select class_id, parent_class_id, ".$_SESSION['language']." from class order by ".$_SESSION['language']);
                    while(list($class_id, $parent_class_id, $class_name) = sql_fetch_row($res)) {
                        $html .= "<option value='".$class_id."'>".$class_name."</option>";
                    }
                    $html .= "</select>
                                    </td></tr>
                                    <tr><td>".guil('guild').":</td><td><input type='text' name='guild' size='50'></td></tr>
                                    <tr><td>".guil('server').":</td><td>
                                        <select name='server'>";
                    $res = sql_query("select id, name from server order by name");
                    while(list($server_id, $server_name) = sql_fetch_row($res)) {
                        $html .= "<option value='".$server_id."'>".$server_name."</option>";
                    }
                    $html .= "</select>
                                    </td></tr>
                                    <tr><td>".guil('level').":</td><td><input type='text' name='charlevel' value='50' size='3'></td></tr>
                                    <tr><td>".guil('race').":</td><td><select name='charrace'>";
                    $res = sql_query("select id, ".$_SESSION['language']." from race order by ".$_SESSION['language']);
                    while(list($race_id, $race_name) = sql_fetch_row($res)) {
                        $html .= "<option value='".$race_id."'>".$race_name."</option>";
                    }
                    $html .= "</select></td></tr>
                                <tr><td>".guil('gender').":</td><td><select name='chargender'><option value='m'>".guil('male')."</option><option value='f'>".guil('female')."</option></select></td></tr>
                                </table>
                                <input type='hidden' name='op' value='createchar'>
                                <p style='text-align:left'><input type='submit' value='".guil('save')."' $disable_ui_element></p>
                            </form>
                        </div>";
                }
                
                $html .= "<h3><a href='#'>".guil('sendbugreport')."</a></h3>
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
                </div><!-- accordion options -->";
                
                $this->content = $html;
                $this->nobutton = 1;
                
                if(! $_SESSION['min_fight_duration'] || !$_SESSION['log_id'] || $openOptions==1) {
                    $this->autoopen = 1;
                    $this->modal = 1;
                } else {
                    $this->autoopen = 0;
                    $this->modal = 0;
                }
        }
    }
?>