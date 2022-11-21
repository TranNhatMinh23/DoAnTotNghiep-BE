<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Result of exam</title>
  </head>
  <body style="margin: 20px 0; padding: 0;">
    <table align="center" style="border: 1px solid #cccccc;" cellspacing="0"
      width="700"
      style="border-collapse: collapse;">
      <tr>
        <td align="center">
          <img
            src="https://s3-ap-southeast-1.amazonaws.com/trung.my.toeic/Emails+image/banner_10.png"  
            alt="Creating Email Magic" width="100%"
            height="180" style="display: block;" />
        </td>
      </tr>
      <tr>
        <td bgcolor="#ffffff" style="padding: 20px 15px;">
          <table cellspacing="0" width="100%">
            <tr>
              <td style="text-align: center; color: #500050; padding: 10px; font-size: 24px">
                CONGRATS! YOU FINISHED THE
              </td>
            </tr>
            <tr>
              <td style="text-align: center; color: #3c80c1; font-size: 20px; padding: 0 0 20px 0" >
                {{ $exam->name }}
              </td>
            </tr>
            <tr>
              <td style="font-size: 18px;">
                <p>Dear <span style="text-transform:capitalize; font-weight: 600">{{$user->name}}</span>,</p> 
                <p>You has achieved the score of <strong style="color: #500050; padding: 0 4px;"> {{$total_score}} </strong> in the <span style="text-transform: uppercase;">{{ $exam->name }}</span> at {{ $date }}.</p>
                <p>Here are details of your points:</p>
              </td>
            </tr>
            <tr>
              <td style="padding: 10px 0 10px 0;">
                <table style="border: 2px solid #3c80c1; border-radius: 5px;" width="100%">
                  <tr>
                    <td style="font-size: 17px; text-align: center; border-right: 2px solid #3c80c1; padding: 10px;">
                      <div style="text-align: center;">
                        <img style="width: 20px;" src="https://s3-ap-southeast-1.amazonaws.com/trung.my.toeic/Emails+image/user.png" alt="user" />
                        <p style="margin-top: 5px;">{{$user->name}}</p>
                      </div>
                      <div style="text-align: center">
                        <img style="width: 20px;" src="https://s3-ap-southeast-1.amazonaws.com/trung.my.toeic/Emails+image/mail-icon.png" alt="mail" />
                        <p style="margin-top: 5px;">{{$user->email}}</p>
                      </div>
                      <div style="text-align: center">
                        <img style="width: 20px;" src="https://s3-ap-southeast-1.amazonaws.com/trung.my.toeic/Emails+image/calendar.png" alt="calendar" />
                        <p style="margin-top: 5px;">{{$date}}</p>
                      </div>
                    </td>
                    <td style="border-right: 2px solid #3c80c1; padding: 10px;">
                      <div style="text-align: center">
                        <p style="font-weight: 600; margin-top: 0;">LISTENING</p>  
                        <div style="width: 80px;height: 80px;margin: 0 auto;vertical-align: middle;text-align: center;border: 1px solid #3c80c1;border-radius: 50%;line-height: 80px; font-weight: 600;color: #3c80c1;font-size: 20px;">
                          {{$listening_score}}
                        </div>
                      </div>
                      <div style="text-align: center">
                        <p style="font-weight: 600;">READING</p>  
                        <div style="width: 80px;height: 80px;margin: 0 auto;vertical-align: middle;text-align: center;border: 1px solid #3c80c1;border-radius: 50%;line-height: 80px; font-weight: 600;color: #3c80c1;font-size: 20px;">
                          {{$reading_score}}
                        </div>
                      </div>
                    </td>
                    <td style="padding: 10px;">
                      <div style="text-align: center">
                        <p style="font-weight: 600; margin-top: -20px;">TOTAL</p>  
                        <div style="width: 80px;height: 80px;margin: 0 auto;vertical-align: middle;text-align: center;border: 1px solid #3c80c1;border-radius: 50%;line-height: 80px; font-weight: 600;color: #3c80c1;font-size: 20px;">
                          {{$total_score}}
                        </div>
                      </div>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td style="font-size: 18px;">
                <p>Have a nice day and be full of energy.</p>
                <p>Regards,</p> 
                <p>English Center</p>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td bgcolor="#0275C7" style="padding: 10px;">
          <table width="100%">
            <tr>
              <td width="75%" style="color: #fff">
                &reg; English Center, Copy-right 2019<br/>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </body>
  </html>