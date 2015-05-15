<!DOCTYPE html>
<html>

<?php
$fileName = time() . '-results.txt';
$_POST['file'] = $fileName;
$params = serialize($_POST);
$paramFile = time() . '-params.txt';
file_put_contents ( '../parameters/' . $paramFile , $params );
$command = 'nohup nice -n 10 php Background.php ' . $paramFile . ' > /dev/null &';
$pid = shell_exec(sprintf('%s ', $command));
?>

<head>
    <script src='../js/jquery-1.10.2.min.js' ></script>
    <script>
        ready = false;
        jQuery.noConflict();
        var int = 0;
        jQuery(document).ready(function(){
            //while (ready == false){
            intent = 0;
            int = self.setInterval(function(){
                intent = intent + 1;
                data = new Object();
                data.file = '<?php echo $fileName; ?>';

                ajaxCall("check.php",data, function(response){
                    if (response != 'false'){
                        ready = true;
                        //alert(response.success);
                        document.open();
                        document.write(response);
                        document.close();
                        window.clearInterval(int);
                    }else{
                        /*document.open();
                        document.write(intent.toString());
                        document.close();*/

                    }
                });
            }, 60000);//setTimeout(function() {



            //}

        });

        function ajaxCall(ruta,objParametros,callback){

            jQuery.ajax({
                dataType: "text",
                type: "POST",
                url: ruta,
                async: true,
                data: objParametros,
                success: function (msg) {
                    retorno = msg;
                    callback(msg);
                },

                error: function (xhr, data, thrownError) {
                    //jQuery("#ajaxloader").html("");
                    window.clearInterval(int);
                    alert("ERROR: " + xhr.responseText);
                }
            });
        }
    </script>

    <title>Milestone Based Project - Indicator report</title>
    <meta charset="utf-8">


</head>

<body>
<h1>Calculando resultados, espere por favor</h1>

</body>
</html>
