<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\controllers;

use yii\rest\ActiveController;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use app\models\LoginForm;
use Yii;
use app\models\User;
use yii\web\NotFoundHttpException;

/**
 * Description of UserController
 *
 * @author nishant
 */
class UserController extends ActiveController {

    public $modelClass = "app\models\User";

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'only' => ['dashboard'],
        ];
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        return $behaviors;
    }

    public function actionLogin() {
        $model = new LoginForm;
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->login()) {
            return User::jwtToken(['id' => Yii::$app->user->id, 'email' => $user->email]);
        } else {
            $model->validate();
            return $model;
        }
    }

    public function actionDashboard() {
        return ['message' => "Welcome to dashboard"];
    }
    
    /**
     * User forgot password API.
     * @param type $email_id
     */
    public function actionForgotPassword(){
        $user = User::findOne(['email' => trim(Yii::$app->request->post('email_id'))]);
        if($user){
            return ['message' => 'Password Reset Email has been sent successfully.'];
        }else {
            throw new NotFoundHttpException;
        }
    }

}
