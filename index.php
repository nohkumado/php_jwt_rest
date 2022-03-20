<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>PHP Rest API Test</title>
    </head>
<body>
<h1>Test of Rest API</h1>

<?php
var_dump($_REQUEST);
$jwt  ="";

if(strlen($jwt)<= 0)
{
  ?>
    <form action="api.php" method="post">
    <input type="hidden" name="rquest" value="login"/>
    <label for"login">Login</label><input type="text" name="login"/>
    <label for"passwd">Pasword</label><input type="password" name="passwd"/>
    <input type="submit" value="Rechercher" />
    </form>

    <?php
}
else print("seems a valid jwt: '$jwt' of len ".strlen($jwt)."<br\>\n");



?>
</body>
</html>
