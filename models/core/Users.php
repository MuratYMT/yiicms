<?php

namespace yiicms\models\core;

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\web\UserEvent;
use yiicms\components\core\AuthKey;
use yiicms\components\core\behavior\DateTimeBehavior;
use yiicms\components\core\behavior\JsonArrayBehavior;
use yiicms\components\core\behavior\TimestampBehavior;
use yiicms\components\core\DateTime;
use yiicms\components\core\db\Connection;
use yiicms\components\core\File;
use yiicms\components\core\validators\DateTimeValidator;
use yiicms\components\core\validators\HtmlFilter;
use yiicms\components\core\validators\LangValidator;
use yiicms\components\core\validators\PhoneValidator;

/**
 * This is the model class for table "web.users".
 * @property integer $userId Идентификатор пользователя
 * @property string $login Логин
 * @property string $password Пароль. При записи передаваемое значение преобразуется в хэш. При чтении выдается хеш пароля
 * @property string $email Email
 * @property integer $sex Пол
 * @property string $fio Ф.И.О.
 * @property string $timeZone Часовой пояс
 * @property string $birthday Дата рождения
 * @property integer $pmSubscribe Дублировать личные сообщения на почту
 * @property string $lang Язык пользователя
 * @property string $phone Номер сотового телефона
 * @property string $lastIp ip адрес
 * @property string $provider Провайдер авторизации
 * @property string $providerIdentity  Идентификатор пользователя в системе авторизации
 * @property array $uData переменные пользователя
 * @property DateTime|string $createdAt Дата/время регистрации. При чтении всегда выдает объект DateTime
 * При записи можно передать строку которая будет считаться что находится в часовом поясе указанном в \Yii::$app->formatter->timeZone
 * либо объект DateTime
 * @property DateTime|string $updatedAt Дата/время последнего обновления. При чтении всегда выдает объект DateTime
 * При записи можно передать строку которая будет считаться что находится в часовом поясе указанном в \Yii::$app->formatter->timeZone
 * либо объект DateTime
 * @property DateTime|string $visitedAt Дата/время последнего входа. При чтении всегда выдает объект DateTime
 * При записи можно передать строку которая будет считаться что находится в часовом поясе указанном в \Yii::$app->formatter->timeZone
 * либо объект DateTime
 * @property integer $status Статус пользователя
 * @property string $token Токен для сброса пароля, подтверждения e - mail и т . д .
 * @property File $photo Фото
 * @property AuthKey[] $authKeys Ключ запомнить меня
 * @property array $social Массив данных о социальных контактах skype, icq и т.д.
 * а также подпись о себе и т.п.
 * @property int $uploadedFilesSize Общий размер загруженных файлов
 * @property string $publicEmail
 * @property int $icq
 * @property string $skype
 * @property string $about
 * @property string $location местонахождение
 * @property string $interests интересы
 * @property string $education образование
 * @property string $work место работы
 * @property string $mailAgent
 * @property string $facebook
 * @property string $vkontakte
 * @property string $twitter
 */
class Users extends ActiveRecord implements IdentityInterface
{
    const EVENT_USER_REGISTRATION = 'userRegistration';

    const SC_REGISTRATION = 'registration';
    const SC_PROFILE_EDIT = 'profileEdit';

    /** Inactive status */
    const STATUS_INACTIVE = 0;
    /** Active status */
    const STATUS_ACTIVE = 1;
    /** Banned status */
    const STATUS_BANNED = 2;
    /** Deleted status */
    const STATUS_DELETED = 3;

    const SEX_MAN = 1;
    const SEX_WOOMEN = 0;
    const SEX_UNKNOWN = -1;

    protected static $statuses = [
        self::STATUS_INACTIVE,
        self::STATUS_ACTIVE,
        self::STATUS_BANNED,
        self::STATUS_DELETED,
    ];

