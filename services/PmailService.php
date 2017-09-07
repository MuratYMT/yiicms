<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 06.09.2017
 * Time: 14:46
 */

namespace yiicms\services;

use yii\db\Exception;
use yiicms\components\core\DateTime;
use yiicms\components\YiiCms;
use yiicms\models\core\constants\PmailsConst;
use yiicms\models\core\PmailsFolders;
use yiicms\models\core\PmailsIncoming;
use yiicms\models\core\PmailsOutgoing;
use yiicms\models\core\PmailsUserStat;
use yiicms\settings\Users;

class PmailService
{
    /**
     * создает системные папки для пользователя
     * @param Users $user
     * @return bool
     */
    public function createDefaultsForUsers($user)
    {
        $userId = $user->userId;
        $folderIncoming = new PmailsFolders([
            'userId' => $userId,
            'folderType' => PmailsConst::FOLDER_TYPE_INCOMING,
            'title' => 'Incoming'
        ]);
        $folderIncoming->scenario = PmailsFolders::SC_EDIT;
        $folderOutgoing = new PmailsFolders([
            'userId' => $userId,
            'folderType' => PmailsConst::FOLDER_TYPE_OUTGOING,
            'title' => 'Outgoing'
        ]);
        $folderOutgoing->scenario = PmailsFolders::SC_EDIT;
        /*$folderDraft = new PmailsFolders(['userId' => $userId, 'folderType' => self::TYPE_DRAFT, 'title' => 'Draft']);
        $folderDraft->scenario = self::SC_EDIT;*/
        return /*$folderDraft->save() &&*/
            $folderOutgoing->save() && $folderIncoming->save();
    }

    public function incomingPmailSave(PmailsIncoming $pmail)
    {
        $trans = YiiCms::$app->getDb()->beginTransaction();
        try {
            $isNew = $pmail->isNewRecord;
            $readedOld = $pmail->getOldAttribute('readed');

            $result = $pmail->save();
            if (!$result) {
                $trans->rollBack();
                return false;
            }
            if ($isNew) {
                PmailsUserStat::changePmNotReadCount($pmail->toUser, 1);
                PmailsUserStat::changePmTotalCount($pmail->toUser, 1);
            } else {
                if ($readedOld !== $$pmail->readed) {
                    if ((int)$pmail->readed === 1) {
                        PmailsUserStat::changePmNotReadCount($pmail->toUser, -1);
                    } else {
                        PmailsUserStat::changePmNotReadCount($pmail->toUser, 1);
                    }
                }
            }
            $trans->commit();
            return $result;
        } catch (\Exception $e) {
            $trans->rollBack();
            throw new $e;
        }
    }

    public function incomingPmailDelete(PmailsIncoming $pmail)
    {
        $trans = YiiCms::$app->getDb()->beginTransaction();
        try {
            $result = $pmail->delete();
            if ($result === false) {
                $trans->rollBack();
                return false;
            }

            if (!$pmail->readed) {
                PmailsUserStat::changePmNotReadCount($pmail->toUser, -1);
            }
            PmailsUserStat::changePmTotalCount($pmail->toUser, -1);
            $trans->commit();
            return $result;
        } catch (Exception $e) {
            $trans->rollBack();
            throw  $e;
        }
    }

    public function outgoingPmailSave(PmailsOutgoing $pmail)
    {
        $isNew = $pmail->isNewRecord;
        $oldSended = (int)$pmail->getOldAttribute('sended');
        if ($isNew && $pmail->talkId === null) {
            $pmail->talkId = YiiCms::$app->security->generateRandomString();
        }
        $pmail->trgmToUsers = implode('|', $pmail->toUsersList);
        if ($pmail->sended && (int)$pmail->sended !== $oldSended) {
            $pmail->sentAt = DateTime::runTime();
        }

        $trans = YiiCms::$app->getDb()->beginTransaction();
        try {
            $result = $pmail->save();
            if (!$result) {
                $trans->rollBack();
                return false;
            }

            if ($isNew) {
                PmailsUserStat::changePmTotalCount($pmail->fromUser, 1);
            }
            if ($oldSended !== (int)$pmail->sended && (int)$pmail->sended === 1) {
                $this->send($pmail);
            }
            $trans->commit();
            return $result;
        } catch (\Exception $e) {
            $trans->rollBack();
            throw new $e;
        }
    }

    public function outgoingPmailDelete(PmailsIncoming $pmail)
    {
        $trans = YiiCms::$app->getDb()->beginTransaction();
        try {
            $result = $pmail->delete();
            if ($result === false) {
                $trans->rollBack();
                return false;
            }
            PmailsUserStat::changePmTotalCount($pmail->fromUser, -1);
            $trans->commit();
            return $result;
        } catch (Exception $e) {
            $trans->rollBack();
            throw  $e;
        }
    }

    public function sendMessage(PmailsOutgoing $pmail)
    {
        $pmail->sended = 1;
        $scenario = $pmail->scenario;
        $pmail->scenario = PmailsOutgoing::SC_SEND;
        $result = YiiCms::$app->pmailService->outgoingPmailSave($pmail);
        $pmail->scenario = $scenario;
        return $result;
    }

    /**
     * выполняет отправку уже сохраненного письма
     * @param PmailsOutgoing $pmail
     */
    protected function send(PmailsOutgoing $pmail)
    {
        foreach ($pmail->toUsers as $toUser) {
            $pmailIncoming = new PmailsIncoming([
                'talkId' => $pmail->talkId,
                'toUserId' => $toUser->userId,
                'fromUserId' => $pmail->fromUser->userId,
                'fromUserLogin' => $pmail->fromUser->login,
                'subject' => $pmail->subject,
                'msgText' => $pmail->msgText,
                'readed' => false,
                'sentAt' => $pmail->sentAt,
            ]);
            $pmailIncoming->scenario = PmailsIncoming::SC_INSERT;
            $this->incomingPmailSave($pmailIncoming);
        }
    }
}
