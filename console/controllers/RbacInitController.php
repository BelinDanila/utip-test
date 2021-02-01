<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\web\User;

class RbacInitController extends Controller
{
    /**
     * Создает роль и присваевает дочерние роли
     *
     * @param string $role
     * @param array $childs
     */
    public function actionCreateRole(string $role, array $childs = [])
    {
        $auth = Yii::$app->authManager;

        try {
            $user = $auth->createRole($role);
            $auth->add($user);
        } catch (\Throwable $e) {
            Yii::error("Role $role already exist");
        }

        try {
            if (!empty($childs)) {
                foreach ($childs as $child) {
                    if ($role = $auth->getRole($child)) {
                        $auth->addChild($role, $user);
                    } else {
                        new \Exception("Role $role does not exist");
                    }
                }
            }
        } catch (\Throwable $e) {
            Yii::error($e->getMessage());
        }
    }

    /**
     * Присваевает пользователю c идентификатором $userId роль администратора
     *
     * @param int $userId Идентификатор пользователя
     * @throws \Exception
     */
    public function actionAssignAdmin(int $userId)
    {
        if (!$userId || !is_int($userId)) {
            Yii::error("Param 'id' must be set!");
            throw new \Exception("Param 'id' must be set!");
        }

        $user = (new User())->findIdentity($userId);
        if(!$user){
            // throw new \yii\base\InvalidConfigException("User witch id:'$id' is not found");
            Yii::error("User witch id:'$userId' is not found!");
            throw new \Exception("User witch id:'$userId' is not found!");
        }

        $auth = Yii::$app->authManager;
        $role = $auth->getRole('admin');
        $auth->revokeAll($userId);
        $auth->assign($role, $userId);
    }
}