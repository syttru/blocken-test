<?php
/**
 * BlockenMobile.class.php
 *
 * PHP versions 4 and 5
 *
 * @package   Blocken
 * @author    Kouhei Suzuki <sigma@mfer.jp>
 * @copyright 2006-2009 SIGMA Project
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   $Id: BlockenMobile.class.php 27 2009-04-25 06:39:17Z sigmax $
 */
if ( basename( $_SERVER[ 'SCRIPT_NAME' ] ) == basename( __FILE__ ) ) { exit; }

require_once 'Net/UserAgent/Mobile.php';

class BlockenMobile extends Net_UserAgent_Mobile
{
    /**
     * &factory()
     *
     * @access public
     * @param  mixed  $mUserAgent string | null
     * @return mixed              BlockenMobile | PEAR_Error
     */
    function &factory( $mUserAgent = null )
    {
        if ( is_null( $mUserAgent ) )
        {
            $mUserAgent = $_SERVER[ 'HTTP_USER_AGENT' ];
        }

        $sDriver = 'NonMobile';
        if ( BlockenMobile::isDoCoMo( $mUserAgent ) )
        {
            $sDriver = 'DoCoMo';
        }
        else if ( BlockenMobile::isEZweb( $mUserAgent ) )
        {
            $sDriver = 'EZweb';
        }
        else if ( BlockenMobile::isSoftBank( $mUserAgent ) )
        {
            $sDriver = 'SoftBank';
        }

        foreach ( $_SERVER as $sKey => $sValue )
        {
            if ( preg_match( '/^HTTP_X_EMULATOR_/', $sKey ) )
            {
                $sTmp = str_replace( 'EMULATOR', 'JPHONE', $sKey );
                $_SERVER[ $sTmp ] = $sValue;
            }
        }

        $sClass = "BlockenMobile_{$sDriver}";

        if ( ! class_exists( $sClass ) )
        {
            $sFile = str_replace( '_', '/', $sClass ) . '.php';
            if ( ! include_once $sFile )
            {
                return PEAR::raiseError( null,
                                         NET_USERAGENT_MOBILE_ERROR_NOT_FOUND,
                                         null, null,
                                         "Unable to include the {$sFile} file",
                                         'Net_UserAgent_Mobile_Error', true );
            }
        }

        $objInstance =& new $sClass( $mUserAgent );
        $objError =& $objInstance->isError();
        if ( BlockenMobile::isError( $objError ) )
        {
            if ( $GLOBALS[ '_NET_USERAGENT_MOBILE_FALLBACK_ON_NOMATCH' ]
             && NET_USERAGENT_MOBILE_ERROR_NOMATCH == $objError->getCode() )
            {
                $objInstance =& BlockenMobile::factory( 'Net_UserAgent_Mobile_Fallback_On_NoMatch' );
                return $objInstance;
            }

            $objInstance =& $objError;
        }

        return $objInstance;
    }

    /**
     * &singleton()
     *
     * @access public
     * @param  mixed  $mUserAgent string | null
     * @return mixed              BlockenMobile | PEAR_Error
     */
    function &singleton( $mUserAgent = null )
    {
        static $objInstance;

        if ( ! isset( $objInstance ) )
        {
            $objInstance = array();
        }

        if ( is_null( $mUserAgent ) )
        {
            $mUserAgent = $_SERVER[ 'HTTP_USER_AGENT' ];
        }

        if ( ! isset( $objInstance[ $mUserAgent ] ) )
        {
            $objInstance[ $mUserAgent ] = BlockenMobile::factory( $mUserAgent );
        }

        return $objInstance[ $mUserAgent ];
    }

