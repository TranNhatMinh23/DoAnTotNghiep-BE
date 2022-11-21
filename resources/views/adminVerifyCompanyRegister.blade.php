<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Verify new company registration</title>
</head>
<body>
  <div>
    Hi Admin,
    <br/> 
    There are new company registered in the system. Here is the information:
    <br/><br/> 
    Company name: {{$company_name}} <br/>
    Address: {{$address}}<br/>
    Phone: {{$phone}}<br/> <br/>
    Manager's account:<br/>
    Name of Manager: {{$name}}<br/>
    Email: {{$email}} <br/> 
    <br/>  
    Please click on the link below or copy it into the address bar of your browser to confirm new registration:
    <br/> 
    <a href="{{ url('api/verify', $verification_code)}}">Confirm email address </a> 
    <br/>
    <p>Best Regards,</p>
    English Center
</div>
</body>
</html> 