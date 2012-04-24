<?
    class ErrorMessageDialog extends MessageDialog {
        function ErrorMessageDialog($message, $returnto='') {
            parent::MessageDialog($message, guil('dialog_error_title'), $returnto);
        }
    }
?>