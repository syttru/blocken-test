<?php
/**
 * BlockenDB.class.php
 *
 * PHP versions 4 and 5
 *
 * @package   Blocken
 * @author    Kouhei Suzuki <sigma@mfer.jp>
 * @copyright 2006-2009 SIGMA Project
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   $Id: BlockenDB.class.php 27 2009-04-25 06:39:17Z sigmax $
 */
if ( basename( $_SERVER[ 'SCRIPT_NAME' ] ) == basename( __FILE__ ) ) { exit; }

require_once 'MDB2.php';

class BlockenDB
{
    /**
     * @access private
     * @var    array $_aryConn
     */
    var $_aryConn = array();

    /**
     * @access private
     * @var    object $_objWork
     */
    var $_objWork = null;

    /**
     * BlockenDB()
     */
    function BlockenDB()
    {
        $this->_objWork =& new MDB2_Driver_Common();
        $this->_objWork->phptype = 'Common';
        $this->_objWork->loadModule( 'Extended' );
    }

    /**
     * connect()
     *
     * @access public
     * @param  string $sDsn
     * @param  mixed  $mOptions boolean | array
     * @return mixed            boolean | MDB2_Error
     */
    function connect( $sDsn, $mOptions = array() )
    {
        if ( isset( $this->_aryConn[ $sDsn ] ) )
        {
            $mRet = $this->setOptions( $sDsn, $mOptions );
            if ( MDB2::isError( $mRet ) )
            {
                 return $mRet;
            }

            $this->_aryConn[ $sDsn ]->debug_output = '';

            return true;
        }

        if ( ! is_array( $mOptions ) )
        {
            $mOptions = array( 'persistent' => $mOptions );
        }

        $this->_aryConn[ $sDsn ] = MDB2::factory( $sDsn, $mOptions );
        if ( MDB2::isError( $this->_aryConn[ $sDsn ] ) )
        {
            $objConn = $this->_aryConn[ $sDsn ];
            unset( $this->_aryConn[ $sDsn ] );
            return $objConn;
        }

        $this->_aryConn[ $sDsn ]->loadModule( 'Date' );
        $this->_aryConn[ $sDsn ]->loadModule( 'Extended' );

        $this->_aryConn[ $sDsn ]->setFetchMode( MDB2_FETCHMODE_ASSOC );
        $this->_aryConn[ $sDsn ]->setCharset( 'UTF8' );

        $this->_aryConn[ $sDsn ]->debug_output = '';

        return true;
    }

    /**
     * setOption()
     *
     * @access public
     * @param  string $sDsn
     * @param  string $sOption
     * @param  mixed  $mValue
     * @return mixed           boolean | MDB2_Error | PEAR_Error
     */
    function setOption( $sDsn, $sOption, $mValue )
    {
        $objConn =& $this->_aryConn[ $sDsn ];

        if ( ! is_object( $objConn ) )
        {
            return PEAR::raiseError( null, MDB2_ERROR_NOT_FOUND, null, null, 'None connection', 'MDB2_Error', true );
        }

        $mRet = $objConn->setOption( $sOption, $mValue );
        if ( MDB2::isError( $mRet ) )
        {
            return $mRet;
        }

        return true;
    }

    /**
     * setOptions()
     *
     * @access public
     * @param  string $sDsn
     * @param  array  $aryOptions
     * @return mixed              boolean | MDB2_Error | PEAR_Error
     */
    function setOptions( $sDsn, $aryOptions )
    {
        $objConn =& $this->_aryConn[ $sDsn ];

        if ( ! is_object( $objConn ) )
        {
            return PEAR::raiseError( null, MDB2_ERROR_NOT_FOUND, null, null, 'None connection', 'MDB2_Error', true );
        }

        if ( ! is_array( $aryOptions ) )
        {
            return false;
        }

        foreach ( $aryOptions as $sOption => $sValue )
        {
            $mRet = $objConn->setOption( $sOption, $sValue );
            if ( MDB2::isError( $mRet ) )
            {
                 return $mRet;
            }
        }

        return true;
    }

