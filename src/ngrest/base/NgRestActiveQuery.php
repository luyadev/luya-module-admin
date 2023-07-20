<?php

namespace luya\admin\ngrest\base;

use luya\helpers\ArrayHelper;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\Expression;

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
     * @param string $value The value to compare within the json string
     * @param string $operator The operator which should be used for the where condition, by default its "EQUALS" or "=" {@since 2.0.2}
     * @return NgRestActiveQuery
     */
    public function i18nWhere($field, $value, $operator = '=')
    {
        return $this->jsonWhere($operator, $field, Yii::$app->composition->langShortCode, $value);
    }

    /**
     * Where condition with json values.
     *
     * ```
     * jsonWhere(['=', 'json_values', 'key', 'value']);
     * ```
     *
     * Assuming you have a json value in the field `json_values` stored as objects, for example `{"key":"value", "key":"value2"}`
     *
     * > Keep in mind this only works with mysql version 5.7 and above.
     *
     * @param string $operator The operator to compare the value
     * @param string $field The field which contains the json data
     * @param string $key The key inside the json object to compare
     * @param string|integer $value The value to compare against the key value.
     * @return NgRestActiveQuery
     * @since 2.0.0
     */
    public function jsonWhere($operator, $field, $key, $value)
    {
        return $this->andWhere([$operator, "JSON_EXTRACT({$field}, \"$.{$key}\")", $value]);
    }


    /**
     * Order by a given json key inside a field.
     *
     * ```
     * ->orderBy('title_json', 'de', 'asc')
     * ```
     *
     * Assuming the database content of `title_json` looks like this:
     *
     * ```
     * {'de': 'foobar', 'en' : 'foobar'}
     * ```
     *
     * @param string $field The field name (attribute) which contains the json
     * @param string $key The json key inside the json object to sort after
     * @param string $direction Either `desc` or `asc`
     * @see https://stackoverflow.com/a/70909842/4611030
     * @return NgRestActiveQuery
     * @since 4.5.0
     */
    public function jsonOrderBy($field, $key, $direction)
    {
        return $this->orderBy(new Expression($field . '->"$.'.$key.'" ' . $direction));
    }

    /**
     * Where condition for a field inside an array.
     *
     * ```
     * jsonWhere(['json_values', 'key', 'value']);
     * ```
     *
     * Assuming you have a json value in the field `json_values` stored as array with obejcts, for example `[{"key":"value"}, {"key":"value2"}]`.
     *
     * > Keep in mind this only works with mysql version 5.7 and above.
     *
     * @param string $field The field which contains the json data
     * @param string $key The key inside the json array object
     * @param string|integer $value The value to compare against the key
     * @return NgRestActiveQuery
     * @since 2.1.0
     */
    public function jsonArrayWhere($field, $key, $value)
    {
        return $this->andWhere(['>', "JSON_CONTAINS(JSON_EXTRACT({$field}, \"$[*].{$key}\"), \"{$value}\")", 0]);
    }

    /**
     * Generate a pool where condition for a given ngRestPools() definition.
     *
     * The defined pool must exists in the list of {{luya\admin\ngrest\base\NgRestModel::ngRestPools()}}. If not found
     * the where condition will not be added. If $exception is enabled an exception will be thrown when the pool identifier
     * is not found in the list of pools.
     *
     * @param string $pool The name of the pool to lookup in the ngRestPools(). Usually provied via Yii::$app->request->get('pool')
     * @param boolean $exception Whether the method should throw an exception if the pool can not be found in the list of model pools. {@since 2.4.0}
     * @return NgRestActiveQuery
     * @since 2.0.0
     */
    public function inPool($pool = null, $exception = false)
    {
        if (empty($pool)) {
            return $this;
        }

        $model = Yii::createObject($this->modelClass);

        if (array_key_exists($pool, $model->ngRestPools())) {
            return $this->andWhere($model->ngRestPools()[$pool]);
        }

        if ($exception) {
            throw new InvalidConfigException("The requested pool identifier '{$pool}' does not exist in the ngRestPools() definition.");
        }

        return $this;
    }

    /**
     * Add a where condition for the current model primary key.
     *
     * ```php
     * MyModel::ngRestFind()->byPrimaryKey(1);
     *
     * // equals to if primary key field is id
     *
     * MyModel::ngRestFind()->andWhere(['id' => 1]);
     * ```
     *
     * Composite keys
     *
     * ```php
     * MyModel::ngRestFind()->byPrimaryKey("1,14");
     * // or
     * MyModel::ngRerstFind()->byPrimaryKey([1,14]);
     *
     * // equals to if composite primary key would be user_id and group_id
     * MyModel::ngRestFind()->andWhere(['user_id' => 1, 'group_id' => 14]);
     * ```
     *
     * @param string|array $condition
     * @return NgRestActiveQuery
     * @since 2.0.1
     */
    public function byPrimaryKey($condition)
    {
        $modelClass = $this->modelClass;
        $keys = $modelClass::primaryKey();
        $values = is_array($condition) ? array_values($condition) : explode(',', (string) $condition);

        if (count($keys) !== count($values)) {
            throw new Exception("The number of primary key condition values must be equals the number of primary keys ".count($keys)." available for this model.");
        }

        return $this->andWhere(array_combine($keys, ArrayHelper::typeCast($values)));
    }
}
