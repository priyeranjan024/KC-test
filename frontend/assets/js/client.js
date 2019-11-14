//Api Base url
var apiHost = "http://192.168.1.141/loginapi/";
var token=undefined;
// Authentication 
function AuthLogin() {

    token=undefined;
    var param = {
        user_name: $('#userName').val(),
        user_pass: $('#userPass').val()
    };
	
	// remember Me settings
	rememberMe(param);
	 
	 // Login Api 
     ajaxCall('auth', param, 'POST').then(function(data) { // login success
            saveCredentials(data.user);
            location.href = "users.html";
    },function(err){
        deleteCredentials();
        if (err) {
            $("#errorMsg").empty();
            $("#errorMsg").append(err)
        } else {
           location.href = "login.html";
        }
    });
}


// Get all users with respect to Authorize token
function getUsers(_start) {
	// checking localstorage to validate api with userdetais and token
    if (localStorage.getItem('userData') != null) {
        var userDetail = JSON.parse(localStorage.getItem('userData'));
        token= userDetail.tokenid;
        var endPoint = "users/page/"+_start;
        var param={
            "page":_start
        }
        ajaxCall(endPoint, null, 'GET').then(function( data) {
			
            // called when api gets success
            pagination(data);
			// clearing old user list data
            $("#userList").empty();
			
			// creating new users data
            $.each(data.data, function(index, items) {

                var li = "<li><div class='row user-row'><div class='col-md-2 col-sm-2 col-xs-2 text-right'>" +
                    "<i class='fa fa-check-circle userchecked' aria-hidden='true'></i>" +
                    "</div>" +
                    "<div class='col-md-6 col-sm-6 col-xs-6'>" +
                    "    <p class='user-name'>" + items.firstname + items.lastname+ "</p>" +
                    "    <p class='user-data'>" + items.firstname + " " + items.lastname + "</p>" +
                    "</div>" +
                    "<div class='col-md-4 col-sm-4 col-xs-4'>" +
                    "        <i class='fa fa-ellipsis-h more-detail' aria-hidden='true'></i>" +
                    "        <p class='mb-0'>" + items.group + "</p>" +
                    "</div>" +
                    "</div>" +
                    " </li>";

				// Append each user in list
                $("#userList").append(li);
            });
        },function(err){
            // if token expired or any issue in response
            if (err) {
                if (err.status === 498) {
                    alert(err.statusText)
                }
               // location.href = "login.html";
                return false;
            }
        })
    } else {
        location.href = "login.html";
    }
}

// pagination for user list
function pagination(data) {
    $("#userPaging").empty();

    var li = "";

    for (index = 0; index < data.total; index++) {
        var isActive = data.start-1 == index ? 'active' : '';

        if (isActive != "") {
            li = li + "<li><a href='#' class=" + isActive + ">" + Number(index + 1) + "</a></li>";
        } else {
            li = li + "<li><a href='#' onClick='getUsers(" + Number(index + 1)+ ")'>" + Number(index + 1) + "</a></li>";
        }

    }
    if (data.start < data.total - 1) {
        li = li + "<li><a href='#' onClick='getUsers(" + Number(data.start + 1) + ")'>Next <i class='fa fa-angle-double-right' aria-hidden='true'></i></a></li>";
    } else {
        li = li + "<li><a href='#' >Next <i class='fa fa-angle-double-right' aria-hidden='true'></i></a></li>";
    }

    $("#userPaging").append(li);

}

function logout() {

    if (localStorage.getItem('userData') != null) {
        var userDetail = JSON.parse(localStorage.getItem('userData'));
        token: userDetail.tokenid
        var param = {
            "user_id":userDetail.id
        };
        ajaxCall('delete', param, 'DELETE').then(function( data) {
                deleteCredentials();
                location.href = "login.html";
        },function (err){
            deleteCredentials();
            location.href = "login.html";
        });

    }
}

function saveCredentials(data) {
    localStorage.setItem('userData', JSON.stringify(data));
}

function deleteCredentials() {
    localStorage.removeItem('userData');
}

function rememberMe(param){
	
 if ($('#remember_me').is(':checked')) {
			// save username and password
			localStorage.username = param.user_name;
			localStorage.pass = param.user_pass;
			localStorage.chkbx = $('#remember_me').val();
		} else {
			localStorage.username = '';
			localStorage.pass = '';
			localStorage.chkbx = '';
		}	
	
}

// endpoint end point
// params if data or null
// methods GET, PUT, POST 

function ajaxCall(endpoint, params, method){
    return new Promise((resolve, reject) => {
        //asynchronous code goes here
        $.ajax({
            url: apiHost + endpoint,
            type: method,
            dataType: 'JSON',
            ContentType:"application/json",
            beforeSend: function(request) {
                request.setRequestHeader("Authorization", token);
              },
            data:params == null?'':JSON.stringify(params),
            success: function(data) {
                if (data && data.success === 1)
                resolve(data)
                else
                reject(data.message)
            },
            error: function(err) {
                reject(err);
            },
        });
      });
}

