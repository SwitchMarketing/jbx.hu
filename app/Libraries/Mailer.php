<?php namespace App\Libraries;

use Exception;

class Mailer {
 
        
    /**
     * toEmail
     *
     * @var string
     */
    protected static $toEmail;

        
    /**
     * toName
     *
     * @var string
     */
    protected static $toName;


    /**
     * 
     * a kapcsolat email
     * 
     * @param array|null $data
     * 
     * @return bool
     */
    public static function contact(?array $data = null)
    {

        if(is_array($data))
        {

            //send message
            $email = \Config\Services::email();

            $fromEmail = config( 'Config\\AppConfig' )->siteEmail;
            $fromName = config( 'Config\\AppConfig' )->siteName;
            $toEmail = config( 'Config\\AppConfig' )->leadEmail;

            $email->setFrom($fromEmail, $fromName);
            $email->setTo($toEmail);

            if( isset($data['email']) )
                $email->setReplyTo($data['email']);
            // $email->setBCC('durugya@gmail.com');
            $email->setSubject('Ajánlatkérés: '. $data['name']);
            
            helper('html');
            $msg = view('email/contact', $data);
            
            $email->setMessage($msg);

            // csatolmányok
            if( isset($data['files']) && count($data['files']) )
            {
                foreach ($data['files'] as $file) {
                    $email->attach(WRITEPATH . 'uploads/' . $file['filename']);
                }
            }

            if( !$email->send(false) )
            {   
                log_message('debug', $email->printDebugger(['headers']));
                throw new Exception('Hiba az email kiküldésekor!');
            }
                
            return true;
        } 
	
        return false;
    }

    /**
     * 
     * új rendelés email
     * 
     * @param array|null $data
     * @param string|order $tpl
     * 
     * @return bool
     */
    public static function order(array $data, string $tpl = 'order'):bool
    {

        if(is_array($data))
        {

            //send message
            $email = \Config\Services::email();

            $fromEmail = config( 'Config\\AppConfig' )->siteEmail;
            $fromName = config( 'Config\\AppConfig' )->siteName;
            $toEmail = config( 'Config\\AppConfig' )->leadEmail;

            $email->setFrom($fromEmail, $fromName);
            $email->setTo($toEmail);

            if( isset($data['email']) )
                $email->setReplyTo($data['email']);
            // $email->setBCC('
            $email->setSubject('Új rendelés: '. $data['name']);

            helper(['html', 'utils']);
            $msg = view('email/' . $tpl, $data);
            $email->setMessage($msg);
            if(!$email->send(false))
                throw new Exception('Hiba az email kiküldésekor!'); //$email->printDebugger(['headers'])
            return true;
        }
        return false;
    }

    /**
     * 
     * köszönő email küldése
     * 
     * @param array|null $data
     * @param string|thankyou $tpl
     * 
     * @return bool
     */
    public static function thankYou(?array $data = null, string $tpl = 'thankyou'):bool
    {

        if(is_array($data))
        {

            //send message
            $email = \Config\Services::email();

            if(isset($data['email']))
            {
                $toEmail = $data['email'];
                $toName  = $data['name'] ?? $data['email'];
                $email->setTo($toEmail, $toName);
            }
            else
                return false;

            $fromEmail = config( 'Config\\AppConfig' )->siteEmail;
            $fromName = config( 'Config\\AppConfig' )->siteName;
            $email->setFrom($fromEmail, $fromName);

            $email->setSubject('Sikeres ajánlatkérés');
            
            helper('html');

            $msg = view('email/' . $tpl, $data);
            
            $email->setMessage($msg);

            if(!$email->send(false))
                throw new Exception('Hiba az email kiküldésekor!'); //$email->printDebugger(['headers'])

            return true;
        } 
	
        return false;
    }


} 