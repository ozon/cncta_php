<?php

/**
 * GNU Public License 3.0
 * Copyright (C) 2012 Harry Gabriel
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

class TiberiumAlliances
{
    private static $_instance = null;

    private $_session;

    public function __construct()
    {
        $this->_session = new stdClass();

        return $this;
    }

    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new TiberiumAlliances();
        }

        return self::$_instance;
    }

    public function login($user, $password=null, $lang=null)
    {
      $this->cookie = 'keks/' . md5($user) . '.txt';

      $_login_fields = array( 'spring-security-redirect' => '',
          'id' => '',
          'timezone' => '1',
          'j_username' => $user,
          'j_password' => $password,
          '_web_remember_me' => ''
          );

      // login with account data
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
      curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_URL, 'https://www.tiberiumalliances.com/j_security_check');
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_login_fields));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $result = curl_exec( $ch );
      //curl_close($ch);

      //$ch = curl_init();
      $urlL = 'https://www.tiberiumalliances.com/de/game/launch';
      curl_setopt($ch,CURLOPT_URL,$urlL);
      curl_setopt($ch, CURLOPT_REFERER, 'https://www.tiberiumalliances.com/de/login/auth');
      curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
      curl_setopt($ch, CURLOPT_COOKIEJAR,  $this->cookie);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $result = curl_exec($ch);
      curl_close($ch);

      // grab sessionId from result
      if (preg_match('/sessionId\" value=\"([^"]+)"/', $result, $match)) {
        $this->_session->id = $match[1];
      } else {
        // didn't found sessionId
      }
      // grab last used server
      if (preg_match('/action=\"([^"]+)\/index\.aspx"/', $result, $match)) {
        // ok...we can make it better ;)
        $_last_serverId = substr( parse_url( $match[1], PHP_URL_PATH) ,1 );

        $this->_session->server = $this->getServer($_last_serverId);
        //$this->_session->server->Url = preg_replace('/http/', 'https', $this->_session->server->Url);
      }

    }

    public function getServer($id)
    {
      $_account_data = $this->getData('https://gamecdnorigin.alliances.commandandconquer.com/Farm/Service.svc/ajaxEndpoint/', 'GetOriginAccountInfo', array('session'=>$this->_session->id ));
      $_servers = array();

      foreach ($_account_data->Servers as &$server) {
        if ($server->Id == $id) {
          return $server ;
          break;
        }
      }

      return false;
    }


    public function openGameSession()
    {
      $_post_data = array(
        'session' => $this->_session->id,
        'reset'=>true,
        'refId' => $this->getTimestamp(),
        'version'=>-1,
        'platformId'=>1
        );
      $url = $this->_session->server->Url . '/Presentation/Service.svc/ajaxEndpoint/';
      $result = $this->getData( $url, 'OpenSession', $_post_data);
      $this->_session->key = $result->i;
    }

    public function getData( $url=Null, $endpoint, $data )
    {
      $ch = curl_init();
      curl_setopt( $ch, CURLOPT_URL, $url . $endpoint );
      curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 2 );
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
      curl_setopt( $ch, CURLOPT_HTTPHEADER, array( "Content-Type: application/json; charset=utf-8", "Cache-Control: no-cache", "Pragma: no-cache", "X-Qooxdoo-Response-Type: application/json" ) );
      curl_setopt( $ch, CURLOPT_POST, true );
      curl_setopt( $ch, CURLOPT_POSTFIELDS, str_replace( '\\\\', '\\', json_encode( $data ) ) );
      curl_setopt( $ch, CURLOPT_COOKIEFILE, $this->cookie);
      curl_setopt( $ch, CURLOPT_COOKIEJAR, $this->cookie);
      $result = curl_exec( $ch );
      curl_close( $ch );
      if ( empty( $result )) {
        $this->error = 'errrrrrrrrrrrrroooorrrrr';

        return false;
      }

      return json_decode( $result );

    }

    public function get( $endpoint, $data=array())
    {
      $data = array_merge( array( 'session' => $this->_session->key ), $data );
      $url = $this->_session->server->Url . '/Presentation/Service.svc/ajaxEndpoint/';

      return $this->getData( $url, $endpoint, $data);
    }

    // little helpers
    private function getTimestamp()
    {
      $seconds = microtime(true); // false = int, true = float

      return round( ($seconds * 1000) );
    }

}
