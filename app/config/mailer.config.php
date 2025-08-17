<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config 				= array();

$config['smtp_debug']	= 0;
$config['debug_output']	= 'html';
$config['host']			= 'mail.forextravel.com.au';
$config['port']			= '587';
$config['smtp_secure']	= 'tls';
$config['smtp_auth']	= true;
$config['user_name']	= 'crms@forextravel.com.au';
$config['password']		= 'ZUd[Zb5XD2n}';
$config['from']			= array('crms@forextravel.com.au', 'Forex Travel');
$config['reply_to']		= array('crms@forextravel.com.au', 'Forex Travel');
$config['cc']			= array();
$config['bcc']			= array();
$config['to']			= array('', '');
$config['subject']		= '';
$config['content']		= '';
$config['alt_body']		= '';
$config['attachments']	= array();

/* End of file mailer.php */
/* Location: ./application/config/mailer.config.php */
