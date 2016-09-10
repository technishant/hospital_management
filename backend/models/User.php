<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use UnexpectedValueException;
use DomainException;
/**
 * This is the model class for table "{{%tbl_user}}".
 *
 * @property integer $id
 * @property string $first_name
 * @property string $last_name
 * @property integer $gender
 * @property string $email
 * @property string $mobile_number
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property integer $status
 * @property integer $is_deleted
 * @property string $created
 * @property string $updated
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%tbl_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['first_name', 'email', 'mobile_number', 'auth_key', 'password_hash', 'gender'], 'required'],
            [['gender', 'status', 'is_deleted'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['first_name', 'last_name', 'mobile_number'], 'string', 'max' => 45],
            [['email', 'password_hash', 'password_reset_token'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 255],
            [['is_deleted'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'gender' => 'Gender',
            'email' => 'Email',
            'mobile_number' => 'Mobile Number',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'status' => 'Status',
            'is_deleted' => 'Deleted',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function fields() {
        $fields = parent::fields();
        unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);
        return $fields;
    }

    public function getId() {
        return $this->id;
    }

    public static function findIdentity($id) {
        return static::findOne(['id' => $id]);
    }

    public static function findByUsername($username) {
        return static::findOne(['email' => $username]);
    }

    public static function findIdentityByAccessToken($token, $type = null) {
        try {
            $token = JWT::decode($token, Yii::$app->params['jwtKey'], [Yii::$app->params['jwtAlgorithm']]);
                return User::findIdentity($token->data->id);
        } catch (UnexpectedValueException $ex) {
            return NULL;
        } catch (DomainException $ex){
            return NULL;
        }
    }

    public function validatePassword($password) {
        if (Yii::$app->getSecurity()->validatePassword($password, $this->password_hash)) {
            return true;
        } else {
            return false;
        }
    }

    public function getAuthKey() {
        return $this->auth_key;
    }

    public function validateAuthKey($auth_key) {
        return $this->auth_key == $auth_key;
    }

    public static function jwtToken($payload) {
        $issuedAt = time();
        $token = [
            'iat' => $issuedAt,
            'jti' => base64_encode(mcrypt_create_iv(32)),
            'iss' => Yii::$app->request->serverName,
            'nbf' => $issuedAt + 2,
            'exp' => $issuedAt + (60*60*2),
            'data' => $payload
        ];
        $jwt = JWT::encode($token, Yii::$app->params['jwtKey'], Yii::$app->params['jwtAlgorithm']);
        return $jwt;
    }

}
