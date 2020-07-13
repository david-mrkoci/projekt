<?php
/*
	Input:
        $_GET[ 'ime' ] - username igraca

	Output: JSON sa svojstvima
        opponent - protivnik protiv kojeg igramo
        ID - ID od igre
        mojred - jesam li na redu?
        players - lista slobodnih igraca
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
    // // spajanje na bazu podataka
    // $db = DB::getConnection();

    // // dohvacamo sve retke s nasim username-om
	// try
	// {
	// 	$st = $db->prepare( 'SELECT username FROM connect4 WHERE username=:username' );
	// 	$st->execute( array( 'username' => $username ) );
	// }
	// catch( PDOException $e ) { return; }

	// $row = $st->fetch();

	// if( $row === false )
	// {
	// 	// nema nas u bazi
	// 	return true;
	// }
	// else
	{
        $timestamp = time();
		$st = $db->prepare( 'UPDATE connect4 SET timestamp=:timestamp WHERE username=:username' );
		$st->execute( array( 'username' => $username, 'timestamp' => $timestamp ) );
	}
}

// brise neaktivne igrace
function cleanup()
{
    // spajanje na bazu podataka
    $db = DB::getConnection();

    // priprema naredbe za brisanje
    $st = $db->prepare( 'DELETE FROM connect4 WHERE :current - timestamp > :max_time AND in_game=0');

    // brisemo sve koji nisu u igri i neaktivni su vec 1 min
    $current = time();
    $max_time = 10; // 10 sec
	$st->execute( array( 'current' => $current, 'max_time' => $max_time ) );
}

// provjeravamo imamo li protivnika, tj. je li nas netko odabrao
function check_opponent($username)
{
    // spajanje na bazu podataka
    $db = DB::getConnection();

    // dohvacamo protivnika
	try
	{
		$st = $db->prepare( 'SELECT * FROM connect4 WHERE username=:username' );
		$st->execute( array( 'username' => $username ) );
	}
	catch( PDOException $e ) { return; }

	$row = $st->fetch();
    $opponent = $row['opponent'];
    $ID = $row['game_ID'];

    // ako imamo protivnika, posaljemo podatke o igri
    if ( $opponent !== NULL && $ID !== NULL )
    {
        $response = [];
        $response['opponent'] = $opponent;
        $response['ID'] = $ID;
        $response['mojred'] = false;

        sendJSONandExit( $response );
    }
}

// vraca listu svih igraca osim nas
function list_players($username)
{
    $players = [];
    // spajanje na bazu podataka
    $db = DB::getConnection();

    // dohvacamo igrace
	try
	{
		$st = $db->prepare( 'SELECT username FROM connect4 
                             WHERE username != :username' );
		$st->execute( array( 'username' => $username ) );
	}
    catch( PDOException $e ) { return; }

    // dodamo igrace u listu
    foreach( $st->fetchAll() as $row )
        $players[] = $row['username'];
    
    return $players;
}

#############################################################################
#############################################################################

// treba uskalditi GET-ove
$username = isset($_GET[ 'ime' ]) ? $_GET[ 'ime' ] : '';
$timestamp = time(); // trenutno vrijeme

update_timestamp($username);
cleanup();

check_opponent($username);

// ako smo tu onda nemamo protivnika, treba ispisati igrace

$players = list_players($username);
$response = [];
$response['opponent'] = "";//nema opponenta
$response[ 'players' ] = $players;

sendJSONandExit( $response );

?>