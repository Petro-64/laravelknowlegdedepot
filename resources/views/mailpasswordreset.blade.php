<h1>Hi, {{ $name }}</h1>
<h3>You recently requested to reset password for your knowledgedepot.ca account</h3>
<p>Click the button below to reset it. Password reset link valid only for the next {{ $expirationTimeInHours }} hours</p>
<br/><br/>
<a href="{{ $projectURL }}passwordreset/{{ $token }}" style="cursor: pointer;"><button style="  background-color: #4CAF50; /* Green */
  border: none;
  color: white;
  padding: 15px 32px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  cursor: pointer;
  font-size: 16px;">Click here to reset your password</button></a>
<br/><br/><br/>

<p>If you did not requests password reset, please ignore this email</p>
<p>Thanks, <br />The knowledgedepot Team</p>
<br/><br/><br/>
<p style="font-size: 12px;">If you having trouble with the button above, just copy and paste URL below into your web browser
<p style="font-size: 12px;">{{ $projectURL }}passwordreset/{{ $token }}</p>