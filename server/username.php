<?php
/*
	Input:
        $_GET[ 'ime' ] - username igraca

	Output: JSON sa svojstvima
        ok - true ako je ime jedinstveno
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

function is_unique($username)
{
    // spajanje na bazu podataka
    $db = DB::getConnection();

    // dohvacamo sve retke s nasim username-om
	try
	{
		$st = $db->prepare( 'SELECT username FROM connect4 
                             WHERE username=:username' );
		$st->execute( array( 'username' => $username ) );
	}
	catch( PDOException $e ) { return; }

	if ( $st->fetch() )
	{
		// postoji igrac s nasim imenom, nismo jedinstveni
		return false;
	}
	else
	{
		// nas username nije u bazi, znaci da smo jedinstveni
		return true;
	}
}

function add_user($username, $timestamp)
{
    // spajanje na bazu podataka
    $db = DB::getConnection();

    // dodajemo usera
	try
	{
		// pripremi insert naredbu
		$st = $db->prepare( 'INSERT INTO connect4 (username, timestamp) 
                             VALUES (:username, :timestamp)' );

        // izvrši tu insert naredbu
        $st->execute( array( 'username' => $username, 
                             'timestamp' => $timestamp ) );
	}
	catch( PDOException $e ) { return; }
}

#############################################################################
#############################################################################

// treba uskalditi GET-ove
$username = isset($_GET[ 'ime' ]) ? $_GET[ 'ime' ] : '';
$timestamp = time(); // vrijeme prijave

// postavljamo ok varijablu na false
$ok = false;

// imamo li jedinstveno korisnicko ime?
if ( is_unique($username) )
{
    // da, dodajemo novi redak
    $ok = true;
    add_user($username, $timestamp);
}

$response [ 'ok' ] = $ok;
sendJSONandExit( $response );

?>