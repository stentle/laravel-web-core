<?php

namespace Stentle\LaravelWebcore\Business;
use DrewM\MailChimp\MailChimp;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class User
{


    static public function subscribeNewsletter($email, $list_id, $fields=null)
    {
        //docs:https://github.com/drewm/mailchimp-api/tree/api-v3
        $MailChimp = new MailChimp(Config::get('services.mailchimp.key'));

        if (empty($fields) || !is_array($fields)) {
            return $MailChimp->post('lists/' . $list_id . '/members', array(
                'email_address' => $email,
                'status' => 'subscribed'
            ));
        } else {
            return $MailChimp->post('lists/' . $list_id . '/members', array(
                'email_address' => $email,
                'merge_fields' => $fields,
                'status' => 'subscribed'
            ));
        }
    }

}