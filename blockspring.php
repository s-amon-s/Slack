<?php

class Blockspring {
  public static function parse($input_params, $json_parsed = true) {
    $request = new BlockspringRequest();

    # try to parse inputs as json
    if ($json_parsed == true) {
      $params = $input_params;
    } else {
      $params = json_decode($input_params, true);
    }

    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new Exception("STDIN was not valid JSON.");
    }

    // If inputs json, check if they're an array.
    if (!self::is_assoc($params)) {
      throw new Exception("STDIN was valid JSON, but was not key value pairs.");
    }

    // Check if following blockspring spec
    if (isset($params["_blockspring_spec"]) && $params["_blockspring_spec"]) {
      // We're following spec so lets remove _blockspring_spec, print errors to stderr, and parse files.
      foreach($params as $key => $val) {
        if ($key == "_blockspring_spec") {
          // Remove _blockspring_spec flag from params.
          unset($params[$key]);
        } elseif ($key == "_errors" && is_array($params["_errors"])) {
          // Add errors to request object.

          foreach ($params["_errors"] as $error) {
            // Make sure the error has a title.
            if (self::is_assoc($error) && $error["title"]) {
              $request->addError($error);
            }
          }
        } elseif ($key == "_headers" && is_array($params["_headers"])) {
          // Add headers to request object.
          $request->addHeaders($params["_headers"]);
        } elseif (self::is_assoc($params[$key]) && $params[$key]["filename"]) {

          if ($params[$key]["data"] || $params[$key]["url"]) {
            // Create temp file
            $prefix = "-" . $params[$key]["filename"];
            $tmp_file_name = tempnam(sys_get_temp_dir(), "") . $prefix;
            $request->params[$key] = $tmp_file_name;
            $handle = fopen($tmp_file_name, "w");

            // Check if we have raw data
            if ($params[$key]["data"]) {
              // Try to decode base64, if not set naively.
              try {
                $file_contents = base64_decode($params[$key]["data"]);
              } catch (Exception $e) {
                $file_contents = $params[$key]["data"];
              }
            } elseif ($params[$key]["url"]) {
              // Download file and save to tmp file.
              $opts = array(
                'http' => array(
                  'method' => "GET"
                )
              );

              $context = stream_context_create($opts);
              $file_contents = file_get_contents($params[$key]["url"], false, $context);
            }

            // Write to tmp file
            fwrite($handle, $file_contents);
            fclose($handle);
          } else {
            // Set naively since no data or url given.
            $request->params[$key] = $params[$key];
          }
        } else {
          // Handle everything else
          $request->params[$key] = $params[$key];
        }
      }
    } else {
      // Not following spec, naively set params.
      $request->params = $params;
    }

