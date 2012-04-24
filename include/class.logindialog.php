<?
    class LoginDialog extends Dialog {
        function LoginDialog($message='') {
            parent::Dialog('login', guil('dialog_login_title'), '', 1, 1);

            $html  = "<p>".guil('dialog_login_useforumacc')."</p>
                <form action='' method='POST'>
                    <table>
                        <tr><td>".guil('username').":</td><td><input type='text' name='username'></td></tr>
                        <tr><td>".guil('password').":</td><td><input type='password' name='password'></td></tr>
                        <tr><td>".guil('preferedlanguage').":</td><td>
                            <select name='language' onChange='document.location.href=\"?op=setlanguage&language=\"+this.value'>";
            foreach(array('de' => 'Deutsch', 'en' => 'English') as $short => $long) {
                if($_SESSION['language'] == $short) {
                    $selected = "selected='selected'";
                } else {
                    $selected = "";
                }
                $html .= "<option value='".$short."' ".$selected.">".$long."</option>";
            }
            $html .= "</select>
                            </td></tr>
                            <tr><td colspan='2' align='center'><input type='submit' value='".guil('login')."'></td></tr>
                        </table>";
            if($login_message) {
                $html .= "<p style='color:red; text-align:center'>".$message."</p>";
            }
            $html .= "<input type='hidden' name='op' value='login'>
                    </form>
                    <p><a href='/forum/ucp.php?mode=register'>".guil('register')."</a> <a href='?op=demo'>".guil('startdemo')."</a></p>";

            $this->nobutton = 1;
            $this->content = $html;
        }
        
        function jsskeleton() {
            return '$( "#dialog_'.$this->name.'" ).dialog({
                    autoOpen: '.($this->autoopen?'true':'false').',
                    modal: '.($this->modal?'true':'false').',
                    title: "'.$this->title.'"
                });';
        }
    }
?>