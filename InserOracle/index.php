<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <title>Tarik Data FingerPrint</title>
    </head>
    
    <body>
    <table cellspacing="2" cellpadding="2" border="1">
    <tr align="center">
    <td><B>UserID</B></td>
    <td width="200"><B>Tanggal & Jam</B></td>
    <td><B>Verifikasi</B></td>
    <td><B>Status</B></td>
    </tr>
    <?php

    function Parse_Data($data,$p1,$p2){
    $data=" ".$data;
    $hasil="";
    $awal=strpos($data,$p1);
    if($awal!="") {
    $akhir=strpos(strstr($data,$p1),$p2);
    if($akhir!="") {
    $hasil=substr($data,$awal+strlen($p1),$akhir-strlen($p1));

    }
    }
    return $hasil;
    }

    $IP="192.168.1.201";
    $Key="0";
    $Limit = set_time_limit(86400);
    
    $Connect = fsockopen($IP,"80", $errno, $errstr, $Limit);
    if($Connect){
    //echo "Koneksi sukses";
    $soap_request="<GetAttLog>
    <ArgComKey xsi:type=\"xsd:integer\">".$Key."</ArgComKey>
    <Arg><PIN xsi:type=\"xsd:integer\">All</PIN></Arg>
    </GetAttLog>";

    $newLine="\r\n";
    fputs($Connect, "POST /iWsService HTTP/1.0".$newLine);
    fputs($Connect, "Content-Type: text/xml".$newLine);
    fputs($Connect, "Content-Length: ".strlen($soap_request).$newLine.$newLine);
    fputs($Connect, $soap_request.$newLine);
    $buffer="";
    while($Response = fgets($Connect, 8192)){
    $buffer=$buffer.$Response;

    }
    }

    $buffer=Parse_Data($buffer,"<GetAttLogResponse>","</GetAttLogResponse>");
    $buffer=explode("\r\n",$buffer);
    for($a=0;$a<count($buffer);$a++){
        $data     = Parse_Data($buffer[$a],"<Row>","</Row>");
        $PIN      = Parse_Data($data,"<PIN>","</PIN>");
        $DateTime = Parse_Data($data,"<DateTime>","</DateTime>");
        $Verified = Parse_Data($data,"<Verified>","</Verified>");
        $Status   = Parse_Data($data,"<Status>","</Status>");
    
    if($DateTime===""){
       $time = date('Y-m-d H:i:s');
    }else{
       $time = $DateTime;
    }
        
    $Date   = date_create_from_format('Y-m-d H:i:s',$time);
    $dt     = date_format($Date, 'm/d/Y H:i:s');
    
    echo "<tr align='center'>";
    echo "<td> $PIN </td>";
    echo "<td> $dt </td>";
    //if($Verified==1) $Verified="sidik jari";
    echo "<td> $Verified </td>";
    echo "<td> $Status </td>";
    echo "</tr>"; 
    
    //  koneksi ke Database Oracle
        $username   = "JUP";
        $password   = "JUP2010";
        $dbname     = "192.168.1.21/XE";
        $conn       = oci_connect($username, $password, $dbname);

        if (!$conn) {
            echo "Koneksi ke server database gagal dilakukan";
            exit();
        }else{
//        echo "Koneksi Berhasil";       
        } 
        $cSql = "insert into finger_testphp(pin, datetime, status) values('$PIN','$dt','$Status')";
        if (!$conn){
            echo "Penyimpanan Gagal";
            exit();
        }else {
        $Statement = oci_parse($conn, $cSql) or die("Query Gagal Menyimpan...!!");
        oci_execute($Statement,OCI_DEFAULT);          
        }
        oci_commit($conn);
        

        
/*    
    // insert ke database    
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "absen";
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "INSERT INTO checkinout(pin, datetime, verify, status)
            VALUES('$PIN', '$DateTime', '$Verified', '$Status')";
    if ($conn->query($sql) === TRUE) {
       // echo "New record created successfully";
        } else {
          echo "Error: " . $sql . "<br>" . $conn->error;
        }
    $conn->close();
    
*/    
    }
        oci_free_statement($Statement);
        oci_close($conn);       
   
    echo "</table>";

    
    
    ?>
    </body>
</html>
