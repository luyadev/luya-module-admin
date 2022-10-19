<?php

namespace luya\admin\dashboard;

use luya\admin\base\DashboardObjectInterface;
use luya\helpers\StringHelper;
use Yii;
use yii\base\BaseObject;

/**
 * Base Implementation of an Dashboard Object.
 *
 * This provides the setters and getters from the {{luya\admin\base\DashboardObjectInterface}}.
 *
 * @property string $template
 * @property string $outerTemplateContent
 * @property string $dataApiUrl
 * @property string $title
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
abstract class BaseDashboardObject extends BaseObject implements DashboardObjectInterface
{
    /**
     * Get the Outer Template.
     *
     * The outer is mainly a wrapper which wraps the template. As the template is the input from the module property, it has to wrappe into a nice looking
     * crad panel by default. But this is only used when dealing with base dashboard implementation.
     *
     * @return string Returns the outer template string which can contain the {{template}} variable, but don't have to.
     */
    abstract public function getOuterTemplateContent();

    /**
     * Option content parser varaibles
     *
     * Pass additional variables into the template.
     *
     * ```
     * 'variables' => [
     *     'foo' => 'bar',
     *     'time' => function() {
     *         return time();
     *     },
     *     'title' => ['Key', 'Value'] // equals to: Yii::t('Key', 'Value')
     * ]
     * ```
     *
     * The variables can be used as {{foo}} and {{time}} in the template.
     *
     * @var array An array with key and value, where the key is what is available in the template.
     * @since 4.2.0
     */
    public $variables = [];

    private $_template;

    /**
     * Setter method for template.
     *
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    /**
     * @inheritdoc
     */
    public function getTemplate()
    {
        return $this->contentParser($this->getOuterTemplateContent());
    }

    /**
     * Parse the content will replace {{dataApiUrl}}, {{title}}, {{template}} variables with the content from the object.
     *
     * @param string $content The content to parse.
     * @return string
     */
    public function contentParser($content)
    {
        $customVars = [];
        foreach ($this->variables as $key => $value) {
            if (is_array($value)) {
                [$category, $message] = $value;
                $customVars[$key] = Yii::t($category, $message);
            } else {
                $customVars[$key] = is_callable($value) ? call_user_func($value, $content, $this) : $value;
            }
        }

        $vars = [
            'dataApiUrl' => $this->getDataApiUrl(),
            'title' => $this->getTitle(),
            'template' => StringHelper::template($this->_template, $customVars, false),
        ];

        return StringHelper::template($content, array_merge($vars, $customVars), true);
    }

    private $_dataApiUrl;

    /**
     * Setter methdo for dataApiUrl.
     *
     * @param string $dataApiUrl
     */
    public function setDataApiUrl($dataApiUrl)
    {
        $this->_dataApiUrl = $dataApiUrl;
    }

    /**
     * @inheritdoc
     */
    public function getDataApiUrl()
    {
        return $this->_dataApiUrl;
    }

    private $_title;

    /**
     * Setter method for title.
     *
     * The title can be either a string on array, if an array is provided the first key is used to defined the yii2 message category and second key
     * is used in order to find the message. For example the input array `['cmsadmin', 'mytitle']` would be converted to `Yii::t('cmsadmin', 'mytitle')`.
     *
     * @param string|array $title The title of the dashboard object item, if an array is given the first element is the translation category the second element the message.
     */
    public function setTitle($title)
    {
        if (is_array($title)) {
            [$category, $message] = $title;
            $this->_title =  Yii::t($category, $message);
        } else {
            $this->_title = $title;
        }
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->_title;
    }
}
