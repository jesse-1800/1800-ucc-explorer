<?php namespace App\Models;

use Kernel\Mail;

class Notify
{

    /**
     * Send email notification to the partner.
     *
     * @param int $partner_id
     * @param object $data
     * @return bool
     */
    public static function email($partner_id, $data = array())
    {
        $partner_smtp = Partners::smtp_settings($partner_id);
        $mail = new Mail((object)[
            'username' => $partner_smtp->smtp_username,
            'password' => $partner_smtp->smtp_password,
            'hostname' => $partner_smtp->smtp_host,
            'port'     => $partner_smtp->smtp_port,
        ]);
        $mail->from("no-reply@1800officesolutions.com");
        $mail->to($data->recipient);
        $mail->subject($data->subject);
        $mail->body("
            $data->content
            <br><br>
            Best regards,<br>
            Administrator
        ");
        return $mail->send();
    }
}