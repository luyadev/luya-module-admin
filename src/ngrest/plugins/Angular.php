<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\ngrest\base\Plugin;

/**
 * Render Angular Templates.
 *
 * If you want to directly apply angular code within forms, or the list you can use this plugin.
 *
 * Example usage to dump all items in the current row of grid list
 *
 * ```php
 * 'dump' => ['angular'],
 * ```
 *
 * Assuming `getDump()` returns
 *
 * ```php
 * public funtion getDump()
 * {
 *     return '{{ item | json }}';
 * }
 * ```
 *
 * A very common scenario is that you might access relation data within angular response.
 *
 * Assuming a news relation is available for the current api:
 *
 * ```php
 * public function getNews()
 * {
 *     return $this->hasOne(News::class, ['id' => 'news_id']);
 * }
 * ```
 *
 * Add the joinable relation to the API response (in the NgRest API):
 *
 * ```php
 * public function withRelations()
 * {
 *     return ['news'];
 * }
 * ```
 *
 * Now you can create an angular plugin based extra field with the name news which will expand the news
 * and return the angular template:
 *
 * ```php
 * public function ngRestExtraAttributeTypes()
 * {
 *     return [
 *         'news' => ['class' => Angular::class, 'template' => '{{item.news.headline }}'],
 *     ];
 * }
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.2.2
 */
class Angular extends Plugin
{
    /**
     * @var string A angular template to render instead of the function name.
     * @since 1.2.3
     */
    public $template;

    /**
     * @inheritdoc
     */
    public function renderList($id, $ngModel)
    {
        return $this->createTag('div', $this->template ?: $this->renderContext->getModel()->{$this->name});
    }

    /**
     * @inheritdoc
     */
    public function renderCreate($id, $ngModel)
    {
        return $this->renderList($id, $ngModel);
    }

    /**
     * @inheritdoc
     */
    public function renderUpdate($id, $ngModel)
    {
        return $this->renderList($id, $ngModel);
    }
}