    /**
     * debug()
     *
     * @access public
     * @param  string $sDsn
     * @return mixed        string | PEAR_Error
     */
    function debug( $sDsn )
    {
        $objConn =& $this->_aryConn[ $sDsn ];

        if ( ! is_object( $objConn ) )
        {
            return PEAR::raiseError( null, MDB2_ERROR_NOT_FOUND, null, null, 'None connection', 'MDB2_Error', true );
        }

        $sRet = $objConn->getDebugOutput();

        return $sRet;
    }

    /**
     * bindParam()
     *
     * @access public
     * @param  string $sQuery
     * @param  array  $aryParams
     * @return mixed             string | PEAR_Error
     */
    function bindParam( $sQuery, $aryParams )
    {
        $objStatement = $this->_objWork->prepare( $sQuery );
        $objStatement->bindValueArray( $aryParams );

        $sRealquery = '';
        $iLastPosition = 0;
        foreach ( $objStatement->positions as $iCurrentPosition => $sParameter )
        {
            if ( ! array_key_exists( $sParameter, $objStatement->values ) )
            {
                return PEAR::raiseError( null, MDB2_ERROR_NOT_FOUND, null, null,
                                         'Unable to bind to missing placeholder: ' . $sParameter, __FUNCTION__ );
            }
            $sValue = $objStatement->values[ $sParameter ];
            $sRealquery .= substr( $sQuery, $iLastPosition, $iCurrentPosition - $iLastPosition );
            if ( '' == $sValue )
            {
                $sValueQuoted = 'NULL';
            }
            else
            {
                $sValueQuoted = $this->_objWork->quote( $sValue );
                if ( PEAR::isError( $sValueQuoted ) )
                {
                    return $sValueQuoted;
                }
            }
            $sRealquery .= $sValueQuoted;
            $iLastPosition = $iCurrentPosition + 1;
        }
        $sRealquery .= substr( $sQuery, $iLastPosition );

        $objStatement->free();

        return $sRealquery;
    }

    /**
     * buildSelectSQL()
     *
     * @access public
     * @param  string $sTable
     * @param  array  $aryTableFields
     * @param  mixed  $mWhere         string | array | boolean
     * @return mixed                  string | PEAR_Error
     */
    function buildSelectSQL( $sTable, $aryTableFields = array(), $mWhere = false )
    {
        $sSql = $this->_objWork->extended->buildManipSQL( $sTable, $aryTableFields, MDB2_AUTOQUERY_SELECT, $mWhere );

        return $sSql;
    }

    /**
     * &query()
     *
     * @access public
     * @param  string $sDsn
     * @param  string $sQuery
     * @param  array  $aryParams
     * @return mixed             MDB2_Result | MDB2_Error | PEAR_Error
     */
    function &query( $sDsn, $sQuery, $aryParams = array() )
    {
        $objConn =& $this->_aryConn[ $sDsn ];

        if ( ! is_object( $objConn ) )
        {
            return PEAR::raiseError( null, MDB2_ERROR_NOT_FOUND, null, null, 'None connection', 'MDB2_Error', true );
        }

        if ( empty( $aryParams ) )
        {
            return $objConn->query( $sQuery );
        }

        $objStmt = $objConn->prepare( $sQuery );
        if ( MDB2::isError( $objStmt ) )
        {
            return $objStmt;
        }

        $objRet = $objStmt->execute( $aryParams );
        if ( MDB2::isError( $objRet ) )
        {
            return $objRet;
        }

        $objStmt->free();

        return $objRet;
    }

    /**
     * &limitQuery()
     *
     * @access public
     * @param  string  $sDsn
     * @param  string  $sQuery
     * @param  integer $iLimit
     * @param  mixed   $mOffset   integer | null
     * @param  array   $aryParams
     * @return mixed              MDB2_Result | MDB2_Error | PEAR_Error
     */
    function &limitQuery( $sDsn, $sQuery, $iLimit, $mOffset = null, $aryParams = array() )
    {
        $objConn =& $this->_aryConn[ $sDsn ];

        if ( ! is_object( $objConn ) )
        {
            return PEAR::raiseError( null, MDB2_ERROR_NOT_FOUND, null, null, 'None connection', 'MDB2_Error', true );
        }

        $objRet = $objConn->setLimit( $iLimit, $mOffset );
        if ( MDB2::isError( $objRet ) )
        {
            return $objRet;
        }

        $objRet = $this->query( $sDsn, $sQuery, $aryParams );

        return $objRet;
    }

