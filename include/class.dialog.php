<?
    class Dialog {
        var $name;
        var $title;
        var $buttontext;
        var $content;
        var $modal;
        var $autoopen;
        var $nobutton;
        var $width;
        var $position;
        var $important;
        
        function Dialog($name, $title='', $content='', $modal=false, $autoopen=false) {
            $this->name = $name;
            $this->title = $title;
            $this->content = $content;
            $this->modal = $modal;
            $this->autoopen = $autoopen;
            
            $this->width = 800;
            $this->position = "'top'";
            $this->important = 0;
        }
        
        function htmlskeleton() {
            return "<div id='dialog_".$this->name."'>".$this->content."</div>";
        }
        
        function htmlskeleton_button() {
            return "<button id='button_open_dialog_".$this->name."'>".($this->buttontext?$this->buttontext:$this->title)."</button>";
        }
        
        function jsskeleton() {
            $js = '$( "#dialog_'.$this->name.'" ).dialog({
                    autoOpen: '.($this->autoopen?'true':'false').',
                    modal: '.($this->modal?'true':'false').',
                    title: "'.$this->title.'"';
            if($this->width) {
                $js .= ',width: '.$this->width;
            }
            if($this->position) {
                $js .= ',position: '.$this->position;
            }
            $js .= '});';
            if(!$this->nobutton) {
                $js .= $this->jssskeleton_button();
            }
            return $js;
        }
        
        function jssskeleton_button() {
            return '$( "#button_open_dialog_'.$this->name.'" ).click(function() {
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