<?php

function get_github_data($url, $return = "body", $key = 1)
{
    $ch = curl_init($url);
    $headers = [];
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: application/vnd.github+json",
        "User-Agent: Awesome-Octocat-App",
        "Authorization: Bearer " . env($key == 1 ? "GITHUB_API_TOKEN" : "GITHUB_API_TOKEN2")
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

function get_developer_type($devs){
    $totalDev = [];
    $fullTime = [];
    $partTime = [];
    $oneTime = [];
    foreach ($devs as $row){
        $total = [];
        $z = explode(",", $row);
        foreach ($z as $x){
            if (!in_array($x, $totalDev))
                $totalDev[] = $x;
            if (!isset($total[$x]))
                $total[$x] = 1;
            else
                $total[$x] += 1;
        }
        $fullTime = array_merge($fullTime, array_keys(array_filter($total, function ($row){
            return $row > 10;
        })));
        $partTime = array_merge($partTime, array_keys(array_filter($total, function ($row){
            return $row < 10 && $row > 1;
        })));
        $oneTime = array_merge($oneTime, array_keys(array_filter($total, function ($row){
            return $row == 1;
        })));
    }

    return [
        'full_time' => count(array_unique($fullTime)),
        'part_time' => count(array_unique($partTime)),
        'one_time' => count(array_unique($oneTime)),
        'total_developer' => count(array_filter($totalDev))
    ];
}

function process_developer_string($developerString)
{
    $developers = [];
    foreach (explode(",", $developerString) as $developer){
        if (isset($developers[$developer]))
            $developers[$developer] += 1;
        else
            $developers[$developer] = 1;
    }

    return [
        'full_time' => count(array_filter($developers, function ($row){
            return $row > 10;
        })),
        'part_time' => count(array_filter($developers, function ($row){
            return $row > 1 && $row <= 10;
        })),
        'one_time' => count(array_filter($developers, function ($row){
            return $row == 1;
        })),
    ];
}

function unique_name(array $array){
    return array_unique(explode(",", implode(",", $array)));
}
