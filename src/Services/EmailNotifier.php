<?php

namespace App\Services;

class EmailNotifier
{
    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    private function sendEmail($recipient, $subject, $body)
	{
        $message = (new \Swift_Message($subject))
            ->setTo($recipient)
            ->setBody($body, 'text/plain');
        $this->mailer->send($message);
	}
	
	public function sendAccountVerificationEmail($auth)
	{
		$link = getenv('DASHBOARD_ROOT_URL') . '/verify-account?token=' . $auth->getToken();
		$body = "Hello " . $auth->getUser()->getUsername() . ",\n\nTo verify your account, follow this link: " . $link
				. "\n\nIf you did not initiate this action, disregard this message.";
        $this->sendEmail($auth->getUser()->getEmail(), 'Verify your Geometry Dash account', $body);
	}
	
	public function sendPasswordResetEmail($auth)
	{
		$link = getenv('DASHBOARD_ROOT_URL') . '/recover-password?token=' . $auth->getToken();
        $body = "Hello,\n\nYour username is: " . $auth->getUser()->getUsername() . ".\nTo reset your password, follow this link: " . $link
				. "\n\nIf you did not initiate this action, disregard this message.";
        $this->sendEmail($auth->getUser()->getEmail(), 'Reset the password of your Geometry Dash account', $body);
	}
}