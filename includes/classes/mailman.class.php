<?php
if(!defined('FUNC_FILE')) die('Access is limited');

class MAILMAN {
    public $sendmail = '/usr/sbin/sendmail';
    public $readingto_addres = '';
    public $attachment = array();
    public $attached = array();
    public $is_html = false;
    public $type_send = false;
    public $readingto = false;
    public $charset = '';
    public $tpl = '';
    public $smtp = array(
        'host'     => '',
        'user'     => '',
        'password' => '',
        'port'     => '',
        'ssl'      => '',
    );
    public $status = false;
    public $content_type = false;
    public $priority = 3;
    public $MAIL;

    public function __construct() {
    global $config, $main;
        if(!is_object($this->MAIL)){
            require "includes/classes/phpmailer/class.phpmailer.php";
            $this->MAIL = new PHPMailer();
            $this->MAIL->Priority = $this->priority;
            $this->MAIL->Sendmail = $this->sendmail;
        }
        $this->status = true;
        $this->readingto = false;
        $this->is_html = $main->config['type_emeils']=='text/html' ? true : false;
        $this->type_send = $main->config['type_email_send'];
        $this->content_type = $main->config['type_emeils'];
        $this->readingto_addres = '';
        $this->charset = $main->config['charset_mail'];
        
        $this->smtp = array(
            'host'     => $main->config['smtp_host'],
            'user'     => $main->config['smtp_user'],
            'password' => $main->config['smtp_password'],
            'port'     => $main->config['smtp_port'],
            'ssl'      => $main->config['smtp_ssl']==ENABLED?'ssl':'',
        );
        $this->tpl = file_exists(TEMPLATE_PATH."{$main->tpl}/other/mail.tpl") ? TEMPLATE_PATH."{$main->tpl}/other/mail.tpl" : $main->config['template_mail'];
    }

    public function __destruct() {
    }

    public function headers($from, $to, $subject='[ No subject ]', $message='', $bbc=array(), $cc=array()){
        if($this->type_send=='smtp'){
            $this->MAIL->Host       = $this->smtp['host'];
            //$this->MAIL->SMTPDebug  = 2; //debugging SMTP
            if(!empty($this->smtp['user'])) $this->MAIL->SMTPAuth = true;
            if(!empty($this->smtp['ssl'])) $this->MAIL->SMTPSecure = "ssl";
            $this->MAIL->Port       = $this->smtp['port'];
            $this->MAIL->Username   = $this->smtp['user'];
            $this->MAIL->Password   = $this->smtp['password'];
        }
        $this->MAIL->CharSet = $this->charset;
        $this->MAIL->AddAddress($to['mail'], $to['name']);
        $this->MAIL->SetFrom($from['mail'], $from['name']);
        $this->MAIL->AddReplyTo($from['mail'], $from['name']);
        $this->MAIL->Subject = $subject;
        if($this->is_html){
            $this->MAIL->AltBody = "To view the message, please use an HTML compatible email viewer!";
            $this->MAIL->MsgHTML($this->use_tpl($subject, $message));
            $this->MAIL->IsHTML(true);
        } else {
            main::init_class('html2text');
            $h2t = new html2text($message);
            $this->MAIL->Body = $h2t->get_text();
        }
        if(!empty($bbc)) foreach($bbc as $b) $this->MAIL->AddBCC($b['mail'], $b['name']);
        if(!empty($cc)) foreach($cc as $c) $this->MAIL->AddCC($c['mail'], $c['name']);
        if($this->readingto==true) $this->MAIL->ConfirmReadingTo = $this->readingto_addres;
        if(!empty($this->attachment)) foreach($this->attachment as $f) $this->MAIL->AddAttachment($f);
    }

    public function send(){
        if($this->type_send=='smtp') $this->MAIL->IsSMTP();
        elseif($this->type_send=='qmail') $this->MAIL->IsQmail();
        elseif($this->type_send=='sendmail') $this->MAIL->IsSendmail();
        if($this->status==true) $send = $this->MAIL->Send();
        else $send = false;
        $this->clear();
        return $send;
    }

    public function clear(){
        $this->MAIL->ClearAddresses();
        $this->MAIL->ClearAttachments();
        $this->MAIL->ClearCCs();
        $this->MAIL->ClearBCCs();
        $this->MAIL->ClearReplyTos();
        $this->attachment = array();
    }

    public function attach($file){
        $this->attachment[] = $file;
    }
    
    private function use_tpl($subject, $content){
    global $config;
        if(!empty($this->tpl)){
            return str_replace(array('$subject', '$content', '$logo_small'), array($subject, $content, "<img src='http://".get_host_name()."/{$config['sitelogo']}' alt='' />"), file_get_contents($this->tpl));
        } else return $content;
    }
}

?>