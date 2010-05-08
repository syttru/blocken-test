<?php
if ( ! isset( $aryParam[ '_rtn' ] ) )
{
    $aryParam[ '_rtn' ] = BLOCKEN_HTTP_URL . BLOCKEN_ROOT_PATH . '/';
}

if ( BLOCKEN_MOBI_OFFCIAL )
{
    $sUrl = '';
    switch ( $aryParam[ '_mode' ] )
    {
        case 'regist':
            $sUrl = BlockenCommon::registUrl( $aryParam[ '_rtn' ], $aryPear );
            break;

        case 'expire':
            $sUrl = BlockenCommon::expireUrl( $aryParam[ '_rtn' ], $aryPear );
            break;

        default:
            $sUrl = BLOCKEN_HTTP_URL . BLOCKEN_ROOT_PATH . '/';
            break;
    }
    header( "Location: {$sUrl}" );
    exit;
}

$objTemplate = loadTemplate();

$objTemplate->hideBlock( 'regist' );
$objTemplate->hideBlock( 'expire' );
$objTemplate->hideBlock( 'error' );

switch ( $aryParam[ '_mode' ] )
{
    case 'regist':
        $objTemplate->touchBlock( 'regist' );
        break;

    case 'expire':
        $objTemplate->touchBlock( 'expire' );
        break;

    default:
        $objTemplate->touchBlock( 'error' );
        break;
}

$objTemplate->setVariable( 'return',  $aryParam[ '_rtn' ] );
$objTemplate->setVariable( 'carrier', $aryPear[ 'mobile' ]->getCarrierShortName() );

$objTemplate->show();
?>
