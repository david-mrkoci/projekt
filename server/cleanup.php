<?php
/*
	Input:
        $_GET[ 'ime' ] - username igraca

	Output: JSON sa svojstvima
        nista
*/

require_once 'db_class.php';

function sendJSONandExit( $message )
{
    // Kao izlaz skripte pošalji $message u JSON formatu i prekini izvođenje.
    header( 'Content-type:application/json;charset=utf-8' );
    echo json_encode( $message );
    flush();
    exit( 0 );
}

function update_timestamp($username)
{
    // spajanje na bazu podataka
    $db = DB::getConnection();

    // dohvacamo sve retke s nasim username-om
	try
	{
		$st = $db->prepare( 'SELECT username FROM connect4 WHERE username=:username' );
		$st->execute( array( 'username' => $username ) );
	}
	catch( PDOException $e ) { return; }

	if ($row = $st->fetch() )
	{
        $timestamp = time();
		$st = $db->prepare( 'UPDATE connect4 SET timestamp= :timestamp WHERE username= :username' );
		$st->execute( array( 'username' => $username, 'timestamp' => $timestamp ) );
	}
	else
	{
		// nema nas u bazi
		return true;
	}

}

// brise protivnika, game ID i postavlja in_game na 0
function cleaup($username)
{
    // spajanje na bazu podataka
    $db = DB::getConnection();
    $timestamp = time();

    // priprema naredbe za update
    $st = $db->prepare( 'UPDATE connect4 SET in_game= 0, opponent= NULL, game_ID= NULL WHERE username= :username');
    $st->execute( array( 'username' => $username ) );
}

#############################################################################
#############################################################################

// treba uskalditi GET-ove
$username = isset($_GET[ 'ime' ]) ? $_GET[ 'ime' ] : '';

update_timestamp($username);
cleaup($username);

sendJSONandExit( $response );

?>