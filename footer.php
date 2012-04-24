<?
    global $cache_stats_filename, $openOptions, $duration, $logfiles, $guil;
    
    $jquery_common_init = '
                $( ".accordion" ).accordion({
                    autoHeight: false,
                    collapsible: true
                });
                $( ".tabs" ).tabs();';
?>              
        <script type="text/javascript">
            $(document).ajaxComplete(function(e, xhr, settings){
                <?=$jquery_common_init?>
                $( ".dataTable_ajaxLoaded").dataTable( {
                    "bJQueryUI": true,
                    "bStateSave": false,
                    "bPaginate": false,
                    "bFilter": true,
                    "bSort": true,
                    "bInfo": false,
                    "bAutoWidth": false,
                    "bRetrieve": true,
                    "sDom": "R<\'H\'W>t",
                    "fnRowCallback": function( nRow, aaData, iDisplayIndex ) {
                        $("td:odd", nRow).addClass( "col2" );
                        return nRow;
                    },
                    "aaSorting": [[ 0, "asc" ]]
                });
                $( ".dataTableFullFightStats_ajaxLoaded" ).dataTable( {
                    "bJQueryUI": true,
                    "bStateSave": false,
                    "bPaginate": false,
                    "bFilter": true,
                    "bSort": true,
                    "bInfo": false,
                    "bAutoWidth": false,
                    "bRetrieve": true,
                    "sDom": "R<\'H\'W>t",
                    "aaSorting": [[ 0, "asc" ]]
                });
                $( ".button_open_dialog_misc" ).button();
                $( ".button_open_dialog_misc" ).click(function() {
                    $( "#dialog_misc" ).dialog( "open" );
                    $("#dialog_misc iframe").attr("src", $(this).attr("href"));
                    return false;
                });
                $(".accordion_ajax_ajax").accordion({
                    header: "h4",
                    clearStyle: true,
                    autoHeight: false,
                    active: false,
                    collapsible: true,
                    change: function(event, ui){
                        var clicked = $(this).find(".ui-state-active").attr("id");
                        $("#"+clicked).load("/widgets/"+clicked);
                    }
                });
                $("h4", ".accordion_ajax_ajax").click(function(e) {
                    var contentDiv = $(this).next("div");
                    contentDiv.html("<iframe frameborder='0' scrolling='no' width='100%' height='550' src='" + $(this).find("a").attr("href") + "'></iframe>");
                });
            });
            
            $(document).ready(function() {
                <?=$jquery_common_init?>
                $( "input:submit, a, button", ".navigation" ).button();
                $( "a", ".navigation" ).click(function() { return false; });
                $( "input:submit, button" ).button();
                $(".accordion_ajax").accordion({
                    header: "h3",
                    clearStyle: true,
                    autoHeight: false,
                    active: false,
                    change: function(event, ui){
                        var clicked = $(this).find(".ui-state-active").attr("id");
                        $("#"+clicked).load("/widgets/"+clicked);
                    }
                }); 
                $("h3", ".accordion_ajax").click(function(e) {
                    var contentDiv = $(this).next("div");
                    contentDiv.html("<img src='../../images/loading.gif' alt='Loading...'> Loading...");
                    contentDiv.load($(this).find("a").attr("href"));
                });
                $( ".dataTable").dataTable( {
                    "bJQueryUI": true,
                    "bStateSave": false,
                    "bPaginate": false,
                    "bFilter": true,
                    "bSort": true,
                    "bInfo": false,
                    "bAutoWidth": false,
                    "sDom": "R<\'H\'W>t",
                    "fnRowCallback": function( nRow, aaData, iDisplayIndex ) {
                        $("td:odd", nRow).addClass( "col2" );
                        return nRow;
                    },
                    "aaSorting": [[ 0, "asc" ]]
                });
                $( ".dataTableSimple").dataTable( {
                    "bJQueryUI": true,
                    "bStateSave": false,
                    "bPaginate": false,
                    "bFilter": false,
                    "bSort": true,
                    "bInfo": false,
                    "bAutoWidth": false,
                    "fnRowCallback": function( nRow, aaData, iDisplayIndex ) {
                        $("td:odd", nRow).addClass( "col2" );
                        return nRow;
                    },
                    "aaSorting": [[ 0, "asc" ]]
                });
                $( "#accordion_options" ).accordion({
                    <?
                        if(!count($logfiles)) {
                            print "active: 1,\n";
                        }
                    ?>
                    autoHeight: false,
                });
                
                
                $('.dataTableScrolling').dataTable( {
                    'bJQueryUI': true,
                    'bStateSave': false,
                    "bPaginate": false,
                    /* 'sScrollY': '200px',          buggy atm (datatables 1.9)
                       'bScrollAutoCss': true,  */
                    "bFilter": true,
                    "bSort": true,
                    "bInfo": false,
                    "bAutoWidth": false,
                    'sDom': 'R<"H"W>t',
                    "fnRowCallback": function( nRow, aaData, iDisplayIndex ) {
                        $('td:odd', nRow).addClass( 'col2' );
                        return nRow;
                    },
                    "aaSorting": [[ 0, "asc" ]]
                });
                $('.dataTableFullFightStats').dataTable( {
                    'bJQueryUI': true,
                    'bStateSave': false,
                    "bPaginate": false,
                    /* 'sScrollY': '200px',          buggy atm (datatables 1.9)
                       'bScrollAutoCss': true,  */
                    "bFilter": true,
                    "bSort": true,
                    "bInfo": false,
                    "bAutoWidth": false,
                    'sDom': 'R<"H"W>t',
                    "aaSorting": [[ 0, "asc" ]]
                });
                $('#datatable_optionsLogfiles').dataTable( {
                    'bJQueryUI': true,
                    "bSort": true,
                    "bFilter": false,
                    "fnRowCallback": function( nRow, aaData, iDisplayIndex ) {
                        $('td:odd', nRow).addClass( 'col2' );
                        return nRow;
                    },
                    "aaSorting": [[ 1, "desc" ]]
                });
                $( "#min_fight_duration_slider" ).slider({
                    <?
                        if($_SESSION['min_fight_duration']) {
                            print "value: ".$_SESSION['min_fight_duration'].",\n";
                        } else {
                            print "value: 11,\n";
                        }
                    ?>
                    min: 1,
                    max: 600,
                    step: 10,
                    slide: function( event, ui ) {
                        $( "#min_fight_duration_slider_value" ).val( ui.value );
                    }
                });
                $( "#min_fight_duration_slider_value" ).val( $( "#min_fight_duration_slider" ).slider( "value" ) );
                $( ".button_open_dialog_misc" ).click(function() {
                    $( "#dialog_misc" ).dialog( "open" );
                    return false;
                });
                $( "#dialog_misc" ).dialog({
                    autoOpen: false,
                    width: 800,
                    position: 'top'
                });
                $( "#button_start_upload" ).click(function() {
                    $( "#dialog_upload" ).dialog( "open" );
                    return false;
                });
                $( "#dialog_upload" ).dialog({
                    title: '<? print $guil[$_SESSION['language']]['dialog_upload_title']?>',
                    autoOpen: false,
                    modal: true
                });
                $('.dialog').dialog();
                <?
                    foreach($dialogs as $dialog) {
                        if(! $dialog->important) {
                            print $dialog->jsskeleton();
                            if($dialog->name == $_GET['opendialog']) {
                                print $dialog->jsopen();
                            }
                            switch($dialog->name) {
                                case 'options':
                                case 'help':
                                    print $dialog->jssskeleton_button();
                            }
                        }
                    }
                    foreach($dialogs as $dialog) {
                        if($dialog->important) {
                            print $dialog->jsskeleton();
                        }
                    }
                ?>
                $('a[title]').qtip({
                    position: {
                        my: 'bottom right',
                        at: 'top left',
                        target: 'mouse',
                        viewport: $("body")
                    },
                    style: {
		                classes: 'ui-tooltip-light ui-tooltip-shadow ui-tooltip-rounded'
	                }
                }); 
            } );    
        </script>
    </body>
</html>
<?
    exit();
?>