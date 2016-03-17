<?php

namespace Stentle\Webcore\Business;
use DrewM\MailChimp\MailChimp;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class User
{

  static public function subscribeNewsletter($email){
      //docs:https://github.com/drewm/mailchimp-api/tree/api-v3
      $MailChimp = new MailChimp(Config::get('services.mailchimp.key'));
      if(App::getLocale()=='it'){
          $id=Config::get('services.mailchimp.list_footer_it');
      }else{
          $id=Config::get('services.mailchimp.list_footer_en');
      }
      return $MailChimp->post('lists/'.$id.'/members', array(
          'email_address'     => $email,
          'status'=>'subscribed'
      ));
  }
}