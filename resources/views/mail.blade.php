<h1>Hi, {{ $name }}</h1>
<h3>Thanks for your registration with knowledgedepot.ca</h3>
<h3>To verify your email address, please click button below</h3>
<br/><br/>
<a href="{{ $projectURL }}verifyemailaddress/{{ $token }}" style="cursor: pointer;"><button style="  background-color: #4CAF50; /* Green */
  border: none;
  color: white;
  padding: 15px 32px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  cursor: pointer;
  font-size: 16px;">Click here to verify email</button></a>
<br/><br/><br/>
<p>Thanks, <br />The knowledgedepot Team</p>
<br/><br/><br/>
<p style="font-size: 12px;">If you having trouble with the button above, just copy and paste URL below into your web browser
<p style="font-size: 12px;">{{ $projectURL }}verifyemailaddress/{{ $token }}</p>