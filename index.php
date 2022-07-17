<?php

function error($message) {
    header("HTTP/1.1 500 Internal Server Error");
    die($message);
}

mysql_connect('localhost', 'remotecam', 'remotecam') or error(mysql_error());
mysql_select_db('remotecam') or error(mysql_error());

if ($_REQUEST['timestamp']) {
    $sql = "SELECT HEX(image) FROM image WHERE timestamp = '" . mysql_escape_string($_REQUEST['timestamp']) . "'";
    $result = mysql_query($sql);
    if (!$result) {
        error("Failed sql:(  " . $sql . mysql_error());
    }
    $row = mysql_fetch_row($result);
    if (!$row) {
        error("Failed sql:(  " . $sql);
    }
    header("HTTP/1.1 200 OK");
    header("Content-Type: image/webp");
    print(hex2bin($row[0]));
} else {
    header("HTTP/1.1 200 OK");
    print("<html><body>");

    $sql = "SELECT timestamp, md5(image) FROM image LIMIT 10";
    $result = mysql_query($sql);
    if (!$result) {
        error("Failed to insert :(  " . $sql);
    }
    print("<table><tr><th>timestamp</th><th>md5</th></tr>");
    while ($row = mysql_fetch_row($result)) {
        print("<tr><td><a href='?timestamp=" . $row[0] . "'>" . $row[0] . "</a></td><td>" . $row[1] . "</td></tr>");
    }
    print("</table>");

    print("<h1>hourly beacon</h1>");
    $sql = "SELECT timestamp, now() FROM log order by timestamp desc LIMIT 10";
    $result = mysql_query($sql);
    if (!$result) {
        error("Failed to insert :(  " . $sql);
    }
    print("<table><tr><th>timestamp</th><th>md5</th></tr>");
    while ($row = mysql_fetch_row($result)) {
        print("<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>");
    }
    print("</table>");

    print("</body></html>");
}
