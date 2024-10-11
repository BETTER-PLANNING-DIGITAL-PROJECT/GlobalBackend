<?php
namespace App\Service;

class SmsService
{
    public function opt($phone, $opt): string
    {
        //Your authentication key
        $authKey = "YourAuthKey";
        $userName = 'global.wallet@betterplanning.net';
        $pass = 'GlobalWallet';

        //Multiple mobiles numbers separated by comma
        $mobileNumber = $phone;

        //Sender ID,While using route4 sender id should be 6 characters long.
        $senderId = "GLOBAL School";

        //Your message to send, Add URL encoding here.
        //$message = urlencode("Welcome to travel of Test API");
        $message = 'Your validation code is: '.$opt;

        //Define route
        $route = "default";
        //Prepare you post parameters
        $postData = array(
            //'authkey' => $authKey,
            //'route' => $route,
            'mobiles' => $mobileNumber,
            'sms' => $message,
            'senderid' => $senderId,
            'user' => $userName,
            'password' => $pass
        );

        //API URL
        $url="https://smsvas.com/bulk/public/index.php/api/v1/sendsms";

        // init the resource
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData
            //,CURLOPT_FOLLOWLOCATION => true
        ));


        //Ignore SSL certificate verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        //get response
        $output = curl_exec($ch);

        //Print error if any
        if(curl_errno($ch))
        {
            echo 'error:' . curl_error($ch);
        }

        return $output;

    }

    public function reset($phone, $opt): string
    {
        //Your authentication key
        $authKey = "YourAuthKey";
        $userName = 'global.wallet@betterplanning.net';
        $pass = 'GlobalWallet';

        //Multiple mobiles numbers separated by comma
        $mobileNumber = $phone;

        //Sender ID,While using route4 sender id should be 6 characters long.
        $senderId = "GLOBAL SCHOOL";

        //Your message to send, Add URL encoding here.
        //$message = urlencode("Welcome to travel of Test API");

        //$message = 'Your new password is: '.$opt;
        $message = 'Your One Time Password is: '.$opt;

        //Define route
        $route = "default";
        //Prepare you post parameters
        $postData = array(
            //'authkey' => $authKey,
            //'route' => $route,
            'mobiles' => $mobileNumber,
            'sms' => $message,
            'senderid' => $senderId,
            'user' => $userName,
            'password' => $pass
        );

        //API URL
        $url="https://smsvas.com/bulk/public/index.php/api/v1/sendsms";

        // init the resource
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData
            //,CURLOPT_FOLLOWLOCATION => true
        ));


        //Ignore SSL certificate verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        //get response
        $output = curl_exec($ch);

        //Print error if any
        if(curl_errno($ch))
        {
            echo 'error:' . curl_error($ch);
        }

        return $output;

    }
}
