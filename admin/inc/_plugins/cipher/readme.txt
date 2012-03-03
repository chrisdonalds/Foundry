CIPHER:LIB PLUG-IN
Web Template 3.0
========================================

-- Inclusion --
$incl = 'cipher';

-- Implementation --
$text2encrypt = htmlspecialchars($txt);                 //Text you want encrypted - this is snatched from the ?txt= parameter after the url for demo purposes.  But it is a very good idea in general to check for special characters regardless.
$privateKey = genPrivateKey();                          //Generate a private key - This will made into a static value after generating once in practice.
$ciphertext = encrypt($text2encrypt, $privateKey);      //Catches the ciphertext from the encrypt function.
$plaintext = decrypt($ciphertext, $privateKey);         //Catches the plaintext from the decrypt function.
$cipherParts = explode(":",$ciphertext);                //Only for the purpose of this example to show you the various parts more clearly. (see below)

echo("Origional Text: \"".$text2encrypt."\"");
echo("Private Key: ".$privateKey);
echo("Public Key: ".$cipherParts[1]);
echo("Cipher Text: ".$cipherParts[0]);
echo("Decrypted Text: \"".$plaintext."\"");