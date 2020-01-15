<?php
// error_reporting(E_ALL);
// ini_set("display_errors", 1);

use \DrewM\MailChimp\MailChimp;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';

require_once "functions.php";
$config = getConfig($sub = true);

date_default_timezone_set("America/Mexico_City");

//CONFIGURACION:
$auth         = $config->configuracion->mailer->auth;
$to           = $config->configuracion->mailer->to;
$cc           = $config->configuracion->mailer->cc;
$bcc          = $config->configuracion->mailer->bcc;
$replyTo      = $config->configuracion->mailer->replyTo;
$from         = $config->configuracion->mailer->from;
$fromName     = $config->configuracion->mailer->fromName;
$useMailchimp = $config->configuracion->mailchimp;
$fecha        = date('Y-m-d H:i:s');
$success      = 0;
$a            = (isset($_REQUEST['a'])) ? $_REQUEST['a'] : "";

$mailchimp_respuesta = "";
$subject_mailchimp   = "";

if ( isset($_REQUEST['form']) ) {

	$form = $config->forms->{$_REQUEST['form']};

	if ( $useMailchimp->use == 1 && $form->mailchimpList != "" ){

		$mailchimpApi = $useMailchimp->api;
		// include('Mailchimp.php');
		$MailChimp = new MailChimp($mailchimpApi);
		$mailchimpList = $form->mailchimpList;
		$mailchimpData = new stdClass;

	}

	if ( $a == 1 ){
		
		$subject = $form->titleMail;
		$body = "<strong>Datos del solicitante:</strong><br>";
		foreach ($form->fields as $key => $field) {
			if ( $field->attribs->type != "submit" ) {
				$body .= '<strong>'.ucfirst($field->name).'</strong>: ' . $_REQUEST[$field->name] . "<br>";
			}
			if ( $field->name == "email" ) {
				$email = $_REQUEST["email"];
			}
			if ( $field->name == "nombre" ) {
				$nombre = $_REQUEST["nombre"];
			}

			if ( $useMailchimp->use == 1 && $field->mailchimpField != "" ){
				$mailchimpData->{$field->mailchimpField} = $_REQUEST[$field->name];
			}

		}
		$body .= "<strong>Fecha de envío:</strong> $fecha";
		
		$texto_respuesta = $form->successMail;
		$success = 1;


	}
	

	if ( $success == 1 && filter_var($email, FILTER_VALIDATE_EMAIL) ){

		$mail = new PHPMailer(true);

		/**
		 * Completa los datos si se requiere autenticación
		 */
		if ( $auth == 1 ):

			$mail->isSMTP();
			$mail->SMTPDebug   = 0;
			$mail->Debugoutput = 'html';
			$mail->Host        = $config->configuracion->mailer->host;
			$mail->Port        = $config->configuracion->mailer->port;
			$mail->SMTPSecure  = $config->configuracion->mailer->smtpSecure;
			$mail->SMTPAuth    = true;
			$mail->Username    = $config->configuracion->mailer->userName;
			$mail->Password    = $config->configuracion->mailer->password;

		endif;

		$mail->CharSet = 'UTF-8';


		$mail->addReplyTo($email,$nombre);
		$mail->setFrom($from, $fromName, 0);

		$mailTo = explode(",",$to);
		foreach ($mailTo as $mto) {
			$mail->addAddress($mto);
		}

		if ( $cc ) { 
			$mailCC = explode(",",$cc); 
			foreach ($mailCC as $mcc) {
				$mail->addCC($mcc);
			}
		}
		if ( $bcc ) { 
			$mailBCC = explode(",",$bcc); 
			foreach ($mailBCC as $mbcc) {
				$mail->addBCC($mbcc);
			}
		}
		
		$mail->Subject = $subject;
		$mail->CharSet = 'UTF-8';
		$mail->msgHTML($body);

		if (!$mail->Send()) {

			$responder = 2;
			$texto_respuesta = "Ha ocurrido un error en el envio\nError code: 2\n".$mail->ErrorInfo;

		} else {

			$responder = 1;

		}


		if ( $useMailchimp->use == 1 && $mailchimpList != "" ){

			$result = $MailChimp->post('lists/'.$mailchimpList.'/members', [
				'email_address' => $email,
				'merge_fields'  => $mailchimpData,
				'status'        => 'subscribed',
			]);
						
			if ($result["status"]=="error"){
				$mailchimp_respuesta = "Error " . $result["code"] . " " . $result["name"] . " " . $result["error"];
				$subject_mailchimp 	 = "Error en envio a MailChimp";

			}else{
				$mailchimp_respuesta = "Ok";
				$subject_mailchimp = "Envio a MailChimp";

			}

		}


	}else{

		$responder = 3;
		$texto_respuesta = "Ha ocurrido un error en el envio. - " . $mail;

	}

} else {

	$responder = 4;
	$texto_respuesta = "Ha ocurrido un error en el envio. - ";

}	

//RESPUESTA DE CONSULTA
$resp = array(
	'respuesta' => $responder,
	'texto_respuesta' => $texto_respuesta,
	'mailchimp_respuesta' => $mailchimp_respuesta,
	'subject_mailchimp' => $subject_mailchimp
);

//	header('Content-Type: application/json');
echo json_encode($resp);