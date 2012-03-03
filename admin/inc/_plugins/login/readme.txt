LOGIN PLUG-IN
Web Template 3.0
Chris Donalds <chrisd@navigatormm.com>
========================================

-- Inclusions --
$incl = "login";

-- Preparation --
> authenticate users on log-in page where the POST/GET submissions are checked
> check for active login on all interior pages
> username and password form fields must be named "username" and "password" only

-- Authenticating --

var (string) = authenticate_login($table (string), $section (string), $activefld (string), $successpage (string), checkotherpc (bool), timeout (int));

 - returns: a string containing the error on fail or empty on success
 - parameters:  $table (string) - name of database table containing user data
                $section (string) - part of site where login is processed
                $activefld (string) - name of the activation status column
                $successpage (string) - url of page to direct successful login entry
                $checkotherpc (bool) - set to true to check if username has been logged in from a different pc
                $timeout (int) - time to keep the session live

-- Revalidating --

var (bool) is_loggedin($username (string optional)));

 - returns: 1 (true) if logged in
            0 (false) if not logged in
            2 if someone else has logged in
 - parameters: none

-- Log In --

var (int) = login($username (string), $section (string));

- returns: 1 (true) if successful
- parameters:   $username - username of account
                $section - part of site where login is processed

-- Log Out --

(void) logout();

-- Get Username --

var (string) = get_db_login_username();

 - returns: string with username or empty string if not logged in
 - parameters: none

-- Get Login ID --

var (int) = get_loginid();

 - returns: integer with loginid or 0 if not logged in
 - parameters: none

?>