    /**
     * &unicode2sjis()
     *
     * @access public
     * @return void
     */
    function &unicode2sjis()
    {
        $aryBuff = array(
            '/&#xE63E;/i' => '&#63647;',
            '/&#xE63F;/i' => '&#63648;',
            '/&#xE640;/i' => '&#63649;',
            '/&#xE641;/i' => '&#63650;',
            '/&#xE642;/i' => '&#63651;',
            '/&#xE643;/i' => '&#63652;',
            '/&#xE644;/i' => '&#63653;',
            '/&#xE645;/i' => '&#63654;',
            '/&#xE646;/i' => '&#63655;',
            '/&#xE647;/i' => '&#63656;',
            '/&#xE648;/i' => '&#63657;',
            '/&#xE649;/i' => '&#63658;',
            '/&#xE64A;/i' => '&#63659;',
            '/&#xE64B;/i' => '&#63660;',
            '/&#xE64C;/i' => '&#63661;',
            '/&#xE64D;/i' => '&#63662;',
            '/&#xE64E;/i' => '&#63663;',
            '/&#xE64F;/i' => '&#63664;',
            '/&#xE650;/i' => '&#63665;',
            '/&#xE651;/i' => '&#63666;',
            '/&#xE652;/i' => '&#63667;',
            '/&#xE653;/i' => '&#63668;',
            '/&#xE654;/i' => '&#63669;',
            '/&#xE655;/i' => '&#63670;',
            '/&#xE656;/i' => '&#63671;',
            '/&#xE657;/i' => '&#63672;',
            '/&#xE658;/i' => '&#63673;',
            '/&#xE659;/i' => '&#63674;',
            '/&#xE65A;/i' => '&#63675;',
            '/&#xE65B;/i' => '&#63676;',
            '/&#xE65C;/i' => '&#63677;',
            '/&#xE65D;/i' => '&#63678;',
            '/&#xE65E;/i' => '&#63679;',
            '/&#xE65F;/i' => '&#63680;',
            '/&#xE660;/i' => '&#63681;',
            '/&#xE661;/i' => '&#63682;',
            '/&#xE662;/i' => '&#63683;',
            '/&#xE663;/i' => '&#63684;',
            '/&#xE664;/i' => '&#63685;',
            '/&#xE665;/i' => '&#63686;',
            '/&#xE666;/i' => '&#63687;',
            '/&#xE667;/i' => '&#63688;',
            '/&#xE668;/i' => '&#63689;',
            '/&#xE669;/i' => '&#63690;',
            '/&#xE66A;/i' => '&#63691;',
            '/&#xE66B;/i' => '&#63692;',
            '/&#xE66C;/i' => '&#63693;',
            '/&#xE66D;/i' => '&#63694;',
            '/&#xE66E;/i' => '&#63695;',
            '/&#xE66F;/i' => '&#63696;',
            '/&#xE670;/i' => '&#63697;',
            '/&#xE671;/i' => '&#63698;',
            '/&#xE672;/i' => '&#63699;',
            '/&#xE673;/i' => '&#63700;',
            '/&#xE674;/i' => '&#63701;',
            '/&#xE675;/i' => '&#63702;',
            '/&#xE676;/i' => '&#63703;',
            '/&#xE677;/i' => '&#63704;',
            '/&#xE678;/i' => '&#63705;',
            '/&#xE679;/i' => '&#63706;',
            '/&#xE67A;/i' => '&#63707;',
            '/&#xE67B;/i' => '&#63708;',
            '/&#xE67C;/i' => '&#63709;',
            '/&#xE67D;/i' => '&#63710;',
            '/&#xE67E;/i' => '&#63711;',
            '/&#xE67F;/i' => '&#63712;',
            '/&#xE680;/i' => '&#63713;',
            '/&#xE681;/i' => '&#63714;',
            '/&#xE682;/i' => '&#63715;',
            '/&#xE683;/i' => '&#63716;',
            '/&#xE684;/i' => '&#63717;',
            '/&#xE685;/i' => '&#63718;',
            '/&#xE686;/i' => '&#63719;',
            '/&#xE687;/i' => '&#63720;',
            '/&#xE688;/i' => '&#63721;',
            '/&#xE689;/i' => '&#63722;',
            '/&#xE68A;/i' => '&#63723;',
            '/&#xE68B;/i' => '&#63724;',
            '/&#xE68C;/i' => '&#63725;',
            '/&#xE68D;/i' => '&#63726;',
            '/&#xE68E;/i' => '&#63727;',
            '/&#xE68F;/i' => '&#63728;',
            '/&#xE690;/i' => '&#63729;',
            '/&#xE691;/i' => '&#63730;',
            '/&#xE692;/i' => '&#63731;',
            '/&#xE693;/i' => '&#63732;',
            '/&#xE694;/i' => '&#63733;',
            '/&#xE695;/i' => '&#63734;',
            '/&#xE696;/i' => '&#63735;',
            '/&#xE697;/i' => '&#63736;',
            '/&#xE698;/i' => '&#63737;',
            '/&#xE699;/i' => '&#63738;',
            '/&#xE69A;/i' => '&#63739;',
            '/&#xE69B;/i' => '&#63740;',
            '/&#xE69C;/i' => '&#63808;',
            '/&#xE69D;/i' => '&#63809;',
            '/&#xE69E;/i' => '&#63810;',
            '/&#xE69F;/i' => '&#63811;',
            '/&#xE6A0;/i' => '&#63812;',
            '/&#xE6A1;/i' => '&#63813;',
            '/&#xE6A2;/i' => '&#63814;',
            '/&#xE6A3;/i' => '&#63815;',
            '/&#xE6A4;/i' => '&#63816;',
            '/&#xE6A5;/i' => '&#63817;',
            '/&#xE6CE;/i' => '&#63858;',
            '/&#xE6CF;/i' => '&#63859;',
            '/&#xE6D0;/i' => '&#63860;',
            '/&#xE6D1;/i' => '&#63861;',
            '/&#xE6D2;/i' => '&#63862;',
            '/&#xE6D3;/i' => '&#63863;',
            '/&#xE6D4;/i' => '&#63864;',
            '/&#xE6D5;/i' => '&#63865;',
            '/&#xE6D6;/i' => '&#63866;',
            '/&#xE6D7;/i' => '&#63867;',
            '/&#xE6D8;/i' => '&#63868;',
            '/&#xE6D9;/i' => '&#63869;',
            '/&#xE6DA;/i' => '&#63870;',
            '/&#xE6DB;/i' => '&#63872;',
            '/&#xE6DC;/i' => '&#63873;',
            '/&#xE6DD;/i' => '&#63874;',
            '/&#xE6DE;/i' => '&#63875;',
            '/&#xE6DF;/i' => '&#63876;',
            '/&#xE6E0;/i' => '&#63877;',
            '/&#xE6E1;/i' => '&#63878;',
            '/&#xE6E2;/i' => '&#63879;',
            '/&#xE6E3;/i' => '&#63880;',
            '/&#xE6E4;/i' => '&#63881;',
            '/&#xE6E5;/i' => '&#63882;',
            '/&#xE6E6;/i' => '&#63883;',
            '/&#xE6E7;/i' => '&#63884;',
            '/&#xE6E8;/i' => '&#63885;',
            '/&#xE6E9;/i' => '&#63886;',
            '/&#xE6EA;/i' => '&#63887;',
            '/&#xE6EB;/i' => '&#63888;',
            '/&#xE70B;/i' => '&#63920;',
            '/&#xE6EC;/i' => '&#63889;',
            '/&#xE6ED;/i' => '&#63890;',
            '/&#xE6EE;/i' => '&#63891;',
            '/&#xE6EF;/i' => '&#63892;',
            '/&#xE6F0;/i' => '&#63893;',
            '/&#xE6F1;/i' => '&#63894;',
            '/&#xE6F2;/i' => '&#63895;',
            '/&#xE6F3;/i' => '&#63896;',
            '/&#xE6F4;/i' => '&#63897;',
            '/&#xE6F5;/i' => '&#63898;',
            '/&#xE6F6;/i' => '&#63899;',
            '/&#xE6F7;/i' => '&#63900;',
            '/&#xE6F8;/i' => '&#63901;',
            '/&#xE6F9;/i' => '&#63902;',
            '/&#xE6FA;/i' => '&#63903;',
            '/&#xE6FB;/i' => '&#63904;',
            '/&#xE6FC;/i' => '&#63905;',
            '/&#xE6FD;/i' => '&#63906;',
            '/&#xE6FE;/i' => '&#63907;',
            '/&#xE6FF;/i' => '&#63908;',
            '/&#xE700;/i' => '&#63909;',
            '/&#xE701;/i' => '&#63910;',
            '/&#xE702;/i' => '&#63911;',
            '/&#xE703;/i' => '&#63912;',
            '/&#xE704;/i' => '&#63913;',
            '/&#xE705;/i' => '&#63914;',
            '/&#xE706;/i' => '&#63915;',
            '/&#xE707;/i' => '&#63916;',
            '/&#xE708;/i' => '&#63917;',
            '/&#xE709;/i' => '&#63918;',
            '/&#xE70A;/i' => '&#63919;',
            '/&#xE6AC;/i' => '&#63824;',
            '/&#xE6AD;/i' => '&#63825;',
            '/&#xE6AE;/i' => '&#63826;',
            '/&#xE6B1;/i' => '&#63829;',
            '/&#xE6B2;/i' => '&#63830;',
            '/&#xE6B3;/i' => '&#63831;',
            '/&#xE6B7;/i' => '&#63835;',
            '/&#xE6B8;/i' => '&#63836;',
            '/&#xE6B9;/i' => '&#63837;',
            '/&#xE6BA;/i' => '&#63838;',
            '/&#xF9B1;/i' => '&#xE70C;',
            '/&#xF9B2;/i' => '&#xE70D;',
            '/&#xF9B3;/i' => '&#xE70E;',
            '/&#xF9B4;/i' => '&#xE70F;',
            '/&#xF9B5;/i' => '&#xE710;',
            '/&#xF9B6;/i' => '&#xE711;',
            '/&#xF9B7;/i' => '&#xE712;',
            '/&#xF9B8;/i' => '&#xE713;',
            '/&#xF9B9;/i' => '&#xE714;',
            '/&#xF9BA;/i' => '&#xE715;',
            '/&#xF9BB;/i' => '&#xE716;',
            '/&#xF9BC;/i' => '&#xE717;',
            '/&#xF9BD;/i' => '&#xE718;',
            '/&#xF9BE;/i' => '&#xE719;',
            '/&#xF9BF;/i' => '&#xE71A;',
            '/&#xF9C0;/i' => '&#xE71B;',
            '/&#xF9C1;/i' => '&#xE71C;',
            '/&#xF9C2;/i' => '&#xE71D;',
            '/&#xF9C3;/i' => '&#xE71E;',
            '/&#xF9C4;/i' => '&#xE71F;',
            '/&#xF9C5;/i' => '&#xE720;',
            '/&#xF9C6;/i' => '&#xE721;',
            '/&#xF9C7;/i' => '&#xE722;',
            '/&#xF9C8;/i' => '&#xE723;',
            '/&#xF9C9;/i' => '&#xE724;',
            '/&#xF9CA;/i' => '&#xE725;',
            '/&#xF9CB;/i' => '&#xE726;',
            '/&#xF9CC;/i' => '&#xE727;',
            '/&#xF9CD;/i' => '&#xE728;',
            '/&#xF9CE;/i' => '&#xE729;',
            '/&#xF9CF;/i' => '&#xE72A;',
            '/&#xF9D0;/i' => '&#xE72B;',
            '/&#xF9D1;/i' => '&#xE72C;',
            '/&#xF9D2;/i' => '&#xE72D;',
            '/&#xF9D3;/i' => '&#xE72E;',
            '/&#xF9D4;/i' => '&#xE72F;',
            '/&#xF9D5;/i' => '&#xE730;',
            '/&#xF9D6;/i' => '&#xE731;',
            '/&#xF9D7;/i' => '&#xE732;',
            '/&#xF9D8;/i' => '&#xE733;',
            '/&#xF9D9;/i' => '&#xE734;',
            '/&#xF9DA;/i' => '&#xE735;',
            '/&#xF9DB;/i' => '&#xE736;',
            '/&#xF9DC;/i' => '&#xE737;',
            '/&#xF9DD;/i' => '&#xE738;',
            '/&#xF9DE;/i' => '&#xE739;',
            '/&#xF9DF;/i' => '&#xE73A;',
            '/&#xF9E0;/i' => '&#xE73B;',
            '/&#xF9E1;/i' => '&#xE73C;',
            '/&#xF9E2;/i' => '&#xE73D;',
            '/&#xF9E3;/i' => '&#xE73E;',
            '/&#xF9E4;/i' => '&#xE73F;',
            '/&#xF9E5;/i' => '&#xE740;',
            '/&#xF9E6;/i' => '&#xE741;',
            '/&#xF9E7;/i' => '&#xE742;',
            '/&#xF9E8;/i' => '&#xE743;',
            '/&#xF9E9;/i' => '&#xE744;',
            '/&#xF9EA;/i' => '&#xE745;',
            '/&#xF9EB;/i' => '&#xE746;',
            '/&#xF9EC;/i' => '&#xE747;',
            '/&#xF9ED;/i' => '&#xE748;',
            '/&#xF9EE;/i' => '&#xE749;',
            '/&#xF9EF;/i' => '&#xE74A;',
            '/&#xF9F0;/i' => '&#xE74B;',
            '/&#xF9F1;/i' => '&#xE74C;',
            '/&#xF9F2;/i' => '&#xE74D;',
            '/&#xF9F3;/i' => '&#xE74E;',
            '/&#xF9F4;/i' => '&#xE74F;',
            '/&#xF9F5;/i' => '&#xE750;',
            '/&#xF9F6;/i' => '&#xE751;',
            '/&#xF9F7;/i' => '&#xE752;',
            '/&#xF9F8;/i' => '&#xE753;',
            '/&#xF9F9;/i' => '&#xE754;',
            '/&#xF9FA;/i' => '&#xE755;',
            '/&#xF9FB;/i' => '&#xE756;',
            '/&#xF9FC;/i' => '&#xE757;'
        );

        return $aryBuff;
    }
}
?>
