<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 26.01.2016
 * Time: 16:43
 */

namespace yiicms\modules\content\models;

use yiicms\models\content\Comment;

class CommentEdit
{
    /**
     * @param $commentsGroup
     * @param int $parentId
     * @return Comment
     */
    public static function showNew($commentsGroup, $parentId = 0)
    {
        $comment = new Comment();
        $comment->parentId = $parentId;
        $comment->commentGroup = $commentsGroup;

        $comment->scenario = Comment::SC_EDIT;
        return $comment;
    }
}
