<?
    class HelpDialog extends Dialog {
        function HelpDialog() {
            parent::Dialog('help', guil('dialog_help_title'), '', 1, 0);
            
            $html = "<img src='http://geekandpoke.typepad.com/.a/6a00d8341d3df553ef014e8b8f1c5b970d-800wi' alt=''>";
            
            $this->content = $html;
            $this->nobutton = 1;
        }
    }
?>