    protected static $sexes = [
        self::SEX_MAN,
        self::SEX_UNKNOWN,
        self::SEX_WOOMEN,
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%users}}';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            [
                'class' => TimestampBehavior::class,
                'createdAttributes' => ['createdAt'],
                'updatedAttributes' => ['updatedAt'],
            ],
            [
                'class' => JsonArrayBehavior::class,
                'attributes' => ['uData', 'social'],
            ],
            [
                'class' => DateTimeBehavior::class,
                'attributes' => ['visitedAt'],
                'format' => DateTimeBehavior::FORMAT_DATETIME,
            ],
        ]);
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => [],
            self::SC_REGISTRATION => ['login', 'email', 'timeZone', 'phone', 'password'],
            self::SC_PROFILE_EDIT => array_merge(self::$socialField, [
                'fio',
                'timeZone',
                'phone',
            ]),
        ];
    }

    public function isTransactional($operation)
    {
        return true;
    }

    public function init()
    {
        parent::init();
        if ($this->sex === null) {
            $this->sex = self::SEX_UNKNOWN;
        }
        if ($this->social === null) {
            $this->social = [];
        }
        if ($this->uData === null) {
            $this->uData = [];
        }
        if ($this->authKeys === null) {
            $this->authKeys = [];
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['login', 'password', 'email'], 'required'],
            [['email'], 'email', 'checkDNS' => !YII_ENV_TEST],
            [['sex', 'uploadedFilesSize', 'warnLevel', 'pmSubscribe', 'status'], 'integer',],
            [['status'], 'in', 'range' => self::$statuses],
            [['sex'], 'in', 'range' => self::$sexes],
            [['birthday'], DateTimeValidator::class, 'format' => DateTimeValidator::FORMAT_DATE],
            [['password', 'email', 'token'], 'string', 'max' => 255],
            [['login'], 'string', 'min' => 3, 'max' => 64],
            [['fio'], 'string', 'max' => 200],
            [['login', 'fio'], HtmlFilter::class],
            [['timeZone'], 'in', 'range' => \DateTimeZone::listIdentifiers()],
            [['lang'], LangValidator::class],
            [['phone'], PhoneValidator::class],
            [['provider', 'providerIdentity'], 'string', 'max' => 500],
            [['email', 'login'], 'unique'],
            [
                ['provider', 'providerIdentity'],
                'unique',
                'targetAttribute' => ['provider', 'providerIdentity'],
                'message' => \Yii::t('yiicms', 'Такой пользователь уже зарегестрирован'),
            ],
            [['publicEmail'], 'email'],
            [['icq'], 'integer'],
            [['fio', 'skype', 'publicEmail', 'about', 'location', 'mailAgent', 'facebook', 'vkontakte', 'twitter'], 'string', 'max' => 255,],
            [['fio', 'skype', 'publicEmail', 'about', 'location', 'mailAgent', 'facebook', 'vkontakte', 'twitter'], HtmlFilter::class],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'userId' => \Yii::t('yiicms', 'Идентификатор пользователя'),
            'login' => \Yii::t('yiicms', 'Логин'),
            'password' => \Yii::t('yiicms', 'Пароль'),
            'email' => \Yii::t('yiicms', 'Email'),
            'sex' => \Yii::t('yiicms', 'Пол'),
            'fio' => \Yii::t('yiicms', 'Ф.И.О.'),
            'timeZone' => \Yii::t('yiicms', 'Часовой пояс'),
            'uploadedFilesSize' => \Yii::t('yiicms', 'Размер загруженных файлов'),
            'birthday' => \Yii::t('yiicms', 'Дата рождения'),
            'lang' => \Yii::t('yiicms', 'Язык пользователя'),
            'phone' => \Yii::t('yiicms', 'Номер сотового телефона'),
            'lastIp' => \Yii::t('yiicms', 'IP адрес'),
            'provider' => \Yii::t('yiicms', 'Провайдер авторизации'),
            'providerIdentity' => \Yii::t('yiicms', 'Идентификатор пользователя в системе авторизации'),
            'uData' => \Yii::t('yiicms', 'Данные'),
            'createdAt' => \Yii::t('yiicms', 'Дата регистрации'),
            'updatedAt' => \Yii::t('yiicms', 'Дата последнего обновления'),
            'visitedAt' => \Yii::t('yiicms', 'Дата последнего входа'),
            'status' => \Yii::t('yiicms', 'Статус пользователя'),
            'token' => \Yii::t('yiicms', 'Токен для сброса пароля, подтверждения e - mail и т . д . '),
            'photo' => \Yii::t('yiicms', 'Фото'),
            'authKeys' => \Yii::t('yiicms', 'Ключ запомнить меня'),
            'skype' => \Yii::t('yiicms', 'Skype'),
            'about' => \Yii::t('yiicms', 'О мне'),
            'location' => \Yii::t('yiicms', 'Откуда'),
            'mailAgent' => \Yii::t('yiicms', 'Агент Mail.Ru'),
            'icq' => \Yii::t('yiicms', 'ICQ'),
            'facebook' => \Yii::t('yiicms', 'Facebook'),
            'vkontakte' => \Yii::t('yiicms', 'Вконтакте'),
            'twitter' => \Yii::t('yiicms', 'Твиттер'),
            'publicEmail' => \Yii::t('yiicms', 'Публичный email'),
            'interests' => \Yii::t('yiicms', 'Интересы'),
            'education' => \Yii::t('yiicms', 'Образование'),
            'work' => \Yii::t('yiicms', 'Место работы'),
        ];
    }

    /**
     * человекопонятные определния статусов
     * @param $status
     * @return string
     */
    public static function statusLabel($status)
    {
        switch ($status) {
            case self::STATUS_INACTIVE:
                return \Yii::t('yiicms', 'Не активный');
            case self::STATUS_ACTIVE:
                return \Yii::t('yiicms', 'Активный');
            case self::STATUS_BANNED:
                return \Yii::t('yiicms', 'Заблокирован');
            case self::STATUS_DELETED:
                return \Yii::t('yiicms', 'Удален');
        }
        return $status;
    }

    protected static $socialField = [
        'skype',
        'publicEmail',
        'about',
        'location',
        'mailAgent',
        'facebook',
        'vkontakte',
        'twitter',
        'publicEmail',
        'icq',
        'interests',
        'education',
        'work',
    ];

    /**
     * @inheritDoc
     */
    public function __set($name, $value)
    {
        if (in_array($name, self::$socialField, true)) {
            $social = $this->social;
            if ($value === null && isset($social[$name])) {
                unset($social[$name]);
            } else {
                $social[$name] = $value;
            }
            $this->social = $social;
        } else {
            if ($name === 'birthday') {
                $value = \Yii::$app->formatter->asDate($value, DateTime::DATE_FORMAT);
            } elseif ($name === 'photo') {
                /** @var File $value */
                $value = File::saveToJson($value);
            } elseif ($name === 'password') {
                $value = \Yii::$app->security->generatePasswordHash($value);
            } elseif ($name === 'authKeys') {
                $value = AuthKey::saveToJson($value);
            }
            parent::__set($name, $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if (in_array($name, self::$socialField, true)) {
            $social = $this->social;
            if ($social === null) {
                $social = [];
            }
            $value = isset($social[$name]) ? $social[$name] : null;
        } else {
            $value = parent::__get($name);
        }

        if ($name === 'photo') {
            $value = File::createFromJson($value);
            $value = empty($value) ? new File() : reset($value);
        } elseif ($name === 'authKeys') {
            $value = AuthKey::createFormJson($value);
        }
        return $value;
    }

    public function beforeSave($insert)
    {
        $this->lastIp = YII_ENV_TEST ? '127.0.0.255' : \Yii::$app->request->userIP;

        if ($insert) {
            $this->visitedAt = DateTime::runTime();
            $this->status = self::STATUS_ACTIVE;
            $this->generateToken();
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            //назначаем группу по умолчанию
            $auth = \Yii::$app->authManager;
            $role = $auth->getRole(Settings::get('users.defaultRegisteredRole'));
            $auth->assign($role, $this->userId);
            $this->trigger(self::EVENT_USER_REGISTRATION);
        }
    }

    /**
     * устанавливает переменную пользователя
     * @param string|int $key ключ
     * @param mixed $value значение переменной
     */
    public function setUserData($key, $value)
    {
        $uData = $this->uData;
        $uData[$key] = $value;
        $this->uData = $uData;
    }

    /**
     * переменная пользователя
     * @param string|int $key ключ
     * @param mixed $defaultValue значение по умолчанию
     * @return mixed
     */
    public function getUserData($key, $defaultValue = null)
    {
        $uData = $this->uData;
        /** @noinspection NotOptimalIfConditionsInspection */
        if (isset($uData[$key]) || array_key_exists($key, $uData)) {
            return $uData[$key];
        }

        return $defaultValue;
    }

    /**
     * выдает переменную пользователя если она существует и после этого удаляет ее из переменных профиля
     * @param $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public function extractUserData($key, $defaultValue = null)
    {
        $uData = $this->uData;
        /** @noinspection NotOptimalIfConditionsInspection */
        if (isset($uData[$key]) || array_key_exists($key, $uData)) {
            $value = $uData[$key];
            unset($uData[$key]);
            $this->uData = $uData;
            return $value;
        }
        return $defaultValue;
    }

    /**
     * удаление перменной пользователя
     * @param string|int $key ключ
     */
    public function deleteUserData($key)
    {
        $uData = $this->uData;
        /** @noinspection NotOptimalIfConditionsInspection */
        if (isset($uData[$key]) || array_key_exists($key, $uData)) {
            unset($uData[$key]);
        }
        $this->uData = $uData;
    }

    /**
     * Найти пользователя по ИД
     * @param integer $id ИД пользователя
     * @return Users|null
     * null если пользователь не найден или заблокирован, удален и т.п.
     */
    public static function findIdentity($id)
    {
        if (null !== ($user = static::findOne(['userId' => $id, 'status' => self::STATUS_ACTIVE]))) {
            //устанавливаем локаль
            $formatter = \Yii::$app->formatter;
            $formatter->locale = $user->lang;
            $timeZone = $user->timeZone;
            \Yii::$app->timeZone = $timeZone;
            $formatter->timeZone = $timeZone;
            /** @var Connection $db */
            $db = self::getDb();
            $db->setDbTimeZone($timeZone);
        }

        return $user;
    }

    /**
     * находит толшько активных пользователей
     * @param $userId
     * @return null|static
     */
    public static function findById($userId)
    {
        return static::findOne(['userId' => $userId, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Найти пользователя с любым статусом
     * @param $id
     * @return null|static
     */
    public static function findByIdWithAllStatus($id)
    {
        return static::findOne(['userId' => $id]);
    }

    /**
     * найти пользователя по логину
     * @param string $login логин
     * @return Users|null
     * null если пользователь не найден или заблокирован, удален и т.п.
     */
    public static function findByLogin($login)
    {
        return static::findOne(['login' => $login, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * найти пользователя по email
     * @param string $email email
     * @return Users|null
     * null если пользователь не найден или заблокирован, удален и т.п.
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return Users|null
     * null если пользователь не найден или заблокирован, удален и т.п.
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        if (null === ($user = static::findOne(['token' => $token, 'status' => self::STATUS_ACTIVE]))) {
            return null;
        }
        if (!self::checkTokenExpire($user->token)) {
            return null;
        }
        return $user;
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|integer an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        return $this->userId;
    }

    /** @var  AuthKey */
    private $_authKey;

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     * The space of such keys should be big enough to defeat potential identity attacks.
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     * @throws \yii\base\Exception
     */
    public function getAuthKey()
    {
        $authKeys = $this->authKeys;

        $newAuthKey = AuthKey::create();

        if ($this->_authKey !== null) {
            unset($authKeys[$this->_authKey->key]);
        }

        if (!Settings::get('users.multiLogin')) {
            $authKeys = [];
        }
        $authKeys[$newAuthKey->key] = $newAuthKey;

        $this->_authKey = $newAuthKey;
        $this->authKeys = $authKeys;

        self::getDb()->createCommand()
            ->update(self::tableName(), ['authKeys' => AuthKey::saveToJson($authKeys)], ['userId' => $this->userId])
            ->execute();
        return $newAuthKey->key;
    }

    /**
     * проверяет ключ авторизации
     * @param string $authKey проверяемый ключ
     * @return boolean
     */
    public function validateAuthKey($authKey)
    {
        $authKeys = $this->authKeys;
        if (empty($authKeys)) {
            return false;
        }

        if (!isset($authKeys[$authKey])) {
            return false;
        }

        if ($authKeys[$authKey]->check($authKey)) {
            $this->_authKey = $authKeys[$authKey];
            return true;
        } else {
            $this->_authKey = null;
            unset($authKeys[$authKey]);
            return false;
        }
    }

    public function validatePassword($password)
    {
        try {
            return \Yii::$app->security->validatePassword($password, $this->password);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * проверяет дату истечения токена сброса пароля
     * @param string $token password reset token
     * @return boolean
     */
    private static function checkTokenExpire($token)
    {
        if (empty($token)) {
            return false;
        }
        $parts = explode('|', $token);

        if (count($parts) !== 2) {
            return false;
        }

        $expire = Settings::get('users.passwordResetTokenExpire');
        $timestamp = (int)$parts[1];
        return $timestamp + $expire >= time();
    }

    /**
     * создает токен для сброса пароля
     * @throws \yii\base\Exception
     */
    public function generateToken()
    {
        $this->token = \Yii::$app->security->generateRandomString() . '|' . time();
        if (!$this->isNewRecord) {
            self::getDb()->createCommand()
                ->update(self::tableName(), ['token' => $this->token], ['userId' => $this->userId])
                ->execute();
        }
    }

    /**
     * @param UserEvent $event
     */
    public static function afterLogin($event)
    {
        /** @var self $user */
        $user = $event->identity;
        $user->lastIp = YII_ENV_TEST ? '127.0.0.255' : \Yii::$app->request->userIP;
        $user->visitedAt = DateTime::runTime();
        self::getDb()->createCommand()
            ->update(
                self::tableName(),
                ['lastIp' => $user->lastIp, 'visitedAt' => DateTime::convertToDbFormat($user->visitedAt)],
                ['userId' => $user->userId]
            )
            ->execute();
    }

    /**
     * Добавляет пользователю размер загруженных файлов
     * @param int $fileSize сколько байт добавить
     * @return bool
     */
    public function addUploadedFilesSize($fileSize)
    {
        return $this->updateCounters(['uploadedFilesSize' => $fileSize]);
    }
}
