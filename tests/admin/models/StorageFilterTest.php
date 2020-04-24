<?php

namespace admintests\models;

use admintests\AdminModelTestCase;
use luya\admin\models\StorageEffect;
use luya\admin\models\StorageFilter;
use luya\admin\models\StorageFilterChain;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\traits\AdminDatabaseTableTrait;
use Yii;

class StorageFilterTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function testApplyFilterChain()
    {
        $this->createAdminNgRestLogFixture();

        // storage filter
        $storage = new NgRestModelFixture([
            'modelClass' => StorageFilter::class,
        ]);

        $model = new $storage->newModel;
        $this->assertNotNull($model->ngRestActiveWindows());
        $this->assertNotNull($model->ngRestScopes());

        $model->identifier = 'foo';
        $model->name = 'Foo';
        $this->assertTrue($model->save());

        // effect 
        $effect = new NgRestModelFixture([
            'modelClass' => StorageEffect::class,
            'fixtureData' => [
                2 => [
                    'id' => 2,
                    'name' => 'effect2',
                    'identifier' => 'effect2',
                    'imagine_name' => 'crop',
                ]
            ]
        ]);
        $effectModel = $effect->newModel;
        $this->assertNotNull($model->ngRestActiveWindows());
        $this->assertNotNull($model->ngRestScopes());

        $effectModel->identifier = 'foobar';
        $effectModel->name = 'foobar';
        $effectModel->imagine_name = 'thumbnail';
        $effectModel->imagine_json_params = json_encode(['vars' => [
            ['var' => 'width', 'label' => 'Breit in Pixel'],
            ['var' => 'height', 'label' => 'Hoehe in Pixel'],
            ['var' => 'mode', 'label' => 'outbound or inset'], // THUMBNAIL_OUTBOUND & THUMBNAIL_INSET
            ['var' => 'saveOptions', 'label' => 'save options'],
        ]]);

        $this->assertTrue($effectModel->save());

        $this->assertSame('thumbnail', $effectModel->getImagineEffectName());

        // add chains
        $chain = new NgRestModelFixture([
            'modelClass' => StorageFilterChain::class,
        ]);
        
        $chainModel = $chain->newModel;

        $chainModel->setAttributes([
            '$name' => 'Thumbnail',
            'imagine_name' => 'thumbnail',
            'effect_id' => 1,
            'filter_id' => 1,
            'sort_index' => 1,
            'effect_json_values' => ['width' => 100, 'height' => 100],
        ]);

        $this->assertTrue($chainModel->save());

        $chainModel->setAttributes([
            '$name' => 'Crop',
            'imagine_name' => 'crop',
            'effect_id' => 2,
            'filter_id' => 1,
            'sort_index' => 1,
            'effect_json_values' => ['width' => 100, 'height' => 100],
        ]);

        $this->assertTrue($chainModel->save());

        $this->assertNotNull($chainModel->effect);

        /// APPLY THE CHAIN!

        $model->applyFilterChain(Yii::getAlias('@app/tests/data/image.jpg'), Yii::getAlias('@app/tests/data/runtime/image_result_'.time().'.jpg'));

    }
}