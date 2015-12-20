<?
/*
This is the web interface. Feel free to change or ask why I did something the way I did it.
It's a mess below. I didn't split this into a CSS file and the rest, because I wanted to have it done
as soon as possible. Feel free to do it whenever you wish to.
*/

session_start();
$code="1234";

if ($_GET['authcode'] == $code){ $_SESSION['auth']=1; }

@header("refresh:60;url=/");

mysql_connect("localhost", "root", "a");
mysql_select_db("home");

if ($_GET['cmd'] == "up_temp"){
    $query=mysql_query("update heating set dtemp=dtemp+0.5 where id=".$_GET['room']);
} elseif ($_GET['cmd'] == "down_temp"){
    $query=mysql_query("update heating set dtemp=dtemp-0.5 where id=".$_GET['room']);
}

function default_display($room){
    $q = mysql_query("select * from heating where id = ".$room);
    $ret = "
    <table width='600' cellpadding='0' cellspacing='0' style='border: 1px solid #fff; margin:auto;'>";
    while ($row = mysql_fetch_array($q)){
        $ret .= '
        <tr>
            <td colspan="3"><h1>' . $row['room'] . '</h2></td>
        </tr>
        <tr>
            <td><h3>Last read temperature</h3></td>
            <td><h2>' . $row['rtemp'] . '&deg; C</h2></td>
            <td><h3>(at ' . $row['rtemp_time'] . ')</h3></td>
        </tr>
        <tr>
            <td><h3>Desired temperature</h3></td>
            <td><h2>' . $row['dtemp'] . ' &deg; C</h2></td>
            <td>
                <input type="button" onclick="location.href=\'?room='.$room.'&cmd=up_temp\';" value="&#9650;" class="buttonUP">
                <input type="button" onclick="location.href=\'?room='.$room.'&cmd=down_temp\';" value="&#9660;" class="buttonDOWN">
            </td>
        </tr>
        <tr>
            <td><h3>Room status</h3></td>
            <td colspan="2"><h2>';

            // if i didn't get a temperature reading in the last 5 mins, the raspberry pi from that room is offline
            $q1 = mysql_query("SELECT (unix_timestamp(now())-unix_timestamp(rtemp_time)) FROM heating WHERE id=".$room);
            $timedif = mysql_fetch_array($q1);
            if ($timedif[0] > 300) {
                $ret .= '<font class="buttonDOWN">OFFLINE</font>';
            } else {
                $ret .= '<font class="buttonUP">ONLINE</font>';
            }
        $ret .= '
        </tr>
        <tr>
            <td><h3>Heating status</h3></td>
            <td colspan="2"><h2>';
            if (($row['dtemp'] - $row['rtemp']) < 1){
                $ret .= '<font class="buttonDOWN">OFF</font>';
            } else {
                $ret .= '<font class="buttonUP">ON</font>';
            }
        $ret .= '
        </tr>';
    }
    $ret .= '
    </table>';
    return $ret;
}

function generate_top_buttons(){
    $q = mysql_query("select * from heating where circuit > -1 order by circuit ASC");
    $ret = "";
    while ($row = mysql_fetch_array ($q)){
        $ret .= '<input type="button" onclick="location.href=\'?room=' . $row['id'] .'\'" value="' . $row['room'] . '(' . $row['rtemp'] . '&deg; C)"';
        if (($row['dtemp'] - $row['rtemp']) >= 1){
            $ret .= ' class="buttonTopMenuOn">';
        } else {
            $ret .= ' class="buttonTopMenuOff">';
        }
    }

    return $ret;
}

