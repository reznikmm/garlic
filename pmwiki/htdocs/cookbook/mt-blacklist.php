<?PHP

$eMailAddress = "";  // Change this to your e-mail address or "" if you don't want e-mail
$lockTime = 0;       // Change this to the number of seconds to "lock up" on error

// Skip the Blocklist Cookbook control file (so you can add "special" words in addition to this
if( $_POST["pagename"] != "Main.Blocklist" )
  if( check_MT_Blacklist( $_POST['text'] ) ) { 
	  
	  //  E-Mail the administrator if an e-mail address is entered
    if( $eMailAddress != "" )
        mail( $eMailAddress, "Blocked Wiki Comment", 
    			sprintf( "BLOCKED Wiki Post\n\nHTTP_REFERER: %s\nREMOTE_ADDR: %s\n\n%s", 
        		$_SERVER['HTTP_REFERER'], $_SERVER['REMOTE_ADDR'], $_POST['text'] ) );
        		
    unset($_POST['post']);
    $EditMessageFmt .= "<h3 class='wikimessage'>$[This post has been blocked by the administrator]</h3>";

    if( $lockTime > 0 )    
        sleep( $lockTime );
  }

function check_MT_Blacklist( $str ) {

	// Set up config variables
	
	// Automatic File Update
  $MTB_time = 86400;	// Time in seconds since last update of master file - 0 = don't update																									
	$MTB_url	= "http://www.jayallen.org/comment_spam/blacklist.txt";
	$MTB_name = "MT-Blacklist.txt";		// local file name
	
	// Check to see if MT-Blacklist.txt is in current directory and it's current
  if( $MTB_time > 0 )
  		if( ( ! ($MTStat = @stat( $MTB_name ) ) ) || ( $MTStat["mtime"] < (time()-($MTB_time)) ) ) {
	  		// Download file 
	  		if( ( $MTB = @file($MTB_url) ) )
	  				if( $MTB_file = fopen( $MTB_name, "wb" ) ) {
		  				fwrite( $MTB_file, implode( '', $MTB ) );
		  				fclose( $MTB_file );
	  				}
  		}
  
  if(( ! isset( $MTB ) ) || ( ! $MTB ) )
  		$MTB = file( $MTB_name );
  		
  //  Check string against regular expressions		
  
  foreach( $MTB as $MTB_check ) {
	  $MTB_check = preg_replace( "/( *#.*)*$/", "", rtrim( $MTB_check ) );
	  if( ( $MTB_check != "" ) && (@preg_match( "~" . $MTB_check . "~i", $str ) ) )
	  		return( TRUE );
  }
  return( FALSE );
}
  		
?>