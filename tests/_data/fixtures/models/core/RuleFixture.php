<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 09.03.2017
 * Time: 21:29
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yii\test\Fixture;
use yiicms\components\users\FileManagerOwnRule;
use yiicms\components\users\ProfileOwnRule;

class RuleFixture extends Fixture
{
    public function load()
    {
        $auth = \Yii::$app->authManager;
        $rule = new ProfileOwnRule();
        $auth->add($rule);

        //доступ к файловоому менеджеру
        $rule = new FileManagerOwnRule();
        $auth->add($rule);
    }

    public function unload()
    {
        $auth = \Yii::$app->authManager;
        foreach ($auth->getRules() as $rule) {
            $auth->remove($rule);
        }
    }
}