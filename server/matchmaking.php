<?php
/*
	Input:
        $_GET[ 'player1' ] - to smo mi
        $_GET[ 'player2' ] - protivnik kojeg smo odabrali
        $_GET[ 'ID' ] - game ID

	Output: JSON sa svojstvima
        ok - true/false
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

function is_player_available($player2)
{
    // spajanje na bazu podataka
    $db = DB::getConnection();

    // dohvacamo redak s player2
	try
	{
		$st = $db->prepare( 'SELECT in_game FROM connect4 
                             WHERE username=:username' );
		$st->execute( array( 'username' => $player2 ) );
	}
	catch( PDOException $e ) { return false; }

	$row = $st->fetch();

    if ( $row[ 'in_game' ] == 0 )
    {
        return true;
    }
    else//if ( $row[ 'in_game' ] == 1 )
    {
        return false;
    }
}

// postavlja igracu palyer1 opponenta player2, game ID, in_game
function prepare($player1, $player2, $ID)
{
    // spajanje na bazu podataka
    $db = DB::getConnection();

    // dohvacamo redak s player2
	try
	{
		$st = $db->prepare( 'UPDATE connect4 
                             SET opponent=:player2, in_game= 1, game_ID= :ID 
                             WHERE username=:username' );
        $st->execute( array( 'player2' => $player2, 'ID' => $ID, 
                             'username' => $player1 ) );
	}
    catch( PDOException $e ) { return; }
}

#############################################################################
#############################################################################

// treba uskalditi GET-ove
$player1 = isset($_GET[ 'player1' ]) ? $_GET[ 'player1' ] : '';
$player2 = isset($_GET[ 'player2' ]) ? $_GET[ 'player2' ] : '';
$ID = isset($_GET[ 'ID' ]) ? $_GET[ 'ID' ] : '';

// postavljamo ok varijablu na false
$ok = false;

// je li player 2 slobodan?
if ( is_player_available($player2) )
{
    // slobodan je, updejtamo stvari i saljemo ok = true
    $ok = true;
    prepare($player1, $player2, $ID);
    prepare($player2, $player1, $ID);
}

$response [ 'ok' ] = $ok;
sendJSONandExit( $response );

?>