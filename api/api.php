<?php
include('function.php');
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400'); // cache for 1 day
} //isset($_SERVER['HTTP_ORIGIN'])
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE");
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    exit(0);
} //$_SERVER['REQUEST_METHOD'] == 'OPTIONS'
$postdata = file_get_contents("php://input");
$request  = json_decode($postdata);
$response = array();
$url      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$param    = explode("/", $url);
if (isset($param[2])) {
    switch ($param[2]) { //switch case start
        //login
        case 'auth':
            $result = auth($request->user_name, $request->user_pass);
            if ($result) {
                $token = generate_token($result['u_id']);
                if ($token) {
                    $response['user']    = get_token($result['u_id']);
                    $response['message'] = 'You are Logged In';
                    $response['success'] = 1;
                } //$token
            } //$result
            else {
                $response['message'] = 'Invalid Data';
                $response['success'] = 0;
            }
            break;
        //select data
        case 'users':
            $headers = apache_request_headers();
            $user    = get_user($headers['Authorization']);
            if (!empty($user['uid'])) {
                $response['message'] = 'List of Data';
                $response['success'] = 1;
                if (isset($param[4])) {
                    $response['data'] = userlist($param[4]);
                } //isset($param[4])
                else {
                    $response['data'] = userlist(1);
                }
                $response['total'] = $response['data'][0]['totalpage'];
                $response['start'] = $response['data'][0]['start'];
            } //!empty($user['uid'])
            else {
                $response['message'] = 'You are not Log In';
                $response['success'] = 0;
            }
            break;
        //delete
        case 'delete':
            $headers = apache_request_headers();
            $user    = get_user($headers['Authorization']);
            if (!empty($user['uid'])) {
                $result = delete($request->user_id);
                if ($result) {
                    $response['message'] = 'You are Logged out';
                    $response['success'] = 1;
                } //$result
                else {
                    $response['message'] = 'You are not Log In';
                    $response['success'] = 0;
                }
            } //!empty($user['uid'])
            else {
                $response['message'] = 'You are not Log In';
                $response['success'] = 0;
            }
            break;
    } //$param[2]
} //isset($param[2])
else {
    $response['error']   = true;
    $response['message'] = 'Invalid API Call';
}
echo json_encode($response);
?>