    /**
     * &getOne()
     *
     * @access public
     * @param  string  $sDsn
     * @param  string  $sQuery
     * @param  array   $aryParams
     * @param  integer $iColnum
     * @return mixed              string | MDB2_Error | PEAR_Error
     */
    function &getOne( $sDsn, $sQuery, $aryParams = array(), $iColnum = 0 )
    {
        $objConn =& $this->_aryConn[ $sDsn ];

        if ( ! is_object( $objConn ) )
        {
            return PEAR::raiseError( null, MDB2_ERROR_NOT_FOUND, null, null, 'None connection', 'MDB2_Error', true );
        }

        $sRet = $objConn->extended->getOne( $sQuery, null, $aryParams, null, $iColnum );

        return $sRet;
    }

    /**
     * &getRow()
     *
     * @access public
     * @param  string  $sDsn
     * @param  string  $sQuery
     * @param  array   $aryParams
     * @param  integer $iFetchmode
     * @return mixed               array | MDB2_Error | PEAR_Error
     */
    function &getRow( $sDsn, $sQuery, $aryParams = array(), $iFetchmode = MDB2_FETCHMODE_ASSOC )
    {
        $objConn =& $this->_aryConn[ $sDsn ];

        if ( ! is_object( $objConn ) )
        {
            return PEAR::raiseError( null, MDB2_ERROR_NOT_FOUND, null, null, 'None connection', 'MDB2_Error', true );
        }

        $aryRet = $objConn->extended->getRow( $sQuery, null, $aryParams, null, $iFetchmode );

        return $aryRet;
    }

    /**
     * &getCol()
     *
     * @access public
     * @param  string  $sDsn
     * @param  string  $sQuery
     * @param  array   $aryParams
     * @param  integer $iColnum
     * @return mixed              array | MDB2_Error | PEAR_Error
     */
    function &getCol( $sDsn, $sQuery, $aryParams = array(), $iColnum = 0 )
    {
        $objConn =& $this->_aryConn[ $sDsn ];

        if ( ! is_object( $objConn ) )
        {
            return PEAR::raiseError( null, MDB2_ERROR_NOT_FOUND, null, null, 'None connection', 'MDB2_Error', true );
        }

        $aryRet = $objConn->extended->getCol( $sQuery, null, $aryParams, null, $iColnum );

        return $aryRet;
    }

    /**
     * &getAll()
     *
     * @access public
     * @param  string  $sDsn
     * @param  string  $sQuery
     * @param  array   $aryParams
     * @param  integer $iFetchmode
     * @return mixed               array | MDB2_Error | PEAR_Error
     */
    function &getAll( $sDsn, $sQuery, $aryParams = array(), $iFetchmode = MDB2_FETCHMODE_ASSOC )
    {
        $objConn =& $this->_aryConn[ $sDsn ];

        if ( ! is_object( $objConn ) )
        {
            return PEAR::raiseError( null, MDB2_ERROR_NOT_FOUND, null, null, 'None connection', 'MDB2_Error', true );
        }

        $aryRet = $objConn->extended->getAll( $sQuery, null, $aryParams, null, $iFetchmode );

        return $aryRet;
    }

    /**
     * exists()
     *
     * @access public
     * @param  string $sDsn
     * @param  string $sTable
     * @param  mixed  $mWhere string | array | boolean
     * @return mixed          boolean | MDB2_Error | PEAR_Error
     */
    function exists( $sDsn, $sTable, $mWhere = false )
    {
        $objConn =& $this->_aryConn[ $sDsn ];

        if ( ! is_object( $objConn ) )
        {
            return PEAR::raiseError( null, MDB2_ERROR_NOT_FOUND, null, null, 'None connection', 'MDB2_Error', true );
        }

        $sSql = $objConn->extended->buildManipSQL( $sTable, array( 'count(*)' ), MDB2_AUTOQUERY_SELECT, $mWhere );

        $sRet = $objConn->queryOne( $sSql );
        if ( MDB2::isError( $sRet ) )
        {
            return $sRet;
        }

        if ( 0 == intval( $sRet ) )
        {
            return false;
        }

        return true;
    }

