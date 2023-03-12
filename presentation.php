<?php
require_once '../../../wp-config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
?>


<html>
<head>
<title>Outdoor IASD Mirandópolis</title>
	<meta name="robots" content="noindex,nofollow" />
	<meta name="googlebot-news" content="noindex,nofollow" />
	<meta name="googlebot" content="noindex,nofollow">
	<meta name="googlebot-news" content="nosnippet">
    <meta name="viewport" content="width=device-width, initial-scale=1">

	<script src='assets/js/jquery/jquery-1.11.1.min.js'></script>
	<script src='assets/js/jquery/jquery_ui/jquery-ui.min.js'></script>


	<script>
	var i_media;

	function openFullscreen() {
	var elem = document.getElementById("body");
	  if (elem.requestFullscreen) {
		elem.requestFullscreen();
	  } else if (elem.webkitRequestFullscreen) { /* Safari */
		elem.webkitRequestFullscreen();
	  } else if (elem.msRequestFullscreen) { /* IE11 */
		elem.msRequestFullscreen();
	  }
	}

	$(document).ready(function() {
		setInterval(processaMedia, 1000);
		$(".list_media").html($("#list_media").html());
	});
	function processaMedia(){
		if ($("#bt_start").length > 0){
			return false;
		}
		if (!$('body').hasClass('outdoor')){
			return false;
		}
		var indx = $("#list_media").attr("indx");
		if ($("#list_media li[data-indx="+indx+"]").length <= 0){
			$("#list_media").attr("indx",0);
			indx = 0;
		}
		$item = $("#list_media li[data-indx="+indx+"]");


		if ($item.hasClass("start")){
			return false;
		}
		if ($item.hasClass("end")){
			$("#list_media").attr("indx",parseInt(indx)+1);
			$item.removeClass();
			return false;
		}

		$item.addClass("active");
		$item.addClass("start");

		var url = $item.attr("data-url");
		var duration = $item.attr("data-duration");
		var fit = $item.attr("data-fit");
		var mime = $item.attr("data-mime");
		var type = mime.split('/')[0];

		if (type == "image"){
			$("#media").html("<img src='"+url+"' style='object-fit: "+fit+";'>");
			setTimeout(function(){
				$item.addClass("end");
				$item.removeClass("start");
			}, duration * 1000);

		}else if (type == "video"){

			$("#media").html(""
					+"<video c_ontrols autoplay muted>"
					+"<source src='"+url+"' type='"+mime+"'>"
					+"Navegador não suporta vídeos..."
			);

			$("#media video").on('ended',function(){
				$item.addClass("end");
				$item.removeClass("start");
			});

			i_media = setInterval(function(){
				$("#media video").trigger("play");
				//console.log("posição atual:", $("#media video")[0].currentTime);
				//console.log("duração:", $("#media video")[0].duration);
				if ($("#media video")[0].paused != true){
					clearInterval(i_media);
				}
			}, 100);


		}else{

			$item.addClass("end");
			$item.removeClass("start");

		}

		//console.log($("#list_media").html());
	}
	</script>

	<style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@100;400&display=swap');

	html,body{
		background:#000;
		border:0;
		margin:0;
		padding:0;
		overflow: hidden;
        font-family: 'Roboto', sans-serif;
        color: #FFF;
	}
	#media img,#media video{
		width:100%;
		height:100%;
		border:0;
		margin:0;
		padding:0;
	}
	#media img{
		object-fit: contain;
	}

    #bt_start{
        display: flex;
        align-items: center;
        flex-direction: column;
        justify-content: center;
        height: 100vh;
    }

	#bt_start a{
		border: 1px solid #FFF;
		margin: 5px;
		padding: 10px 30px;
		background: #000;
		color: #FFF;
		font-family: system-ui;
		text-decoration: none;
	}
	#bt_start a:hover{
		background:#FFF;
		color:#000;
	}

    #bt_start .list_media{
        width: 100%;
        display: flex;
        align-items: stretch;
        justify-content: center;
        flex-wrap: wrap;
        overflow: auto;
    }
    #bt_start .list_media li{
        margin: 10px;
        text-align: center;
        display: flex;
        background: #111;
        padding: 5px;
        flex-direction: column;
        justify-content: center;
    }
    #bt_start .list_media li div{
        padding: 5px;
    }
	</style>


</head>
<body id="body">

    <div id="bt_start">
        <div style="padding: 25px;">
            <a href="javascript:" onClick="openFullscreen();$('body').addClass('outdoor');$('#bt_start').remove();">Iniciar Outdoor</a>
        </div>
        <div class="list_media">
        </div>
    </div>

    <div id="media"></div>

    <ul id="list_media" style="display:none;" indx=0>
    <?php
$sql = "
        SELECT
            outdoor.outdoor_id,
            posts.post_title,
            posts.post_name,
            posts.post_mime_type,
            posts.guid,
            outdoor.outdoor_status,
            outdoor.outdoor_order,
            outdoor.outdoor_start_date,
            outdoor.outdoor_end_date,
            outdoor.outdoor_object_fit,
            outdoor.outdoor_duration
        FROM " . $table_prefix . "outdoor outdoor
        INNER JOIN " . $table_prefix . "posts posts ON posts.id = outdoor.post_id
        WHERE 1=1
            AND outdoor.outdoor_status='active'
            AND (outdoor.outdoor_start_date IS NULL OR outdoor.outdoor_start_date = '0000-00-00' OR outdoor.outdoor_start_date <= DATE(NOW()))
            AND (outdoor.outdoor_end_date IS NULL OR outdoor.outdoor_end_date = '0000-00-00' OR outdoor.outdoor_end_date >= DATE(NOW()))
        ORDER BY outdoor.outdoor_order
    ";

$result = $wpdb->get_results($sql);
$i = 0;
foreach ($result as $linha) {
    $max_w = 100;
    $max_h = 70;
    $mime = explode('/', $linha->post_mime_type);
    $embed = '';
    $url = $linha->guid;
    $fit = $linha->outdoor_object_fit;
    switch ($mime[0]) {
        case 'image':
            $embed = "<img src='{$url}' style='width:{$max_w}px;height:{$max_h}px;object-fit: {$fit};'>";
            break;
        default:
            $embed = "<img src='img/video.png' style='width:{$max_w}px;height:{$max_h}px;object-fit: contain;background:#F0F0F1;padding:5px;'>";
            /*$embed = "
                    <video muted style='max-width:{$max_w}px;max-height:{$max_h}px;'>
                    <source src='{$url}' type='{$item['post_mime_type']}'>
                ";*/
            break;
    }

    echo "<li data-indx=$i 
        data-name='" . @$linha->post_title . "' 
        data-url='" . @$linha->guid . "' 
        data-duration='" . @$linha->outdoor_duration . "'
        data-mime='" . @$linha->post_mime_type . "'
        data-fit='" . @$linha->outdoor_object_fit . "'
    >";
    echo "<div>" . @$embed . "</div>";
    echo "<div>" . @$linha->post_title . "</div>";
    echo "</li>";
    $i++;
}
?>
    </ul>

</body>
</html>