<?php
include('dbcon.php');
//User Login //
function auth($user_name, $user_pass)
{
    global $conn;
    $query  = "select * from api_users where username = '$user_name' and password = md5($user_pass)";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row              = $result->fetch_assoc();
        $data             = array();
        $data['username'] = $row['username'];
        $data['password'] = $row['password'];
        $data['u_id']     = $row['id'];
        return $data;
    } //$result->num_rows > 0
}
//Get Token on Login //
function get_token($user_id)
{
    global $conn;
    $query  = "select token_id,username, id from api_users where id = '$user_id'";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row              = $result->fetch_assoc();
        $data             = array();
        $data['username'] = $row['username'];
        $data['tokenid']  = $row['token_id'];
        $data['id']       = $row['id'];
        if (isset($data['tokenid'])) {
            return $data;
        } //isset($data['tokenid'])
        return array();
    } //$result->num_rows > 0
    else {
        return array();
    }
}
//Check User for Token //
function get_user($token)
{
    global $conn;
    $query  = "select id from api_users where token_id = '$token'";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row         = $result->fetch_assoc();
        $data        = array();
        $data['uid'] = $row['id'];
        if (isset($data['uid'])) {
            return $data;
        } //isset($data['uid'])
        return array();
    } //$result->num_rows > 0
    else {
        return array();
    }
}
//Generate Token //
function generate_token($user_id)
{
    global $conn;
    $random = rand(10, 100);
    $update = "update api_users set token_id= md5($random) where id = '$user_id' ";
    if ($conn->query($update) == TRUE) {
        return 1;
    } //$conn->query($update) == TRUE
    else {
        return 0;
    }
}
//Logout User //
function delete($user_id)
{
    global $conn;
    $update = "update api_users set token_id='' where id = '$user_id' ";
    if ($conn->query($update) == TRUE) {
        return 1;
    } //$conn->query($update) == TRUE
    else {
        return 0;
    }
}
//Display User List //
function userlist($page)
{
    global $conn;
    $dataresult     = array();
    $items_per_page = 5;
    $offset         = 0;
    if (isset($page) && $page > 1) {
        $offset = ($page - 1) * $items_per_page;
    } //isset($page) && $page > 1
    $query     = "SELECT * FROM students 
        ORDER BY id DESC
        LIMIT $offset, $items_per_page";
    $t_query   = "Select * from students";
    $abc       = $conn->query($t_query);
    $totalpage = $abc->num_rows / $items_per_page;
    $result    = $conn->query($query);
    ;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data              = array();
            $data['id']        = $row['id'];
            $data['firstname'] = $row['firstname'];
            $data['lastname']  = $row['lastname'];
            $data['group']     = $row['group'];
            $data['totalpage'] = round($totalpage);
            $data['start']     = $page;
            array_push($dataresult, $data);
        } //$row = $result->fetch_assoc()
        return $dataresult;
    } //$result->num_rows > 0
    else {
        echo "Record not Found";
    }
}
?>