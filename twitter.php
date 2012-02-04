<?php
/**
 * @licence GPLv2 , v3 , BSD , Public Domain, anything else ? :-)
 * Use at your own risk
 * Love to keep credits ?
 * @author Hari K T
 * @link https://github.com/harikt/TweetRelation
 */

/**
 * Build the array with the contents
 *
 * @param string $id 
 * @param curl   $ch Curl Handle
 */
function buildRelation($id, $ch = null ) {
    static $co;
    // You can also use file_get_contents, but its slow than curl
    // $json = file_get_contents("https://api.twitter.com/1/statuses/show.json?id=$id&include_entities=false");
    $url = "https://api.twitter.com/1/statuses/show.json?id=$id&include_entities=false";
    curl_setopt($ch, CURLOPT_URL, $url);
    $json = curl_exec($ch);
    $contents = json_decode($json);
    $content = array( 
        'text' => $contents->text,
        'screen_name' => $contents->user->screen_name,
        'profile_image_url' => $contents->user->profile_image_url,
        'status_id' => $id
    );
    if( $contents->in_reply_to_status_id_str == null ) {
        $co[] = $content;
        return;
    } else {
        $co[] = $content;
        // Recursion
        buildRelation($contents->in_reply_to_status_id_str, $ch);
    }
    return $co;
}
?>
<!DOCTYPE html>
    <html id="home" lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Build relationship to what the message was</title>
    </head>
    <body>
        <div>Brought to you by <a href="http://harikt.com/blog">Hari K T</a>. Hosting sponsored by <a href="http://vps.fm">vps.fm</a></div>
        <div>
            <p>Just experiment projects :D. Place the status from twitter. </p>
            <p>Eg : https://twitter.com/#!/harikt/status/164665331833520128 , and get the remaining ;-)</p>
            <p>Fork me at <a href="https://github.com/harikt/TweetRelation">GitHub</a> <p>
        </div>
        <div>
        <form method="get" action="<?= $_SERVER['PHP_SELF']; ?>" enctype="application/x-www-form-urlencoded">
            <input type="text" name="status_url" id="status_url" size="90"/>
            <input type="submit" name="submit" value="show" />
        </form>
        </div>
        <div>
<?php
        if(! empty($_GET['status_url'])) {
            $url = $_GET['status_url'];
            if ( filter_var($url , FILTER_VALIDATE_URL) !== false ) {
                $id = basename($url);
                // create a new cURL resource
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                // set URL and other appropriate options
                $con = buildRelation($id, $ch);
                // close cURL resource, and free up system resources
                curl_close($ch);
                $results = array_reverse($con, true);
                if(! empty($results)) {
                    echo "<ul>";
                    foreach( $results as $result ) {
                        //Sometimes the text becomes empty :P , why ? Want to look :D
                        if(! empty($result['text']) ) {
                            echo "<li>{$result['text']} 
                            <a href=\"https://twitter.com/{$result['screen_name']}/status/{$result['status_id']}\">
                                <img src=\"{$result['profile_image_url']}\" />
                                </a></li>";
                        }
                    }
                    echo "</ul>";
                }
            } else {
                echo "Not a Valid URL :), feels right ? contact <a href=\"https://twitter.com/harikt\">@harikt</a> on twitter";
            }
        }
?>
        </div>
    </body>
</html>
