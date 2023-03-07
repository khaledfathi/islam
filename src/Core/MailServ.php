<?php
namespace Khaled\App\Core; 
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


//Create an instance; passing `true` enables exceptions

class MailServ{
    public static function SendEmail(string $toEmail ,string  $subject ,string  $message ,string  $altMessage=""): void 
    {
        try{
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = getConfig('MAIL')->HOST ;             //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = getConfig('MAIL')->USER;              //SMTP username
        $mail->Password   =  getConfig('MAIL')->PASSWORD;             //SMTP password
        $mail->SMTPSecure = getConfig('MAIL')->SMTPSECURE;            //Enable implicit TLS encryption
        $mail->Port       = getConfig('MAIL')->PORT;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom( getConfig('MAIL')->SENDERMAIL, getConfig('MAIL')->SENDER )  ;
        $mail->addAddress( $toEmail );     //Add a recipient

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = $altMessage;
        $mail->send();
        }catch(Exception $e){
            echo "error : ". $mail->ErrorInfo; 
        }
    }
}