    /**
     * insert()
     *
     * @access public
     * @param  string $sDsn
     * @param  string $sTable
     * @param  array  $aryFieldsValues
     * @return mixed                   boolean | MDB2_Error | PEAR_Error
     */
    function insert( $sDsn, $sTable, $aryFieldsValues )
    {
        $objConn =& $this->_aryConn[ $sDsn ];

        if ( ! is_object( $objConn ) )
        {
            return PEAR::raiseError( null, MDB2_ERROR_NOT_FOUND, null, null, 'None connection', 'MDB2_Error', true );
        }

        $mRet = $objConn->extended->autoExecute( $sTable, $aryFieldsValues, MDB2_AUTOQUERY_INSERT );

        return $mRet;
    }

    /**
     * update()
     *
     * @access public
     * @param  string $sDsn
     * @param  string $sTable
     * @param  array  $aryFieldsValues
     * @param  mixed  $mWhere          string | array | boolean
     * @return mixed                   boolean | MDB2_Error | PEAR_Error
     */
    function update( $sDsn, $sTable, $aryFieldsValues, $mWhere = false )
    {
        $objConn =& $this->_aryConn[ $sDsn ];

        if ( ! is_object( $objConn ) )
        {
            return PEAR::raiseError( null, MDB2_ERROR_NOT_FOUND, null, null, 'None connection', 'MDB2_Error', true );
        }

        $mRet = $objConn->extended->autoExecute( $sTable, $aryFieldsValues, MDB2_AUTOQUERY_UPDATE, $mWhere );

        return $mRet;
    }

    /**
     * merge()
     *
     * @access public
     * @param  string $sDsn
     * @param  string $sTable
     * @param  array  $aryFieldsValues
     * @param  mixed  $mWhere          string | array | boolean
     * @return mixed                   boolean | MDB2_Error | PEAR_Error
     */
    function merge( $sDsn, $sTable, $aryFieldsValues, $mWhere = false )
    {
        $bRet = $this->exists( $sDsn, $sTable, $mWhere );
        if ( MDB2::isError( $bRet ) )
        {
            return $bRet;
        }

        if ( ! $bRet )
        {
            $mRet = $this->insert( $sDsn, $sTable, $aryFieldsValues );
        }
        else
        {
            $mRet = $this->update( $sDsn, $sTable, $aryFieldsValues, $mWhere );
        }

        return $mRet;
    }

    /**
     * delete()
     *
     * @access public
     * @param  string $sDsn
     * @param  string $sTable
     * @param  mixed  $mWhere string | array | boolean
     * @return mixed          boolean | MDB2_Error | PEAR_Error
     */
    function delete( $sDsn, $sTable, $mWhere = false )
    {
        $objConn =& $this->_aryConn[ $sDsn ];

        if ( ! is_object( $objConn ) )
        {
            return PEAR::raiseError( null, MDB2_ERROR_NOT_FOUND, null, null, 'None connection', 'MDB2_Error', true );
        }

        $mRet = $objConn->extended->autoExecute( $sTable, null, MDB2_AUTOQUERY_DELETE, $mWhere );

        return $mRet;
    }

    /**
     * truncate()
     *
     * @access public
     * @param  string $sDsn
     * @param  string $sTable
     * @return mixed          boolean | MDB2_Error | PEAR_Error
     */
    function truncate( $sDsn, $sTable )
    {
        $objConn =& $this->_aryConn[ $sDsn ];

        if ( ! is_object( $objConn ) )
        {
            return PEAR::raiseError( null, MDB2_ERROR_NOT_FOUND, null, null, 'None connection', 'MDB2_Error', true );
        }

        $sSql = "TRUNCATE TABLE {$sTable}";

        $mRet = $objConn->exec( $sSql );

        return $mRet;
    }
}
?>
