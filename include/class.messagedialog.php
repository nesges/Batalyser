<?
    class MessageDialog extends Dialog {
        function MessageDialog($message, $title, $returnto='') {
            parent::Dialog('message', $title, '', 1, 1);
            
            if(!$returnto) {
                $returnto = $this->jsclose();
                $backbuttontext = guil('close');
            } elseif($returnto=='back') {
                $returnto = 'document.location.href="?op=noop"';
                $backbuttontext = guil('back');
            } else {
                $backbuttontext = guil('back');
            }

            $html = "<p>$message</p><center><button onClick=\"".$returnto."\">".$backbuttontext."</button></center>";
            
            $this->content = $html;
            $this->important = 1;
            $this->nobutton = 1;
            unset($this->width);
            unset($this->position);
        }
    }
?>