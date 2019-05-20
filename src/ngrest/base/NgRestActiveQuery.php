<?php

namespace luya\admin\ngrest\base;

use Yii;
use yii\db\ActiveQuery;

/**
 * NgRest Active Query.
 *
 * This class extends the {{yii\db\ActiveQuery}} with the {{i18nWhere()}} method which does only work on mysql 5.7.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.2.2
 */
class NgRestActiveQuery extends ActiveQuery
{
    /**
     * Very basic where condition for i18n fields which use mysql's JSON_EXTRACT function.
     *
     * This method allows very basic where condition for NgRest model fiels which are cased as {{luya\admin\ngrest\base\NgRestModel::$i18n}}.
     *
     * Assuming your ngrest model has an i18n field:
     *
     * ```php
     * class MyModel extends NgRestModel
     * {
     *     public $i18n = ['slug', 'name'];
     * }
     * ```
     *
     * The value for slug will be stored as json in the database, an example record for slug could look like `{"en":"english-slug","de":"german-slug"}`.
     *
     * In order to perform where conditions with the json fields you can use {{i18nWhere}}. The above example where condition could be:
     *
     * ```php
     * $model = MyModel::find()->i18nWhere('slug', 'german-slug')->one();
     * ```
     *
     * > Keep in mind this only works with mysql version 5.7 and above.
     *
     * @param string $field The field (attribute) name which is cased with {{luya\admin\ngrest\base\NgRestModel::$i18n}}
     * @param string $value The value to compare within the json string.
     * @return NgRestActiveQuery
     */
    public function i18nWhere($field, $value)
    {
        $lang = Yii::$app->composition->langShortCode;
        return $this->andWhere(["JSON_EXTRACT({$field}, \"$.{$lang}\")" => $value]);
    }

    /**
     * Where condition with json values.
     *
     * @param string $operator
     * @param string $field
     * @param string $key
     * @param string|integer $value
     * @return NgRestActiveQuery
     * @since 2.0.0
     */
    public function jsonWhere($operator, $field, $key, $value)
    {
        return $this->andWhere([$operator, "JSON_EXTRACT({$field}, \"$.{$key}\")", $value]);
    }

    /**
     * Add the pool where condition if a pool is given.
     *
     * @param string $pool
     * @return NgRestActiveQuery
     * @since 2.0.0
     */
    public function inPool($pool = null)
    {
        if (empty($pool)) {
            return $this;
        }
        $model = Yii::createObject($this->modelClass);

        if (!array_key_exists($pool, $model->ngRestPools())) {
            throw new InvalidConfigException("The requested pool identifier '{$pool}' does not exist in the ngRestPools() definition.");
        }

        return $this->andWhere($model->ngRestPools()[$pool]);
    }
}
