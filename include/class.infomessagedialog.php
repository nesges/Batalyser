<?
    class InfoMessageDialog extends MessageDialog {
        function InfoMessageDialog($message, $title='', $returnto='') {
            parent::MessageDialog($message, $title?$title:guil('dialog_message_title'), $returnto);
        }
    }
?>