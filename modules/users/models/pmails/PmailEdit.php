<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 02.02.2016
 * Time: 8:48
 */

namespace yiicms\modules\users\models\pmails;

use yiicms\models\core\PmailsIncoming;
use yiicms\models\core\PmailsOutgoing;
use yiicms\models\core\Users;
use yii\web\NotFoundHttpException;

class PmailEdit
{
    /**
     * @param int|Users $toUser для какого пользователя письмо
     * @return PmailsOutgoing
     * @throws NotFoundHttpException
     */
    public static function showNew($toUser)
    {
        $model = new PmailsOutgoing();
        $model->scenario = PmailsOutgoing::SC_EDIT;
        $model->fromUserId = \Yii::$app->user->id;
        $model->toUsers = ($toUser instanceof Users) ? $toUser : self::getUser($toUser);
        return $model;
    }

    /**
     * @param int $rowId идентификатор письма
     * @param int $userId какому пользователю письмо принадлежит
     * @return PmailsOutgoing
     * @throws NotFoundHttpException
     */
    public static function showEdit($rowId, $userId)
    {
        /** @var PmailsOutgoing $model */
        $model = PmailsOutgoing::viewDraft($userId)->andWhere(['rowId' => $rowId])->one();
        if ($model === null) {
            throw new NotFoundHttpException;
        }

        $model->scenario = PmailsOutgoing::SC_EDIT;
        return $model;
    }

    /**
     * @param int $replyMailRowId идентификатор письма
     * @param int $userId какому пользователю письмо принадлежит
     * @return PmailsOutgoing
     * @throws NotFoundHttpException
     */
    public static function showReply($replyMailRowId, $userId)
    {
        /** @var PmailsIncoming $replyMail */
        $replyMail = PmailsIncoming::view($userId)->andWhere(['rowId' => $replyMailRowId])->one();
        if ($replyMail === null) {
            throw new NotFoundHttpException;
        }

        $model = self::showNew($replyMail->fromUser);
        $model->talkId = $replyMail->talkId;
        $model->subject = 'Re: ' . $replyMail->subject;
        $model->msgText = self::pmCite($replyMail);

        return $model;
    }

    /**
     * @param int $forwardMailRowId идентификатор письма которое надо переслать
     * @param int $toUserId какому пользователю переслать письмо
     * @return PmailsIncoming|PmailsOutgoing
     * @throws NotFoundHttpException
     */
    public static function showForward($forwardMailRowId, $toUserId)
    {
        $forwardMail = self::showReadPossible($forwardMailRowId, \Yii::$app->user->id);

        if ($forwardMail instanceof PmailsOutgoing && (int)$forwardMail->sended === 0) {
            throw new NotFoundHttpException;
        }

        $model = self::showNew($toUserId);
        $model->subject = 'Fw: ' . $forwardMail->subject;
        $model->msgText = self::pmCite($forwardMail);

        return $model;
    }

    /**
     * выдает сообщение для которое можно прочитать
     * @param int $rowId
     * @param int $userId какому пользователю письмо принадлежит
     * @return PmailsIncoming|PmailsOutgoing
     * @throws NotFoundHttpException
     */
    public static function showReadPossible($rowId, $userId)
    {
        $model = self::getAnyModel($rowId, $userId);

        if ($model instanceof PmailsIncoming) {
            $model->markRead();
        }

        return $model;
    }

    /**
     * удаляет сообщение
     * удалить сообщение может только получатель или отправитель
     * @param int $rowId идентификатор письма
     * @param int $userId какому пользователю письмо принадлежит
     * @return PmailsIncoming|PmailsOutgoing|false false если удалить не удалось
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public static function del($rowId, $userId)
    {
        $model = self::getAnyModel($rowId, $userId);

        if ($model->delete()) {
            return $model;
        }

        return false;
    }

    /**
     * @param int $rowId
     * @param int $userId
     * @return PmailsIncoming|PmailsOutgoing
     * @throws NotFoundHttpException
     */
    public static function getAnyModel($rowId, $userId)
    {
        /** @var PmailsOutgoing|PmailsIncoming $model */
        $model = PmailsIncoming::view($userId)->andWhere(['rowId' => $rowId])->one();
        if ($model === null) {
            $model = PmailsOutgoing::view($userId)->andWhere(['rowId' => $rowId])->one();
            /** @noinspection NotOptimalIfConditionsInspection */
            if ($model === null) {
                throw new NotFoundHttpException;
            }
        }
        return $model;
    }

    /**
     * отправка сообщения
     * сообщение отпраляется только в том случае если автор совпадает с userId и письмо еще не было отправлено
     * @param int $rowId
     * @param int $userId
     * @return PmailsOutgoing|false
     * @throws NotFoundHttpException
     */
    public static function sendMessage($rowId, $userId)
    {
        /** @var PmailsOutgoing $model */
        $model = PmailsOutgoing::viewDraft($userId)->andWhere(['rowId' => $rowId])->one();
        if ($model === null) {
            throw new NotFoundHttpException;
        }
        return $model->sendMessage() ? $model : false;
    }

    /**
     * помечает сообщение прочитанным
     * сообщение отмечается прочитанным только если получатель совпдает с userId
     * @param int $rowId
     * @param int $userId
     * @return bool
     * @throws NotFoundHttpException
     */
    public static function markMessageRead($rowId, $userId)
    {
        /** @var PmailsIncoming $model */
        $model = PmailsIncoming::view($userId)->andWhere(['rowId' => $rowId])->one();
        if ($model === null) {
            throw new NotFoundHttpException;
        }

        return $model->markRead();
    }

    /**
     * помечает сообщение не прочитанным
     * сообщение отмечается не прочитанным только если получатель совпдает с userId
     * @param int $rowId
     * @param int $userId
     * @return bool
     * @throws NotFoundHttpException
     */
    public static function markMessageUnRead($rowId, $userId)
    {
        /** @var PmailsIncoming $model */
        $model = PmailsIncoming::view($userId)->andWhere(['rowId' => $rowId])->one();
        if ($model === null) {
            throw new NotFoundHttpException;
        }

        return $model->markUnRead();
    }

    /**
     * Ищет пользователя
     * @param int $userId ID пользователя
     * @return Users
     * @throws NotFoundHttpException
     */
    private static function getUser($userId)
    {
        $user = Users::findById($userId);
        if ($user === null) {
            throw new NotFoundHttpException;
        }
        return $user;
    }

    /**
     * @param PmailsOutgoing|PmailsIncoming $pmail
     * @return string
     */
    public static function pmCite($pmail)
    {
        $header = \Yii::t(
            'yiicms',
            '{fromUserLogin} писал в {date}',
            ['fromUserLogin' => $pmail->fromUser->login, 'date' => \Yii::$app->formatter->asDatetime($pmail->sentAt)]
        );

        $message = $pmail->msgText;

        return "<p>&nbsp;</p><blockquote class=\"cite\"><header>$header</header>$message</blockquote><br/>";
    }
}
