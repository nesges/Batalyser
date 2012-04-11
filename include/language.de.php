<?
    global $guil;

    $guil['de'] = array(
        // general
        'back'                      => 'Zur�ck',                                // back-button on dialogs
        'login'                     => 'Anmelden',                              // dialog login submit button
        'username'                  => 'Benutzername',                          // dialog login
        'password'                  => 'Passwort',                              // dialog login
        'register'                  => 'Registrieren',                          // dialog login
        'startdemo'                 => 'Demo starten',                          // dialog login
        'publicize'                 => 'Ver�ffentlichen',                       // options dialog
        'depublicize'               => 'Ver�ffentlichung aufheben',             // options dialog
        'download'                  => 'Download',                              // options dialog

        // accordion titles
        'fight'                     => 'Kampf',                                 // accordiontitle
        'duration'                  => 'Dauer',                                 // accordiontitle
        'damagedone'                => 'Schaden verursacht',                    // accordiontitle
        'healdone'                  => 'Punkte geheilt',                        // accordiontitle
        'threatdone'                => 'Bedrohung erzeugt',                     // accordiontitle
        'damagestatsfor'            => 'Schadensstatistik f�r',                 // accordiontitle
        'shown'                     => 'angezeigt',
        'hidden'                    => 'versteckt',
        'logsummary'                => 'Gesamtes Log',
        'active'                    => 'aktiv',
        'between'                   => 'zwischen',
        'and'                       => 'und',

        // page header
        'logoff'                    => 'Abmelden',                              // button on the page header
        'options'                   => 'Optionen',                              // button on the page header
        'forum'                     => 'Forum',                                 // button on the page header
        'logfile'                   => 'Logfile',                               // info on the page header, options dialog
        'loggedinas'                => 'Eingeloggt als',                        // info on the page header

        // options dialog
        'view'                      => 'Ansicht',                               // options dialog
        'view_chooselogfile'        => 'Logfile anzeigen',                      // options dialog
        'minfightduration'          => 'Mindest-Kampfdauer (in Sekunden)',      // options dialog, info on page header
        'preferedlanguage'          => 'Bevorzugte Sprache',                    // options dialog, info on page header
        'viewlogwiththissettings'   => 'Log mit diesen Einstellungen ansehen',  // options dialog
        'upload'                    => 'Upload',                                // options dialog, datatable column header
        'zipfilenotice'             => 'Wenn du dein Logfile vor dem Upload zip-komprimierst, wird die Verarbeitung wesentlich schneller sein und du kannst mehr Daten hochladen.',      // options dialog
        'logpublicize'              => 'Soll dein Log �ffentlich zug�nglich sein?',                     // options dialog
        'logmergeable'              => 'Soll dein Log mit Logs anderer Spieler zusammenf�hrbar sein?',  // options dialog
        'startupload'               => 'Upload starten',                        // options dialog
        'upload_demonotice'         => 'In der Demo ist der Upload deaktiviert.',                       // options dialog
        'availablelogs'             => 'Vorhandene Logfiles',                   // options dialog
        'yourlogs'                  => 'Deine Logfiles',                        // options dialog
        'deletechosenlogs'          => 'Gew�hlte Logfiles l�schen',             // options dialog
        'delete_demonotice'         => 'In der Demo ist das L�schen deaktiviert.',  // options dialog
        'sendbugreport'             => 'Bugreport senden',                      // options dialog
        'bugreport_explanation'     => '<p>Batalyser ist noch in einem sehr fr�hen Entwicklungsstatium, es ist daher sehr wahrscheinlich, dass du Bugs finden wirst. Wenn dem so ist, freu ich mich �ber deine Meldung!</p>
                    <p>Deine Nachricht wird automatisch ins <a href="http://batalyser.net/forum/viewforum.php?f=4">Bugreports-Forum</a> gepostet. Wenn du im Forum angemeldet bist unter deinem Namen, ansonsten als Gast.
                    Es werden automatisch bestimmte Session-Daten ermittelt (mit welchem User bist du angemeldet, welches Log schaust du grade an, welche Optionen sind gesetzt..) und mir zugeschickt, diese werden aber nicht im Forum ver�ffentlicht.</p>',  // options dialog
        'bugreport_shorttitle'      => 'Eine kurze �berschrift',                // options dialog
        'bugreport_description'     => 'Beschreibe hier den Fehler',            // options dialog
        'bugreport_yourmail'        => 'Deine E-Mail-Adresse (optional)',       // options dialog
        'thankyou'                  => 'Vielen Dank!',                          // options dialog

        // message dialogs
        'dialog_upload'             => '<p>Der Upload wird gestartet. Im Anschluss wird das Combatlog geparst. Das kann einige Minuten dauern, bitte hab ein wenig Geduld.</p>
                    <p>Wenn der Parser fertig ist, wird dieses Fenster geschlossen und dein Log wird mit den aktuellen Einstellungen angezeigt.</p>',
        'dialog_upload_title'       => 'Upload gestartet',
        'dialog_error_title'        => 'Oops, ein Fehler!',
        'dialog_message_title'      => 'Eine Nachricht f�r Dich',
        'dialog_login_useforumacc'  => 'Batalyser nutzt die Foren-Accounts zur Userverwaltung. Melde Dich bitte mit deinem Foren-Login an.',
        'dialog_login_title'        => 'Login',
        'dialog_help_title'         => 'Hilfe',
        'dialog_options_title'      => 'Optionen',

        // messages
        'login_wrongcredentials'    => 'Falsche Benutzerdaten. Versuch\'s nochmal :)',
        'logpublicizedunder'        => 'Dein Log ist jetzt �ffentlich erreichbar unter',
        'logdepublicized'           => 'Dein Log ist jetzt nicht mehr �ffentlich erreichbar.',
        'logfilecontainsnodata'     => 'Das Log enth�lt keine Kampfdaten.',
        'filenotuploaded'           => 'Die Datei konnte nicht hochgeladen werden.',
        'nofilechosenorfiletolarge' => 'Du hast keine Datei zum Upload ausgew�hlt oder sie war zu gro�.',
        'thanksforyourbugreport'    => 'Vielen Dank f�r deine Nachricht! Du findest sie im Forum unter',
        'bugreportwithoutmessage'   => 'Du hast vergessen eine Nachricht einzugeben.',
        'notthelogyourelookingfor'  => 'Das Logfile wurde nicht von dir hochgeladen und ist nicht (mehr) �ffentlich zug�nglich.',
        'tip_toolowminfightduration'=> 'Tipp: Wenn keine einzelnen K�mpfe angezeigt werden, dann pr�fe ob du in den Optionen eine zu hohe Mindest-Kampfdauer gew�hlt hast.',

        // datatable column headers
        'date'                      => 'Datum',                                 // options dialog
        'at'                        => 'am',                                    // accordiontitle
        'from'                      => 'von',                                   // options dialog, accordiontitle
        'to'                        => 'bis',                                   // options dialog, accordiontitle
        'chars'                     => 'Chars',                                 // options dialog
        'fights'                    => 'K�mpfe',                                // options dialog
        'enemies'                   => 'Gegner',                                // options dialog
        'filename'                  => 'Dateiname',                             // options dialog
        'actions'                   => 'Aktionen',                              // options dialog

        // hit types
        'normal'                    => 'Normal',
        'hitall'                    => 'Treffer (hit+crit)',
        'hitnoncrit'                => 'Treffer (noncrit)',
        'crit'                      => 'Kritisch',
        'miss'                      => 'Verfehlt',
        'dodge'                     => 'Ausgewichen',
        'parry'                     => 'Parriert',
        'deflect'                   => 'Schild',
        'resist'                    => 'Widerstanden',
        'immune'                    => 'Immun',
        
        // tabs
        'hitstatistic'              => 'Trefferstatistik',
        'healhitstatistic'          => 'Heiltrefferstatistik',
        'counterhitstatistic'       => 'Gegentrefferstatistik',
        'damageperability'          => 'Schaden pro F�higkeit',
        'healperability'            => 'Heilung pro F�higkeit',
        'xpspertarget'              => 'DPS/HPS/TPS pro Ziel',
        'ability'                   => 'F�higkeit',
        'target'                    => 'Ziel',
        'damage'                    => 'Schaden',
        'heal'                      => 'Heilung',
        'threat'                    => 'Bedrohung',
        'tankstats_note1'           => 'Dieser Tab zeigt die Werte deiner Gegner gegen dich. Wieviel Schaden hat ein Gegner an dir gemacht? Wie oft hat er verfehlt? Wie oft hast du widerstanden?',
        'tankstats_note2'           => 'In der Gesamtansicht werden DPS-Werte ausgeblendet, da sie auf die Gesamtdauer des Logs nicht sinnvoll darzustellen sind.',
        'sum'                       => 'Summe',
        'overall'                   => 'Gesamt',
        'fightprogress'             => 'Kampfverlauf',
        'graphs'                    => 'Graphen',
        'incdamageprogress'         => 'Schadensverlauf (in)',
        'fullfightstats_note_companion' => 'In den in- und Verlauf-Spalten wird ein evtl. vorhandener Companion nicht ber�cksichtigt.',
        'progress'                  => 'Verlauf',
        'time'                      => 'Zeit',
        'source'                    => 'Quelle',
        'effect'                    => 'Effekt',
        'damagetype'                => 'Schadensart'
    );
?>