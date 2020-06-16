<?php
/*
	Input:
		$_GET[ 'ime' ] - username igraca

	Output: JSON sa svojstvima
        uspjeh - true/false
        id - za sad samo vraca ime
        mojred - true/false
        ? ime2 - ime drugog igrača
*/

function sendJSONandExit( $message )
{
    // Kao izlaz skripte pošalji $message u JSON formatu i prekini izvođenje.
    header( 'Content-type:application/json;charset=utf-8' );
    echo json_encode( $message );
    flush();
    exit( 0 );
}

$ime = $_GET[ 'ime' ] ?? "server: nemam ime";

//--------------------------------------------------------------------------
// Pogledamo datoteku s imenima (usernames.txt), postoji li igrač koji čeka.
// Ako da, maknemo ga i posaljemo njegovo ime natrag.
// U usernames.txt imamo popis imena odvojenih s ";".
//--------------------------------------------------------------------------

$usernames = file_get_contents("usernames.txt");
// ako je prazno dodamo ovo ime
if ($usernames === "")
    file_put_contents("usernames.txt", $ime);
else
{// inače maknemo prvog u redu
    $users = explode(";", $usernames);
    $ime2 = $users[0];
    // pomaknemo ih natrag, implodamo i zapisemo u datoteku
}

///////////////////////////////
// DATOTEKE NISU DOBAR NAČIN // zato sam tu stal...
///////////////////////////////

$message = [];
$message[ 'uspjeh' ] = true;
$message[ 'id' ] = $ime;
$message[ 'mojred' ] = true;
$message[ 'ime2' ] = $ime2

$message[ 'row' ] = 2;
$message[ 'col' ] = 2;
//sleep(2);

sendJSONandExit( $message );

?>