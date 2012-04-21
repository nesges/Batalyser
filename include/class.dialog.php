<?
    class Dialog {
        var $name;
        var $title;
        var $buttontext;
        var $content;
        var $modal;
        
        function Dialog($name, $title='', $content='', $modal=false) {
            $this->name = $name;
            $this->title = $title;
            $this->content = $content;
            $this->modal = $modal;
        }
        
        function htmlskeleton() {
            return "<div id='dialog_".$this->name."'>".$this->content."</div>";
        }
        
        function buttonskeleton($buttontext='') {
            if($buttontext) {
                $this->buttontext = $buttontext;
            }
            return "<button id='button_open_dialog_".$this->name."'>".($this->buttontext?$this->buttontext:$this->title)."</button>";
        }
        
        function jsskeleton() {
            return '$( "#dialog_'.$this->name.'" ).dialog({
                    autoOpen: false,
                    modal: '.($this->modal?'true':'false').',
                    title: "'.$this->title.'",
                    width: 800,
                    position: "top"
                });
                $( "#button_open_dialog_'.$this->name.'" ).click(function() {
                    '.$this->jsopen().'
                    return false;
                });';
        }

        function jsclose() {
            return "$( '#dialog_".$this->name."' ).dialog('close');";
        }
        function jsopen() {
            return "$( '#dialog_".$this->name."' ).dialog('open');";
        }

    }
?>