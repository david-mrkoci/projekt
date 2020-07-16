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
    // spajanje na bazu podataka
    $db = DB::getConnection();

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
        $tstamp = time();
		$st = $db->prepare( 'UPDATE connect4 SET tstamp=:tstamp WHERE username=:username' );
		$st->execute( array( 'tstamp' => $tstamp, 'username' => $username ) );
	}
}

// brise neaktivne igrace
function cleanup()
{
    // spajanje na bazu podataka
    $db = DB::getConnection();

    ######za igrače koji čekaju na protivnika limit je 60 sekundi
    // priprema naredbe za brisanje
    $st = $db->prepare( 'DELETE FROM connect4 WHERE :current - tstamp > :max_time AND in_game=0');

    // brisemo sve koji nisu u igri i neaktivni su vec 1 min
    $current = time();
    $max_time = 60; // 60 sec
    $st->execute( array( 'current' => $current, 'max_time' => $max_time ) );
    

    ###za igrače u igri limit je jedan dan
    // priprema naredbe za brisanje
    $st = $db->prepare( 'DELETE FROM connect4 WHERE :current - tstamp > :max_time AND in_game=1');

    // brisemo sve koji su u igri i neaktivni su vec 1 dan
    $current = time();
    $max_time = 60*60*24; // 60 sec
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
                             WHERE in_game != 1 AND username != :username' );
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
//$tstamp = time(); // trenutno vrijeme

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