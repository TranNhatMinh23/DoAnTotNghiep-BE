<?php

namespace App\Http\Controllers\SendEmail;
 
use App\Http\Controllers\Controller;
use Mail; 

class EmailController extends Controller
{
    public static function sendEmailResult($data, $user)
    {
        $examName = $data['exam']->name;
        $data['title'] = "Score of ".$examName;
        $data['user'] = $user;

        $mailTo = $user->email;

        Mail::send('resultScore', $data, function ($message) use ($mailTo, $examName) {
            $message->to($mailTo); 
            // $message->cc('14081997hs@gmail.com', 'Admin');
            $message->subject('Result of '.$examName);
        });

        if (Mail::failures()) {
            return response()->json('Sorry! Please try again latter', 400);
        } else {
            return response()->json('Great! Successfully send in your mail', 200);
        }
    }

    public static function sendEmailVerifyEmail($data) { 
        $email = $data['email'];
        $name = $data['name'];
        $subject = "Please verify your email address.";
        Mail::send('verifyEmail', $data, function ($message) use ($subject, $email, $name) { 
            $message->to($email, $name); 
            $message->subject($subject); 
        });

        if (Mail::failures()) {
            return response()->json('Sorry! Please try again latter', 400);
        } else {
            return response()->json('Great! Successfully send in your mail', 200);
        }
    }

    public static function sendEmailAdminVerifyEmail($data) {  
        $subject = "Confirm new user registration";
        Mail::send('adminVerifyEmail', $data, function ($message) use ($subject) { 
            $message->to('tranhuutrung1408@gmail.com', 'Admin'); 
            $message->subject($subject); 
        });

        if (Mail::failures()) {
            return response()->json('Sorry! Please try again latter', 400);
        } else {
            return response()->json('Great! Successfully send in your mail', 200);
        }
    } 

    public static function sendEmailVerifyCompanyRegister($data) { 
        $email = $data['email'];
        $company_name = $data['company_name'];
        $subject = "Please verify new company registration.";
        Mail::send('verifyCompanyRegister', $data, function ($message) use ($subject, $email, $company_name) { 
            $message->to($email, $company_name); 
            $message->subject($subject); 
        });

        if (Mail::failures()) {
            return response()->json('Sorry! Please try again latter', 400);
        } else {
            return response()->json('Great! Successfully send in your mail', 200);
        }
    } 

    public static function sendEmailAdminVerifyCompanyRegister($data) { 
        $email = $data['email'];
        $company_name = $data['company_name'];
        $subject = "New company registration";
        Mail::send('adminVerifyCompanyRegister', $data, function ($message) use ($subject, $email, $company_name) { 
            $message->to('tranhuutrung1408@gmail.com', 'Admin'); 
            $message->subject($subject); 
        });

        if (Mail::failures()) {
            return response()->json('Sorry! Please try again latter', 400);
        } else {
            return response()->json('Great! Successfully send in your mail', 200);
        }
    } 
}
