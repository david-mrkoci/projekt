<?php
/*
	Input:
		$_GET[ 'ime' ] - username igraca

	Output: JSON sa svojstvima
        uspjeh - true/false
        id - za sad samo vraca ime
        mojred - true/false
*/

function sendJSONandExit( $message )
{
    // Kao izlaz skripte pošalji $message u JSON formatu i prekini izvođenje.
    header( 'Content-type:application/json;charset=utf-8' );
    echo json_encode( $message );
    flush();
    exit( 0 );
}


$ime = $_GET[ 'ime' ];

$message = [];
$message[ 'uspjeh' ] = true;
$message[ 'id' ] = $ime;
$message[ 'mojred' ] = true;
sendJSONandExit( $message );

?>
