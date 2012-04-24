<?
    global $guil;

    $guil['de'] = array(
        // general
        'back'                      => 'Back',                                  // back-button on dialogs
        'login'                     => 'Login',                                 // dialog login submit button
        'username'                  => 'Username',                              // dialog login
        'password'                  => 'Password',                              // dialog login
        'register'                  => 'Register',                              // dialog login
        'startdemo'                 => 'Start Demo',                            // dialog login
        'publicize'                 => 'Publicize',                             // options dialog
        'depublicize'               => 'Revoke Publication',                    // options dialog
        'download'                  => 'Download',                              // options dialog
        'male'                      => 'Male',
        'female'                    => 'Female',
        'republic'                  => 'Republic',
        'empire'                    => 'Empire',
        'save'                      => 'Save',
        'edit'                      => 'Edit',
        'delete'                    => 'Delete',
        'uploaddate'                => 'Uploaddate',
        'none'                      => 'none',
        
        // class properties
        'name'                      => 'Name',
        'class'                     => 'Class',
        'level'                     => 'Level',
        'race'                      => 'Race',
        'gender'                    => 'Gender',
        'faction'                   => 'Faction',
        'guild'                     => 'Guild',
        'server'                    => 'Server',

        // accordion titles
        'fight'                     => 'Fight',                                 // accordiontitle
        'duration'                  => 'Duration',                              // accordiontitle
        'damagedone'                => 'Damage done',                           // accordiontitle
        'healdone'                  => 'Hitpoints healed',                      // accordiontitle
        'threatdone'                => 'Threat generated',                      // accordiontitle
        'healreceived'              => 'Heal received',                         // accordiontitle
        'damagestatsfor'            => 'Damagestatistics for',                  // accordiontitle
        'shown'                     => 'shown',
        'hidden'                    => 'hidden',
        'logsummary'                => 'Full Log',
        'active'                    => 'active',
        'between'                   => 'between',
        'and'                       => 'and',

        // page header
        'logoff'                    => 'Logoff',                                // button on the page header
        'options'                   => 'Options',                               // button on the page header
        'forum'                     => 'Forum',                                 // button on the page header
        'logfile'                   => 'Logfile',                               // info on the page header, options dialog
        'loggedinas'                => 'Logged in as',                          // info on the page header
        'publicurl'                 => 'Public URL',
        'minfightduration_short'    => 'Min. Fight-Duration (s)',               // info on page header
        'preferedlanguage_short'    => 'Pref. Language',                        // info on page header

        // options dialog
        'view'                      => 'Configure View',                        // options dialog
        'view_chooselogfile'        => 'View Logfile',                          // options dialog
        'minfightduration'          => 'Minimum Fight-Duration (seconds)',      // options dialog
        'preferedlanguage'          => 'Prefered Language',                     // options dialog
        'viewlogwiththissettings'   => 'View Log using these Settings',         // options dialog
        'upload'                    => 'Upload',                                // options dialog, datatable column header
        'zipfilenotice'             => 'Zip-compress your logfile before upload, to speed up processing time and upload more data.',
        'logpublicize'              => 'Shall your log be publicly available?', // options dialog
        'logmergeable'              => 'Shall your log be mergeable with other players logs?',  // options dialog
        'startupload'               => 'Start Upload',                          // options dialog
        'upload_demonotice'         => 'Upload is deactivatet in the Demo.',    // options dialog
        'availablelogs'             => 'Available Logfiles',                    // options dialog
        'yourlogs'                  => 'Your Logfiles',                         // options dialog
        'deletechosenlogs'          => 'Delete chosen Logfiles',                // options dialog
        'delete_demonotice'         => 'Deleting ist deactivated in the Demo.', // options dialog
        'sendbugreport'             => 'Send a Bugreport',                      // options dialog
        'bugreport_explanation'     => '<p>Batalyser is still in an early development stage and it is very likely that you will encounter Bugs. If so, I\'m happy to get your report!</p>
                    <p>Your message will be postet automaticly into the <a href="http://batalyser.net/forum/viewforum.php?f=4">Bugreports-Forum</a>. If you\'re logged in to the forums, it will be postet under your account name, otherwise as Guest.
                    Some session-data will be gathered automaticly (which user are you logged in with, which log are you viewing, what settings are used..) and mailed to me. But these are not posted to the forums.</p>',  // options dialog
        'bugreport_shorttitle'      => 'A short title',                         // options dialog
        'bugreport_description'     => 'Describe the error here',               // options dialog
        'bugreport_yourmail'        => 'Your mail-adress (optional)',           // options dialog
        'thankyou'                  => 'Thank you!',                            // options dialog
        
        'yourchars'                 => 'Your Chars',
        'createnewchar'             => 'Create new or edit character',

        // message dialogs
        'dialog_upload'             => '<p>Upload is beeing started. After that, your combatlog will be parsed for the first time. This may take a couple of minutes, please be patient.</p>
                    <p>Right after the parser is done, this dialog will be closed and your log will be displayed with your current settings.</p>',
        'dialog_upload_title'       => 'Upload startet',
        'dialog_error_title'        => 'Oops, an Error!',
        'dialog_message_title'      => 'A Message for You',
        'dialog_login_useforumacc'  => 'Batalyser utilizes the accounts of our Forums for user management. Please log in with your Forumname.',
        'dialog_login_title'        => 'Login',
        'dialog_help_title'         => 'Help',
        'dialog_options_title'      => 'Options',

        // messages
        'login_wrongcredentials'    => 'Wrong Usercredentials. Try again! :)',
        'logpublicizedunder'        => 'Your Log is now publicly available under',
        'logdepublicized'           => 'Your Log is\'nt publicly available anymore',
        'logfilecontainsnodata'     => 'The Log contains no fightdata.',
        'filenotuploaded'           => 'The File could not be uploaded.',
        'nofilechosenorfiletolarge' => 'You haven\'t chosen a file to upload or it was too large.',
        'thanksforyourbugreport'    => 'Thanks for your message! You can find it in the forums under',
        'bugreportwithoutmessage'   => 'You forgot to type a message.',
        'notthelogyourelookingfor'  => 'This log wasn\'t uploaded by you and is not or no longer publicly available.',
        'tip_toolowminfightduration'=> 'Tip: If no single fights are displayed, check if you have set a too long minimun fight duration in the settings.',

        // datatable column headers
        'date'                      => 'Date',                                  // options dialog
        'at'                        => 'at',                                    // accordiontitle
        'from'                      => 'from',                                  // options dialog, accordiontitle
        'to'                        => 'to',                                    // options dialog, accordiontitle
        'chars'                     => 'Chars',                                 // options dialog
        'fights'                    => 'Fights',                                // options dialog
        'enemies'                   => 'Enemies',                               // options dialog
        'filename'                  => 'Filename',                              // options dialog
        'actions'                   => 'Actions',                               // options dialog

        // hit types
        'normal'                    => 'Normal',
        'hitall'                    => 'Hit (hit+crit)',
        'hitnoncrit'                => 'Hit (noncrit)',
        'crit'                      => 'Critical',
        'miss'                      => 'Missed',
        'dodge'                     => 'Dodged',
        'parry'                     => 'Parried',
        'deflect'                   => 'Deflected',
        'resist'                    => 'Resisted',
        'immune'                    => 'Immune',
        
        // tabs
        'hitstatistic'              => 'Hitstatistics',
        'healhitstatistic'          => 'Healcritstatistics',
        'counterhitstatistic'       => 'Received-Hitstatistics',
        'overhealstatistic'         => 'Overhealstatistics (experimental)',
        'damageperability'          => 'Damage per Ability',
        'healperability'            => 'Heal per Ability',
        'xpspertarget'              => 'DPS/HPS/TPS per Target',
        'ability'                   => 'Ability',
        'target'                    => 'Target',
        'damage'                    => 'Damage',
        'heal'                      => 'Heal',
        'threat'                    => 'Threat',
        'tankstats_note1'           => 'This tab displays your enemies stats against you. How much damage has an enemy done to you? How often has he missed? How often have you resisted?',
        'tankstats_note2'           => 'DPS-values are not shown in the summary, because they make no sense over the full duration of a logfile.',
        'sum'                       => 'Sum',
        'overall'                   => 'Overall',
        'fightprogress'             => 'Fightprogress',
        'graphs'                    => 'Graphs',
        'incdamageprogress'         => 'Damageprogress (in)',
        'fullfightstats_note_companion' => 'The in- and progress-columns don\'t take your companion into account.',
        'progress'                  => 'Progress',
        'time'                      => 'Time',
        'source'                    => 'Source',
        'effect'                    => 'Effect',
        'damagetype'                => 'Damagetype',
        'enlarge_grafic'            => 'Enlarge Grafic',
        'effective'                 => 'effective',
        
        // dialog charassign
        'assignyourchars'           => 'Assign your chars to this log',
        'assignyourchars_note'      => 'Assign your chars to the chars in the currently loaded log here', 
        'createchars_note'          => 'You may create your chars in the Options dialog.',
        'noneofyourcharsfound'      => 'None of your chars where found in the currently loaded log.',
        'saveassignment'            => 'Save Assignment',
        'assignedchars'             => 'Assigned Chars',
        
        // dialog errormessage
        'close'                     => 'Close',
        
        'guildiespubliclogs'        => 'Public Logs of your Guildmates'
    );
?>