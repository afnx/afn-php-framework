<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * @author AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Core;

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

//Load Composer's autoloader
require LIB_DIR . '/vendor/autoload.php';

/**
 * Class Mailer
 *
 * @package AFN-PHP-FRAMEWORK
 */
class Mailer extends PHPMailer
{
    /**
     * Stores recipient email address and recipient names
     * @var array
     */
    private $recipientList = array();

    /**
     * Stores email address which sends mail
     * @var string
     */
    private $from;

    /**
     * Stores name which sends mail
     * @var string
     */
    private $fromName;

    /**
     * Stores email subject
     * @var string
     */
    private $subject;

    /**
     * Stores email body(content)
     */
    private $body;

    /**
     * Stores attachments
     * @var array
     */
    private $attachmentList = array();

    /**
     * Stores CC email addresses
     * @var array
     */
    private $ccList = array();

    /**
     * Stores BCC email addresses
     * @var array
     */
    private $bccList = array();

    /**
     * Stores plain body for non-HTML clients
     * @var string
     */
    private $altBody;

    /**
     * Stores mail server credentials
     * @var object
     */
    private $server;

    /**
     * Prepares requirements to send a mail
     * @param string $mailServer
     */
    public function __construct($mailServer = "default", $from = "", $fromName = "", $subject = "", $body = "", $altBody = "")
    {
        // Assign default name to from
        $this->fromName = empty($fromName) ? $GLOBALS["settings"]["name"] : $fromName;

        $this->from = $from;
        $this->subject = $subject;
        $this->body = $body;
        $this->altBody = $altBody;

        // Check if ROOT_DIR is defined then use it or add it manually for the config file path
        if (!defined(ROOT_DIR)) {
            $db_dir = realpath(__DIR__ . '/../..') . '/config/mail.php';
        } else {
            $db_dir = ROOT_DIR . '/config/mail.php';
        }

        // Include the mail config file
        $mail = require $db_dir;

        // Define mail
        $this->server = $mail[$mailServer];
    }

    /**
     * Executes sending mail process
     */
    public function send()
    {

        $mail = new PHPMailer(true); // Passing `true` enables exceptions
        try {
            //Server settings
            $mail->SMTPDebug = 2; // Enable verbose debug output
            $mail->isSMTP(); // Set mailer to use SMTP
            $mail->Host = $this->server["host"]; // Specify main and backup SMTP servers
            $mail->SMTPAuth = true; // Enable SMTP authentication
            $mail->Username = $this->server["username"]; // SMTP username
            $mail->Password = $this->server["password"]; // SMTP password
            $mail->SMTPSecure = 'ssl'; // Enable SSL encryption, `tls` also accepted
            $mail->Port = $this->server["port"]; // TCP port to connect to

            //Recipients
            $mail->setFrom($this->from, $this->fromName);
            foreach ($this->recipientList as $email => $name) {
                if (!empty($name)) {
                    $mail->addAddress($email, $name); // Add a recipient
                } else {
                    $mail->addAddress($email); // Name is optional
                }
            }
            $mail->addReplyTo($this->from, $this->fromName);
            if ($this->ccList) {
                foreach ($this->ccList as $address) {
                    $mail->addCC($address);
                }
            }
            if ($this->bccList) {
                foreach ($this->bccList as $address) {
                    $mail->addBCC($address);
                }
            }

            //Attachments
            foreach ($this->attachmentList as $attachment => $name) {
                if (!empty($name)) {
                    $mail->addAttachment($attachment, $name); // Add attachments
                } else {
                    $mail->addAttachment($attachment); // Optional name
                }
            }

            //Content
            $mail->CharSet = "utf-8";
            $mail->Encoding = "base64";
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $this->subject;
            $mail->Body = $this->body;
            $mail->AltBody = $this->altBody;

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        }
    }

    /**
     * Adds new email adddress
     * @param string $email
     * @param string $name
     */
    public function addNewAddress($email, $name = "")
    {
        $this->recipientList[$email] = $name;
    }

    /**
     * Adds new attachment
     * @param string $attachment
     * @param string $name
     */
    public function addNewAttachment($attachment, $name = "")
    {
        $this->attachmentList[$attachment] = $name;
    }

    /**
     * Adds cc email adddress
     * @param string $email
     */
    public function addNewCC($email)
    {
        $this->ccList[] = $email;
    }

    /**
     * Adds bcc email adddress
     * @param string $email
     */
    public function addNewBcc($email)
    {
        $this->bccList[] = $email;
    }

}