    return $request;
  }

  public static function run($block, $data = array(), $options = array()) {
    if (is_string($options)){
      $options = array(
        "api_key"=> $options,
        "cache"=> false,
        "expiry"=> null
      );
    }

    if (!isset($options["api_key"])){
      $options["api_key"] = null;
    }

    // Data must be given as an array, or array of arrays (so it can be json_encoded).
    if (self::is_assoc($data)) {
      $json_data = json_encode($data);
    } else {
      throw new Exception("your data needs to be a associative array.");
    }

    # set up API key.
    if (is_null($options["api_key"])){
      if (!is_null(getenv('BLOCKSPRING_API_KEY'))){
        $api_key = getenv('BLOCKSPRING_API_KEY');
      } else {
        $api_key = null;
      }
    } else {
      $api_key = $options["api_key"];
    }
    $api_key_string = $api_key ? "api_key=" . $api_key : "";

    # set up cache flag.
    if (!isset($options["cache"])){
      $cache_string = "&cache=false";
    } else {
      $cache_string = "&cache=" . ($options["cache"] ? "true" : "false");
    }

    # set up expiry flag.
    if (isset($options["expiry"]) && $options["expiry"]){
      $expiry_string = "&expiry=" . (string)$options["expiry"];
    } else {
      $expiry_string = "";
    }

    $block_parts = explode("/", $block);
    $block = end($block_parts);

    $blockspring_url = getenv('BLOCKSPRING_URL') ? getenv('BLOCKSPRING_URL') : 'https://sender.blockspring.com';

    $url = "{$blockspring_url}/api_v2/blocks/{$block}?{$api_key_string}" . $cache_string . $expiry_string;

    $options = array(
      'http' => array(
        'header'  => "Content-type: application/json",
        'method'  => 'POST',
        'content' => $json_data,
        'ignore_errors' => true
      ),
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    try {
      $blockspring_body = json_decode($result, true);
    } catch (Exception $e){}

    if (isset($blockspring_body) == true ){
      return $blockspring_body;
    } else {
      return($result);
    }
  }

  public static function runParsed($block, $data = array(), $options = array()) {
    if (is_string($options)){
      $options = array(
        "api_key"=> $options,
        "cache"=> false,
        "expiry"=> null
      );
    }

    if (!isset($options["api_key"])){
      $options["api_key"] = null;
    }

    // Data must be given as an array, or array of arrays (so it can be json_encoded).
    if (self::is_assoc($data)) {
      $json_data = json_encode($data);
    } else {
      throw new Exception("your data needs to be a associative array.");
    }

    # set up API key.
    if (is_null($options["api_key"])){
      if (!is_null(getenv('BLOCKSPRING_API_KEY'))){
        $api_key = getenv('BLOCKSPRING_API_KEY');
      } else {
        $api_key = null;
      }
    } else {
      $api_key = $options["api_key"];
    }
    $api_key_string = $api_key ? "api_key=" . $api_key : "";

    # set up cache flag.
    if (!isset($options["cache"])){
      $cache_string = "&cache=false";
    } else {
      $cache_string = "&cache=" . ($options["cache"] ? "true" : "false");
    }

    # set up expiry flag.
    if (isset($options["expiry"]) && $options["expiry"]){
      $expiry_string = "&expiry=" . (string)$options["expiry"];
    } else {
      $expiry_string = "";
    }

    $block_parts = explode("/", $block);
    $block = end($block_parts);

    $blockspring_url = getenv('BLOCKSPRING_URL') ? getenv('BLOCKSPRING_URL') : 'https://sender.blockspring.com';

    $url = "{$blockspring_url}/api_v2/blocks/{$block}?{$api_key_string}" . $cache_string . $expiry_string;

    $options = array(
      'http' => array(
        'header'  => "Content-type: application/json",
        'method'  => 'POST',
        'content' => $json_data,
        'ignore_errors' => true
      ),
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    try {
      $blockspring_parsed_results = json_decode($result, true);
      if (self::is_assoc($blockspring_parsed_results)) {
        $blockspring_parsed_results["_headers"] = self::http_parse_headers(implode("\n",$http_response_header));
      } else {
        $blockspring_non_list_json = true;
      }
    } catch (Exception $e){
    }

    if (isset($blockspring_parsed_results) == true){
      if (isset($blockspring_non_list_json)) {
        return $blockspring_parsed_results;
      } else {
        return self::parse($blockspring_parsed_results, true);
      }
    } else {
      return($result);
    }
  }

  public static function define($my_function = null){
    $request = self::processArgs(self::processStdin());
    $response = new BlockspringResponse();

    $my_function($request, $response);
  }

  private function processStdin() {
    // Check if something coming into STDIN.
    if (!posix_isatty(STDIN)) {
      $stdin = '';
      while (false !== ($line = fgets(STDIN))) {
        $stdin .= $line;
      }
      $request = self::parse($stdin, false);
      return $request;
    } else {
      return new BlockspringRequest();
    }
  }

  private function processArgs($request) {
    global $argv;

    $sys_args = array();

    for ($i = 1; $i < count($argv); $i++) {
      if (preg_match('/([^=]*)\=(.*)/', $argv[$i], $match)) {
        $key = (substr($match[1], 0, 2) === "--") ? substr($match[1], 2) : $match[1];
        $sys_args[$key] = $match[2];
      }
    }

    foreach ($sys_args as $key => $val) {
      $request->params[$key] = $val;
    }

    return $request;
  }

  private function is_assoc($array) {
    if (is_array($array)){
      if (empty($array)){
        return true;
      } else {
        $keys = array_keys($array);
        return array_keys($keys) !== $keys;
      }
    } else {
      return false;
    }
  }

  private function http_parse_headers($raw_headers) {
   $headers = array();
   $key = ''; // [+]

   foreach(explode("\n", $raw_headers) as $i => $h)
   {
       $h = explode(':', $h, 2);

       if (isset($h[1]))
       {
           if (!isset($headers[$h[0]]))
               $headers[$h[0]] = trim($h[1]);
           elseif (is_array($headers[$h[0]]))
           {
               // $tmp = array_merge($headers[$h[0]], array(trim($h[1]))); // [-]
               // $headers[$h[0]] = $tmp; // [-]
               $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1]))); // [+]
           }
           else
           {
               // $tmp = array_merge(array($headers[$h[0]]), array(trim($h[1]))); // [-]
               // $headers[$h[0]] = $tmp; // [-]
               $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1]))); // [+]
           }

           $key = $h[0]; // [+]
       }
       else // [+]
       { // [+]
           if (substr($h[0], 0, 1) == "\t") // [+]
               $headers[$key] .= "\r\n\t".trim($h[0]); // [+]
           elseif (!$key) // [+]
               $headers[0] = trim($h[0]);trim($h[0]); // [+]
       } // [+]
   }

   return $headers;
  }
}

class BlockspringRequest {
  public $params = array();
  public $_errors = array();
  public $_headers = array();

  public function getErrors(){
    return $this->_errors;
  }

  public function addError($error){
    array_push($this->_errors, $error);
  }

  public function getHeaders(){
    return $this->_headers;
  }

  public function addHeaders($headers){
    $this->_headers = $headers;
  }
}

class BlockspringResponse {
  public $result = array(
    "_blockspring_spec" => true,
    "_errors" => array()
  );

  public function addOutput($name, $value) {
    $this->result[$name] = $value;
  }

  public function addFileOutput($name, $filepath) {
    // hardcode csv mimetype
    if (pathinfo($filepath, PATHINFO_EXTENSION) == "csv") {
      $mime = "text/csv";
    } else {
      $mime = mime_content_type($filepath);
    }
    $this->result[$name] = array(
      "filename" => pathinfo($filepath, PATHINFO_FILENAME),
      "content-type" => $mime,
      "data" => base64_encode(file_get_contents($filepath))
    );
  }

  public function addErrorOutput($title, $message = null) {
    array_push($this->result["_errors"], array("title" => $title, "message" => $message));
  }

  public function end() {
    echo json_encode($this->result);
  }
}

