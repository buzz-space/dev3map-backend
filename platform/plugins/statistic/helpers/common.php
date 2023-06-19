<?php

function get_github_data($url, $return = "body")
{
    $ch = curl_init($url);
    $headers = [];
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: application/vnd.github+json",
        "User-Agent: Awesome-Octocat-App",
        "Authorization: Bearer " . env("GITHUB_API_TOKEN")
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_HEADERFUNCTION,
        function ($curl, $header) use (&$headers) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) // ignore invalid headers
                return $len;

            $headers[strtolower(trim($header[0]))][] = trim($header[1]);

            return $len;
        }
    );

    $body = curl_exec($ch);

    if ($return == "body")
        return $body;
    return $headers;

}

function write_to_file($filename, $content)
{
    if (!file_exists(public_path("storage")))
        mkdir(public_path("storage"));

    fopen(public_path("storage/$filename"), "w+");
    file_put_contents(public_path("storage/$filename"), $content);

    return;
}

function get_from_file($filename)
{
    return json_decode(file_get_contents(public_path("storage/$filename")));
}

function get_last_page($header){
    if (!isset($header['link']))
        return 1;
    $link = $header['link'][0];
    preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $link, $match);
    $parts = parse_url($match[0][1]);
    parse_str($parts['query'], $query);
    return $query["page"];
}
