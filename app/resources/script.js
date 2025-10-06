var URL_MICROSERVICE_1 = "http://127.0.0.2";
var URL_MICROSERVICE_2 = "http://127.0.0.3";
var URL_MICROSERVICE_3 = "http://127.0.0.5";

/**
 * Creates the initial tables in the DB and refresh the logs
 */
window.onload = function()
{
    //We create the data bases and tables
    var response1 = JSON.parse(sendRequest(URL_MICROSERVICE_1 + "/users/createTable"));

    if(response1["error"]["code"] == 1)
    {
        console.log(response1["error"]["message"]);
        return;
    }

    var response2 = JSON.parse(sendRequest(URL_MICROSERVICE_2 + "/emails/createTable"));

    if(response2["error"]["code"] == 1)
    {
        console.log(response2["error"]["message"]);
        return;
    }

    refreshLogs();

    //We launch to the comsumer
    var xhr = new XMLHttpRequest();
    xhr.open("GET", URL_MICROSERVICE_3, true);
    xhr.send();
}

/**
 * Refresh the logs
 */
function refreshLogs()
{
    var iframe = document.getElementById('msUsersLogs');
    iframe.src = URL_MICROSERVICE_1 + "/log/logs.log?rand=" + Date.now();

    var iframe2 = document.getElementById('msEmailsLogs');
    iframe2.src = URL_MICROSERVICE_2 + "/log/logs.log?rand=" + Date.now();
}

/**
 * Reset users logs
 */
document.getElementById("resetLogsUsersMs").onclick = function()
{
    sendRequest(URL_MICROSERVICE_1 + "/resetLogs");
    refreshLogs();
}

/**
 * Reset emails logs
 */
document.getElementById("resetLogsEmailsMs").onclick = function()
{
    sendRequest(URL_MICROSERVICE_2 + "/resetLogs");
    refreshLogs();
}

/**
 * Sends request by Ajax
 */
function sendRequest($url)
{
    var xhr = new XMLHttpRequest();
    xhr.open("GET", $url, false);
    xhr.send();

    // stop the engine while xhr isn't done
    for(; xhr.readyState !== 4;)
    {
        if (xhr.status !== 200) 
        {
            console.warn('request_error');
        }
    }
    return xhr.responseText;
}

/**
 * Reset emails logs
 */
document.getElementById("createUser").onclick = function()
{
    var userName = document.getElementById('name').value;
    var userEmail = document.getElementById('email').value;

    const emailRegex = new RegExp("^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$");

    if(userName == "")
    {
        alert("Please write a valid name");
        return;
    }
    else if(!emailRegex.test(userEmail))
    {
        alert("Please write a valid email");
        return;
    }

    const xhttp = new XMLHttpRequest();
    xhttp.open("POST", URL_MICROSERVICE_1 + "/users/createNewUser");
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    xhttp.onload = function() 
    {
        var response = JSON.parse(this.responseText);

        if(response["error"]["code"] === 1)
        {
            console.log(response["error"]["message"]);
            return;
        }

        var html = response["data"]["message"]+"<br><br>";
        html += response["data"]["name"]+"<br>";
        html += response["data"]["email"];

        document.getElementById("result2").innerHTML = html;
        refreshLogs();
    }

    xhttp.send("name="+userName+"&email="+userEmail);
}

/**
 * List all emails every 3 seconds
 */
setInterval(function()
{
    var response = JSON.parse(sendRequest(URL_MICROSERVICE_2 + "/emails"));

    refreshLogs();

    if(response["error"]["code"] === 1)
    {
        console.log(response["error"]["message"]);
        return;
    }
    
    var table = "";

    response["emails"].forEach(function (emails) 
    {
        table += "<tr>";
        table += "  <td>"+emails.email+"</td>";
        table += "  <td>"+emails.date+"</td>";
        table += "  <td>Sended</td>";
        table += "</tr>";
    });

    document.getElementById("emails").innerHTML = table;
},5000);