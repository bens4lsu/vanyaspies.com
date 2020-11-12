<?php

require_once('PHPMailer-master/PHPMailerAutoload.php');

const EMAILHOST = 'tls://mail.concordbusinessservicesllc.com:587';
const EMAILUSER = 'no-reply@vanyaspies.com';
const EMAILPASSWORD = 'Ht2s34VM8fHt2s34VM8f';
const EMAILTO = ['vanya@vanyaspies.com'];      // can have multiple, comma seperated values here
const EMAILSUBJECT = 'order from vanyaspies.com';
const URLORDERSUCCESS = 'https://vanyaspies.com/thank-you.html';
const URLORDERERROR = 'https://vanyaspies.com/order-error.html';

function sendAnEmail($to, $subject, $message){
    // send mail
    $mail = new PHPMailer;
    $mail->isSMTP();  
    $mail->Host = EMAILHOST;  
    $mail->SMTPAuth = true; 
    $mail->Username = EMAILUSER; 
    $mail->Password = EMAILPASSWORD; 
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ));
    //$mail->SMTPDebug = 2;
    $mail->From = EMAILUSER; // The FROM field, the address sending the email 
    $mail->FromName = EMAILUSER; // The NAME field which will be displayed on arrival by the email client
    foreach ($to as $address) {
        $mail->addAddress($address);     // Recipient's email address and optionally a name to identify him
    }
    $mail->isHTML(true);   // Set email to be sent as HTML, if you are planning on sending plain text email just set it to false

    // The following is self explanatory
    $mail->Subject = $subject;
    $mail->Body    = $message;

    /*if(!$mail->send()) {  
        echo "Message hasn't been sent.  ";
        echo 'Mailer Error: ' . $mail->ErrorInfo . "\n";
        return false;
    } else {
        echo "Message has been sent :) \n";
        return true;
    }*/
    
    return $mail->send();
}

function processResponse() {

    //print_r($_POST);
    //Array ( [fname] => xx [lname] => xx [email] => xx@co.co [phone] => (225) 590-7785 [bagSize] => peydon [qty] => 1 [date] => [pickup-select-opt] => 12to2 [message] => [recaptcha-token] => 03AGdBq27VhuCu0Zi-dGfcxbgXjxKcS8gKAIyXWRUOUdfeIoB3ASKUXXPHTmNjzqvJrsy4uLsp0cqg1FORtdjKUuD_NaOPmyJG6al4BtCJf4A44xQafUcaImz09rP_pjzjeOhMPrBK4sl7SPT6E7r07Gaa147qX3Tfl0BBiXurTx86djNeUeCEZmavUmSK_OWg10lWztbCEy9a3CBrcIxf4V9qzdMjvEoeJxrBcOu3jNYw7zhRUnPYUc-POmd6i_Wp8H73jUVmEItMkcBzDfW_B8ComCMUzO1po3X6e-1LOgpZlXAuB8gu2dCjtfveNCY7UZDDB2-JJ8PY7Cgm_yI6kWbhraeB59S0TXtNM5jE_MRgUFbQYIWvdoollB8jWeSf3T4n4I05kHMQPFLAGz0De1cR4p0hH5mSB7wZ_zafu_X_THfStm0B1wB5IJI3STOOUEXCdn3wmxC7 )

    $fname = isset($_POST['fname']) ? $_POST['fname'] : "";
    $lname = isset($_POST['lname']) ? $_POST['lname'] : "";
    $email = isset($_POST['email']) ? $_POST['email'] : false;
    $phone = isset($_POST['phone']) ? $_POST['phone'] : false;
    $message = isset($_POST['message']) ? $_POST['message'] : "";
    $qty = isset($_POST['qty']) ? $_POST['qty'] : "";
    $bagSize = isset($_POST['bagSize']) ? $_POST['bagSize'] : "";
    
    if (isset($_POST['date']) && trim($_POST['date']) != "") {
        $date = $_POST['date'];
    }
    else if (isset($_POST['pickup-select-opt'])) {
        $date = $_POST['pickup-select-opt'];
    }
    else {
        $date = "";
    }

    if ($email && $phone){
        $emailText = "<p>First Name:  $fname</p><p>Last Name:  $lname</p><p>Email Address:  $email</p><p>Phone:  $phone</p><p>Quantity:  $qty</p><p>Bag Size:  $bagSize</p><p>Date Requested:  $date</p><p>Message:  $message</p>";
        if (sendAnEmail(EMAILTO, EMAILSUBJECT, $emailText)) {
        
            // confirmation email to customer
            $message = "<p>I have received your order for $qty $bagSize meat pies, to be ready for you by $date.  I will be in touch if I have any questions, or to schedule pickup.</p><p>Thanks for your business!</p><p>Vanya</p>";
            sendAnEmail([$email], 'Vanya\'s Pies Order Received', $message);
        
            // redirect to thank-you page
            header("Location: ".URLORDERSUCCESS);
        }
        else {
            header("Location: ".URLORDERERROR);
        }
    }
}

$verify_url = 'https://www.google.com/recaptcha/api/siteverify';
$args = array('secret' => '6Lc46MYUAAAAAHWDS37_Z3EnXMeauT9tE_KypWQV',
              'response' => $_POST['recaptcha-token'],
              'remoteip' => $_SERVER['REMOTE_ADDR']);
$request_url = $verify_url.'?'.http_build_query($args);
 
// a JSON object is returned
$response = file_get_contents($request_url);
 
// decode the information
$captchaResult = json_decode($response, true); // true decodes it to an array instead of a PHP object


// handle the response
if($captchaResult['success'] == 1 && $captchaResult['score'] > 0.6) {
	processResponse();
} else {
	print "Error:  this system suspects that the form was completed by a bot.";

}

?>