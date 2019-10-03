<?php
namespace yii2cmf\helpers;

class GistHelper
{

    public static function getGists($username, $category,int $limit = 5)
    {

        $data = file_get_contents(
            "https://api.github.com/users/$username/gists",
            false,
            stream_context_create(['http' => ['user_agent'=> $_SERVER['HTTP_USER_AGENT']]])
        );
        $obj = json_decode($data);
        $data = [];
        foreach ($obj as $key => $gist){
            if (strstr($gist->description, ucfirst($category)) || strstr($gist->description, strtolower($category))) {
                $data[] = ['category' => strtolower($category), 'description' => $gist->description, 'url' => $gist->html_url];
            }

            if ($key+1 == $limit) {
                break;
            }
            //$html .= "<li><a href='{$gist->html_url}'>{$gist->description}</a></li>";
        }
        //echo '<pre>';
        //print_r($data);
        //echo '</pre>';die;
        return $data;
    }
}