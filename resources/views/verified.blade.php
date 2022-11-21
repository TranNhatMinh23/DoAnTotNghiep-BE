<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Verify registration</title>
  </head>
  <body style="margin: 20px 0; padding: 0;">
    <table align="center" style="border: 1px solid #cccccc;" cellspacing="0"
      width="70%"
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
              <td style="text-align: center; text-transform: capitalize; color: #228B22; padding: 10px 0 0 0; font-size: 20px">
                @if ($success)
                  CONGRATS!
                @endif
              </td>
            </tr>
            <tr>
              <td style="text-align: center; color: #3c80c1; font-size: 18px; padding: 0 0 20px 0" >
                <div>
                  @if($success)
                    <p>{{$message}}</p>
                    <button style="background: #3CB371; padding: 7px 10px; border: none; border-radius: 5px; ">
                      <a style="text-decoration: none; color: #fff" href="http://localhost:9000/login">Login Now</a>
                    </button> 
                  @else
                    <p style="color: #FF0000;">{{$message}}</p>
                    <button style="background: #3CB371; padding: 7px 10px; border: none; border-radius: 5px; ">
                      <a style="text-decoration: none; color: #fff" href="http://localhost:9000/signup">Back to Signup</a>
                    </button>
                  @endif
                </div>
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