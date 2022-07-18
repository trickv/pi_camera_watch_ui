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
        error("Failed sql:(  " . $sql . mysql_error());
    }
    header("HTTP/1.1 200 OK");
    header("Content-Type: image/webp");
    print(hex2bin($row[0]));
} else {
    header("HTTP/1.1 200 OK");
    print("<html><body>");
    print("<h1>Images</h1>");

    $sql = "SELECT timestamp, ROUND((NOW() - timestamp)/3600,1) AS age, ROUND(LENGTH(image) / 1024 / 1024 * 8, 1) AS cost_c, MD5(image), length(image) FROM image ORDER BY timestamp DESC LIMIT 10";
    $result = mysql_query($sql);
    if (!$result) {
        error("Failed to query :(  " . $sql . mysql_error());
    }
    print("<table><tr><th>timestamp</th><th>age</th><th>cost (cents)</th><th>md5</th><th>len</th></tr>");
    while ($row = mysql_fetch_row($result)) {
        print("<tr>");
        $index = 0;
        foreach ($row as $col) {
            print("<td>");
            if ($index == 0) {
                print("<a href=\"?timestamp=" . $col . "\">");
            }
            print($col);
            if ($index == 0) {
                print("</a>");
            }
            print("</td>");
            $index++;
        }
    }
    print("</table>");

    print("<h1>hourly beacon</h1>");
    $sql = "SELECT timestamp, ROUND((NOW() - timestamp)/3600,1) AS age FROM log ORDER BY timestamp DESC LIMIT 10";
    $result = mysql_query($sql);
    if (!$result) {
        error("Failed to sql :(  " . $sql . mysql_error());
    }
    print("<table><tr><th>timestamp</th><th>age</th></tr>");
    while ($row = mysql_fetch_row($result)) {
        print("<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>");
    }
    print("</table>");

    print("</body></html>");
}
