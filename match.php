
<!DOCTYPE html>
 <html>
 <head>
     <title></title>
     
     
    
     <link rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous"> 
  <link href="https://fonts.googleapis.com/css?family=Lato:400,700|Open+Sans:400,600,700|Roboto:400,500,700|Saira:400,600" rel="stylesheet">
  
   
  <link rel="stylesheet" type="text/css" href="style.css">
 </head>
<body>
<div class="fl"></div>
<?php


$apiKey = '';
$servers = 'eun1';
$input = str_replace(' ', '', filter_input(INPUT_GET, 'summoner'));
$cid = json_decode(file_get_contents('champion.json') , true);

if (strlen($input) > 2)
    {
    function curl($servers, $api, $id, $apiKey)
        {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $servers . '.api.riotgames.com/lol/' . $api . $id . '?api_key=' . $apiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
        }

    function getIconId($iconId)
        {
        global $cid;
        foreach($cid['data'] as $champ)
            {
            if ($champ['key'] == $iconId)
                {
                return $champ['id'];
                break;
                }
            }
        }

    function getIcon($iconId)
        {
        return "img/" . getIconId($iconId) . ".png";
        }

    function table($data, $apiKey, $servers, $sid, $rank)
        {
        global $teams;
        echo "<div class=\"table-container \">";

        // TABLE 1

        echo "<table class=\"t2 animated bounceInDown  \">";
        for ($i = 0; $i < 5; $i++)
            {
            print_r("<tr class=\"tr" . $i . " animated bounceInLeft\"><td><img class=\"icon \" src=\"" . getIcon($data['participants'][$i]['championId']) . "\"></img>" . "</td><td class\"td\" ><p>" . $data['participants'][$i]['summonerName'] . "<hr><br /><img class=\"tier\" src=\"base-icons/" . strtolower((count($rank[$i]) == 0 ? "unranked" : $rank[$i][0]['tier'])) . ".png\">" . (count($rank[$i]) == 0 ? "unranked " : $rank[$i][0]['tier'] . " " . $rank[$i][0]['rank'] . " (" . $rank[$i][0]['leaguePoints'] . " LP)") . "</p></td></tr>");
            }

        print_r("</table>");

        // TABLE 2

        echo "<table class=\"t1 animated bounceInDown\">";
        for ($i = 5; $i < 10; $i++)
            {
            echo "<tr class=\"tr" . ($i - 5) . " animated bounceInRight\"><td class\"td\"><p>" . $data['participants'][$i]['summonerName'] . "<hr><br />" . (count($rank[$i]) == 0 ? "unranked" : $rank[$i][0]['tier'] . " " . $rank[$i][0]['rank']) .(count($rank[$i]) == 0 ? " " : " (" . $rank[$i][0]['leaguePoints'] . " LP)") . "<img class=\"tier\" src=\"base-icons/" . strtolower((count($rank[$i]) == 0 ? "unranked" : $rank[$i][0]['tier'])) . ".png\">"."</p></td><td>" . "<img class=\"icon \" src=\"" . getIcon($data['participants'][$i]['championId']) . "\"></img></td></tr>";
            }

        echo "</table></div>";
        }

    function request($servers, $apiKey, $data)
        {
        $url = [];
        for ($i = 0; $i < 10; $i++)
            {
            array_push($url, 'https://' . $servers . '.api.riotgames.com/lol/league/v3/positions/by-summoner/' . $data['participants'][$i]['summonerId'] . '?api_key=' . $apiKey);
            }

        if (!function_exists('curl_init'))
            {
            die('sorry cURL is not installed!');
            }

        $ch = [];
        for ($i = 0; $i < sizeof($url); $i++)
            {
            $init = curl_init();
            curl_setopt($init, CURLOPT_URL, $url[$i]);
            curl_setopt($init, CURLOPT_RETURNTRANSFER, 1);
            array_push($ch, $init);
            }

        $mh = curl_multi_init();
        foreach($ch as $a)
            {
            curl_multi_add_handle($mh, $a);
            }

        $running = null;
        do
            {
            curl_multi_exec($mh, $running);
            }

        while ($running);
        foreach($ch as $a)
            {
            curl_multi_remove_handle($mh, $a);
            }

        curl_multi_close($mh);
        $res = [];
        foreach($ch as $a)
            {
            array_push($res, json_decode(curl_multi_getcontent($a) , true));
            }

        return $res;
        }

    $sid = @curl($servers, 'summoner/v3/summoners/by-name/', $input, $apiKey) ['id'];
    if (count($sid) == 0)
        {
        print_r("<h1 class=\"h1\">SUMMONER NOT FOUND</h1>");
        }
      else
        {
        $data = @curl($servers, 'spectator/v3/active-games/by-summoner/', $sid, $apiKey);
        if (@count($data['participants']) == 0)
            {
            print_r("<h1 class=\"h1\">SUMMONER NOT IN GAME</h1>");
            }
          else
            {
            $rank = request($servers, $apiKey, $data);
            table($data, $apiKey, $servers, $sid, $rank);
            }
        }
    }
  else
    {
    echo "string";
    }

?>

 
</body>
</html>