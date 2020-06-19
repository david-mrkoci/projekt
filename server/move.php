<?php
/*
	Input:
        $_GET[ 'id' ] - username1_username2_idvrijeme (za prepoznavanje igre)
        $_GET[ 'row' ] - u koji red treba dodati krug, -1 ako nije nas red (dummy potez)
        $_GET[ 'col' ] - u koji stupac treba dodati krug, -1 ako nije nas red
        $_GET[ 'kraj' ] - true/false je li igra gotova

    Output: JSON sa svojstvima (natrag se šalje potez igrača 
                                koji nije pozvao skriptu)
        kraj - true/false je li igra gotova
        row - u koji red treba dodati krug
        col - u koji stupac treba dodati krug
*/

function sendJSONandExit( $message )
{
    // Kao izlaz skripte pošalji $message u JSON formatu i prekini izvođenje.
    header( 'Content-type:application/json;charset=utf-8' );
    echo json_encode( $message );
    flush();
    exit( 0 );
}

$id = isset( $_GET[ 'id' ] ) ? $_GET[ 'id' ] : '';
$row = isset( $_GET[ 'row' ] ) ? $_GET[ 'row' ] : '';
$col = isset( $_GET[ 'col' ] ) ? $_GET[ 'col' ] : '';
$kraj = isset( $_GET[ 'kraj' ] ) ? $_GET[ 'kraj' ] : '';
$timestamp = time();

//-------------------------------------------------------------------------
// zapisujemo poslane podatke u datoteku "username1_username2_idvrijeme.log"
// koja čuva zadnji potez u obliku: row(int) col(int) kraj(bool)
//-------------------------------------------------------------------------

$filename = "'" . $id . ".txt" . "'"; // mozda bez navodnika
$my_move = implode(" ", array($row, $col, $kraj));

$error = "";
// if( !file_exists( $filename ) )
//     $error = $error . "Datoteka " . $filename . " ne postoji. ";
// else
// {
//     if( !is_readable( $filename ) )
//         $error = $error . "Ne mogu čitati iz datoteke " . $filename . ". ";

//     if( !is_writable( $filename ) )
//         $error = $error . "Ne mogu pisati u datoteku " . $filename . ". ";
// }

if( $error !== "" )
{
    $response = [];
    $response[ 'error' ] = $error;

    sendJSONandExit( $response );
}

if( $id !== '' && $col !== '' && $row !== '' && $kraj !== '' )
{
    // spremamo potez u datoteku
    if ($col === "-1") {
        //$rr = fopen($filename, "w+");
        //fclose($rr);
        file_put_contents( $filename, "");
    }
    else
        file_put_contents( $filename, $my_move);

    // kada je zadnji put promjenjena datoteka s potezima?
    $currentmodif = filemtime( $filename );

    // vrtimo petlju dok datoteka nije modificirana
    //while ( $currentmodif <= $timestamp )
    {
        usleep( 1000 );
        clearstatcache();
        $currentmodif = filemtime( $filename );
    }sendJSONandExit( "array('row' => 2)" );
    // Ako smo ovdje, znamo da je datoteka promjenjena i u nju upisan novi potez
    // Spremimo ga i posaljemo natrag

    $response = array();
    $new_move = explode( " ", file_get_contents( $filename ) );
    $response[ 'row' ] = $new_move[0];
    $response[ 'col' ] = $new_move[1];
    if ($new_move[2] === "true")
        $response[ 'kraj' ] = true;
    if ($new_move[2] === "false")
        $response[ 'kraj' ] = false;
    

    if($response[ 'kraj' ] === true)// Ako je poslan kraj = true brisemo datoteku s potezima jer je igra gotova
        unlink($filename);

    sendJSONandExit( $response );
}
else
{
    $response = [];
    $response[ 'error' ] = "Potez nije dobro poslan.";

    sendJSONandExit( $response );
}

?>