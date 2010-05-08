<?php
$bRet = false;
if ( isset( $aryParam[ 'arg1' ], $aryParam[ 'uid' ] ) )
{
    if ( $aryPear[ 'mobile' ]->isNonMobile() && 'I' == $aryParam[ 'arg1' ] )
    {
        $aryPear[ 'mobile' ] = BlockenMobile::singleton( 'DoCoMo/2.0 ' );
        $aryParam[ 'uid' ]   = substr( $aryParam[ 'uid' ], 2 );
    }

    $bRet = funcExpireMember( $aryParam[ 'uid' ], $aryPear );
}

if ( BLOCKEN_MOBI_OFFCIAL )
{
    if ( $aryPear[ 'mobile' ]->isDoCoMo() )
    {
        if ( $bRet )
        {
            echo "OK\n";
        }
        else
        {
            echo "NG\n";
        }
        exit;
    }
}

$objTemplate = loadTemplate();

if ( $bRet )
{
    $objTemplate->touchBlock( 'OK' );
}
else
{
    $objTemplate->touchBlock( 'NG' );
}

if ( isset( $aryParam[ '_rtn' ] ) )
{
    $objTemplate->setVariable( 'return', $aryParam[ '_rtn' ] );
}
else
{
    $objTemplate->setVariable( 'return', BLOCKEN_HTTP_URL . BLOCKEN_ROOT_PATH . '/' );
}

$objTemplate->show();
?>
