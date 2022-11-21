<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Verify registration</title>
</head>
<body>
  <div>
    Hi {{ $company_name }},
    <br/> 
    Thank you for creating an company account with us. Don't forget to complete your registration!
    <br/><br/>
    Here is infomation of your account: <br/>
    Company name: {{$company_name}} <br/>
    Address: {{$address}}<br/>
    Phone: {{$phone}}<br/> <br/>
    Here is manager's account login:<br/>
    Name of Manager: {{$name}}<br/>
    Email: {{$email}} <br/>
    Password: {{$password}}
    <br/>  <br/>
    Please click on the link below or copy it into the address bar of your browser to confirm your registration address:
    <br/> 
    <a href="{{ url('api/verify', $verification_code)}}">Confirm email address </a> 
    <br/>
    <p>Best Regards,</p>
    English Center
</div>
</body>
</html> 