?>
<html>
<head>
    <style>
    #grad {
        background: #999999; /* For browsers that do not support gradients */
        background: radial-gradient(#666666, #333, #000); /* Standard syntax */
    }

    a {
        color: #fff;
        text-decoration: none;
    }

    h1 {
        font-family: Verdana;
        font-size: 25px;
        font-weight: none;
        color: orange;
        margin-left: 10px;
        margin-top: 10px;
        margin-bottom: 20px;
    }

    h2 {
        font-family: Verdana;
        font-size: 18px;
        color: #fff;
        margin-left: 5px;
    }

    h3 {
        font-family: Verdana;
        font-size: 14px;
        color: #fff;
        margin-left: 5px;
        margin-top: 0px;
        margin-bottom: 0px;
    }

    .buttonTopMenuOff {
        -moz-box-shadow: 0px 10px 14px -7px #3e7327;
        -webkit-box-shadow: 0px 10px 14px -7px #3e7327;
        box-shadow: 0px 10px 14px -7px #3e7327;
        background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #77b55a), color-stop(1, #72b352));
        background:-moz-linear-gradient(top, #77b55a 5%, #72b352 100%);
        background:-webkit-linear-gradient(top, #77b55a 5%, #72b352 100%);
        background:-o-linear-gradient(top, #77b55a 5%, #72b352 100%);
        background:-ms-linear-gradient(top, #77b55a 5%, #72b352 100%);
        background:linear-gradient(to bottom, #77b55a 5%, #72b352 100%);
        filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#77b55a', endColorstr='#72b352',GradientType=0);
        background-color:#77b55a;
        -moz-border-radius:5px;
        -webkit-border-radius:5px;
        border-radius:5px;
        border:1px solid #fff;
        display:inline-block;
        cursor:pointer;
        color:#ffffff;
        font-family:Verdana;
        font-size:13px;
        font-weight:bold;
        padding:10px 10px;
        margin: 5px;
        text-decoration:none;
        text-shadow:0px 1px 0px #5b8a3c;
    }

    .buttonTopMenuOn {
        -moz-box-shadow: 0px 10px 14px -7px #cf866c;
        -webkit-box-shadow: 0px 10px 14px -7px #cf866c;
        box-shadow: 0px 10px 14px -7px #cf866c;
        background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #d0451b), color-stop(1, #bc3315));
        background:-moz-linear-gradient(top, #d0451b 5%, #bc3315 100%);
        background:-webkit-linear-gradient(top, #d0451b 5%, #bc3315 100%);
        background:-o-linear-gradient(top, #d0451b 5%, #bc3315 100%);
        background:-ms-linear-gradient(top, #d0451b 5%, #bc3315 100%);
        background:linear-gradient(to bottom, #d0451b 5%, #bc3315 100%);
        filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#77b55a', endColorstr='#72b352',GradientType=0);
        background-color:#cf866c;
        -moz-border-radius:5px;
        -webkit-border-radius:5px;
        border-radius:5px;
        border:1px solid #fff;
        display:inline-block;
        cursor:pointer;
        color:#ffffff;
        font-family:Verdana;
        font-size:13px;
        font-weight:bold;
        padding:10px 30px;
        text-decoration:none;
        text-shadow:0px 1px 0px #5b8a3c;
    }

    .buttonUP {
        background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #77d42a), color-stop(1, #5cb811));
        background:-moz-linear-gradient(top, #77d42a 5%, #5cb811 100%);
        background:-webkit-linear-gradient(top, #77d42a 5%, #5cb811 100%);
        background:-o-linear-gradient(top, #77d42a 5%, #5cb811 100%);
        background:-ms-linear-gradient(top, #77d42a 5%, #5cb811 100%);
        background:linear-gradient(to bottom, #77d42a 5%, #5cb811 100%);
        filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#77d42a', endColorstr='#5cb811',GradientType=0);
        background-color:#77d42a;
        -moz-border-radius:5px;
        -webkit-border-radius:5px;
        border-radius:5px;
        border:1px solid #268a16;
        display:inline-block;
        cursor:pointer;
        color:#fff;
        font-family:Verdana;
        font-size:15px;
        font-weight:bold;
        padding:10px 10px;
        text-decoration:none;
    }

    .buttonDOWN {
        background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #d0451b), color-stop(1, #bc3315));
        background:-moz-linear-gradient(top, #d0451b 5%, #bc3315 100%);
        background:-webkit-linear-gradient(top, #d0451b 5%, #bc3315 100%);
        background:-o-linear-gradient(top, #d0451b 5%, #bc3315 100%);
        background:-ms-linear-gradient(top, #d0451b 5%, #bc3315 100%);
        background:linear-gradient(to bottom, #d0451b 5%, #bc3315 100%);
        filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#d0451b', endColorstr='#bc3315',GradientType=0);
        background-color:#d0451b;
        -moz-border-radius:5px;
        -webkit-border-radius:5px;
        border-radius:5px;
        border:1px solid #942911;
        display:inline-block;
        cursor:pointer;
        color:#ffffff;
        font-family:Verdana;
        font-size:15px;
        padding:10px 10px;
        text-decoration:none;
    }

    .buttonLogin {
        background-color:transparent;
        -moz-border-radius:5px;
        -webkit-border-radius:5px;
        border-radius:5px;
        border:1px solid #dcdcdc;
        display:inline-block;
        cursor:pointer;
        color:#fff;
        font-family:Tahoma;
        font-size:45px;
        font-weight:bold;
        padding:5px 20px;
        text-decoration:none;
        margin: 5px;
    }

    .buttonLoginLong {
        background-color:transparent;
        -moz-border-radius:5px;
        -webkit-border-radius:5px;
        border-radius:5px;
        border:1px solid #dcdcdc;
        display:inline-block;
        cursor:pointer;
        color:#fff;
        font-family:Tahoma;
        font-size:45px;
        font-weight:bold;
        padding:5px 60px;
        text-decoration:none;
        margin: 5px;
    }

    body {
        margin: 0;
        padding: 0;
        border: 0;
        outline: 0;
    }
    </style>
    <title>Home heat control</title>
</head>
<body bgcolor="#000" text="#fefefe">
<table width="100%" height="100%" style="align: center; border: 1px solid #999;" cellspacing="0">
<tr>
    <td valign="middle" width="100%" height="40" style="border-bottom: 1px dashed;" colspan="2">
    <center>
        <?=generate_top_buttons();?>
    </center>
    </td>
</tr>
<tr id="grad">
    <td colspan="2">
    <table width="90%" cellpadding="0" cellspacing="0" border="0" align="center" valign="middle">
    <tr>
        <td>
<?
    if (($_GET['cmd'] == 'login') || (($_GET['authcode']) && ($_GET['authcode'] != $code))){
        $_SESSION['auth'] = '';
        echo '<center>';
        for ($i=1; $i<10; $i++){
            echo '<input type="button" onclick="location.href=\'/?authcode='.$_GET['authcode'].$i.'\';" value="'.$i.'" class="buttonLogin">';
            if ($i%3 == 0) echo '<br/>';
        }
        echo '<input type="button" onclick="location.href=\'/?authcode='.$_GET['authcode'].'0\';" value="0" class="buttonLogin">';
        echo '<input type="button" onclick="location.href=\'/?\';" value="C" class="buttonLoginLong">';

    } else {
        if ((!$_GET['room']) || ($_SESSION['auth'] == '')){
?>
    <div id="time" style="font-family: Comic Sans MS; font-size: 120px; text-align: center;"></div>
    <script type="text/javascript">
    function checkTime(i) {
        if (i < 10) {
            i = "0" + i;
        }
        return i;
    }

    function startTime() {
        var today = new Date();
        var h = today.getHours();
        var m = today.getMinutes();
        var s = today.getSeconds();
        // add a zero in front of numbers<10
        m = checkTime(m);
        s = checkTime(s);
        document.getElementById('time').innerHTML = h + ":" + m + ":" + s;
        t = setTimeout(function () {
            startTime()
        }, 500);
    }
    startTime();
    </script>
<?
        } else {
            echo default_display($_GET['room']);
        }
    }
?>
        </td>
    </tr>
    </table>
    </td>
</tr>
<tr>
    <td align="center" style="border-top: 1px dashed;">
<?
if (($_SESSION['auth'] == '') || (!isset($_SESSION['auth']))){
?>
        <input type="button" onclick="location.href='?cmd=login';" value="Login" class="buttonTopMenuOn">
<?
} else {
?>
        <input type="button" onclick="location.href='?cmd=login';" value="Logout" class="buttonTopMenuOn">
<?
}
?>
    </td>
    <td align="center" valign="middle" height="50" style="border-top: 1px dashed;">
        <?=date("l, d F Y, H:i");?>
    </td>
</tr>
</table>

</body>
</html>
