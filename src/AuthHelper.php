<?php
namespace yii2cmf\helpers;

use Yii;

class AuthHelper {


    public static function getUsername()
    {
        return Yii::$app->user->identity->username ?? 'unknown';
    }

    public static function getRoles():string
    {
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->identity->id);
        $user_roles = '';
        foreach ($roles as $role) {
            $user_roles .= $role->name.',';
        }
        return substr($user_roles, 0, -1);
    }
}