<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 24.06.2016
 * Time: 10:52
 */

namespace yiicms\components\core;

use yii\db\ActiveQuery;
use yii\db\Connection;

/**
 * Class TreeTrait
 * @package yiicms\components\core
 * @property integer $parentId идентификатор родительского пункта меню
 * @property string $mPath материализованный путь
 * @property integer $levelNod уровень пункта меню. Read Only
 * @property static[] $parentsBranch родительская ветка этого объекта исключая сам объект
 * @property static[] $childrenBranch список дочерних объектов находящихся в ветке этого объекта исключая сам объект
 * @property static $parent родительский объект
 * @property static [] $children непосредстенные дочерние объекты
 * @property int $primaryKeyValue значение поля отвечающего за первичный ключ
 * @property int $primaryKeyField поле первичного ключа
 * @property bool $isHierarhyChange флаг того что изменились иерархические данные
 * @method ActiveQuery hasMany($class, array $link) see [[BaseActiveRecord::hasMany()]] for more info
 * @method ActiveQuery hasOne($class, array $link) see [[BaseActiveRecord::hasOne()]] for more info
 * @method static string className() see [[Object::class]] for more info
 * @method static string tableName() see [[ActiveRecord::tableName()]] for more info
 * @method static Connection getDb() see [[ActiveRecord::getDb()]] for more info
 * @method string[] primaryKey() see [[ActiveRecord::primaryKey()]] for more info
 * @method static ActiveQuery find() see [[ActiveRecord::find()]] for more info
 */
trait TreeTrait
{
    /**
     * @var string количество дочерних элементов в построенном дереве
     * устанавливается только при построении дерева.
     */
    public $__childCount__ = 0;

    /**
     * Удаление узла дерева и всех его дочерних узлов
     * @return false|int количество удаленных категорий false - если удаление не удалось
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function deleteRecursive()
    {
        $trans = self::getDb()->beginTransaction();
        try {
            $count = 0;
            foreach ($this->children as $child) {
                $result = $child->deleteRecursive();
                if ($result === false) {
                    $trans->rollBack();
                    return false;
                } else {
                    $count += $result;
                }
            }
            unset($this->children);

            if (false === $result = $this->delete()) {
                $trans->rollBack();
                return false;
            } else {
                $count += $result;
            }
            $trans->commit();
            return $count;
        } catch (\Exception $e) {
            $trans->rollBack();
            throw $e;
        }
    }

    // -------------------------------------------------- связи -------------------------------------------------------
    public function getParent()
    {
        $primaryKey = $this->primaryKey();
        return $this->hasOne(self::class, [reset($primaryKey) => 'parentId'])->from(static::tableName() . ' as child');
    }

    public function getChildren()
    {
        $primaryKey = $this->primaryKey();
        return $this->hasMany(self::class, ['parentId' => reset($primaryKey)])
            ->from(static::tableName() . ' as parent');
    }

    /**
     * список пунктов меню находящихся в ветке этой категории
     * @return ActiveQuery
     */
    public function getChildrenBranch()
    {
        $query = static::find()->where(['like', 'mPath', $this->mPath . '^%', false]);
        $query->multiple = true;
        return $query;
    }

    /**
     * @return ActiveQuery
     */
    public function getParentsBranch()
    {
        $query = self::find()
            ->where([
                'and',
                [$this->primaryKeyField => TreeHelper::mPath2ParentsIds($this->mPath)],
                ['<>', $this->primaryKeyField, $this->primaryKeyValue]
            ])
            ->orderBy('mPath');
        $query->multiple = true;
        return $query;
    }

    // ----------------------------------------------- геттеры и сеттеры ----------------------------------------------

    public function getPrimaryKeyValue()
    {
        $primaryKeyArray = $this->primaryKey();
        $pk = reset($primaryKeyArray);
        return $this->$pk;
    }

    public function getPrimaryKeyField()
    {
        $primaryKeyArray = $this->primaryKey();
        return reset($primaryKeyArray);
    }

    public function getLevelNod()
    {
        return TreeHelper::mPath2Level($this->mPath);
    }

    public function getIsHierarhyChange()
    {
        return $this->isAttributeChanged('parentId');
    }
}
