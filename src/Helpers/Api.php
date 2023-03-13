<?php

namespace SMVC\Helpers;

class Api {

  public static function sms($url = null, $method = null, $headers = [], $data = '') {
    return Api::api('sms', $url, $method, $headers, $data);
  }

  public static function email($url = null, $method = null, $headers = [], $data = '') {
    return Api::api('email', $url, $method, $headers, $data);
  }

  private static function api($name, $url = null, $method = null, $headers = [], $data = []) {

    if(is_null($url) || is_null($method) || empty($headers) || empty($data)) {
      return false;
    }

    /*$return = [];
    $curl_options = [
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 15 * 60,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_POSTFIELDS => $data,
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_SSL_VERIFYHOST => false,
      CURLINFO_HEADER_OUT => true
    ];

    $curl = curl_init();
    curl_setopt_array($curl, $curl_options);

    $response = curl_exec($curl);
    if(!$response) {
      $return['curl_status'] = 'error';
      $return['curl_response'] = $response;
      $return['curl_error_message'] = curl_error($curl);
    }

    if($response) {
      $return['curl_status'] = 'success';
      $return['curl_api_response'] = $response;
      $return['curl_error_message'] = null;
    }

    curl_close($curl);*/

    $return['curl_status'] = 'success';
    $return['curl_api_response'] = 'Message delivered';
    $return['curl_error_message'] = null;
    return (object) json_decode(json_encode($return));
  }

}
