<?php

namespace admintests\models;

use admintests\AdminModelTestCase;
use luya\testsuite\fixtures\ActiveRecordFixture;

class StorageFilterChainTest extends AdminModelTestCase
{
    public function testStorageFilterChain()
    {
        // setup effect model in order to ensure relation call
        $effect = new ActiveRecordFixture([
            'modelClass' => 'luya\admin\models\StorageEffect',
            'fixtureData' => [
                'model1' => [
                    'id' => 1,
                    'identifier' => 'thumbnail',
                    'name' => 'Thumbnail',
                    'imagine_name' => 'thumbnail',
                    'imagine_json_params' => '{"vars":[{"var":"width","label":"Breit in Pixel"},{"var":"height","label":"Hoehe in Pixel"},{"var":"mode","label":"outbound or inset"},{"var":"saveOptions","label":"save options"}]}',
                ],
                'model2' => [
                    'id' => 2,
                    'identifier' => 'crop',
                    'name' => 'Crop',
                    'imagine_name' => 'crop',
                    'imagine_json_params' => '{"vars":[{"var":"width","label":"Breit in Pixel"},{"var":"height","label":"Hoehe in Pixel"},{"var":"saveOptions","label":"save options"}]}',
                ]
            ]
        ]);

        // setup storage filter chain
        $fixture = new ActiveRecordFixture([
            'modelClass' => 'luya\admin\models\StorageFilterChain',
            'fixtureData' => [
                'model1' => [
                    'id' => 1,
                    'sort_index' => 0,
                    'filter_id' => 1,
                    'effect_id' => 1,
                    'effect_json_values' => '{"width":800,"height":700}',
                ],
                'model2' => [
                    'id' => 2,
                    'sort_index' => 0,
                    'filter_id' => 1,
                    'effect_id' => 1,
                    'effect_json_values' => '{}',
                ]
            ]
        ]);

        /* @var \luya\admin\models\StorageFilterChain $model1 */
        $model1 = $fixture->getModel('model1');
        /* @var \luya\admin\models\StorageFilterChain $model2 */
        $model2 = $fixture->getModel('model2');
        $this->assertSame(1, $model1->id);
        $this->assertSame('thumbnail', $model1->effect->identifier);
        $this->assertSame('thumbnail', $model1->effect->getImagineEffectName());

        // effectDefinition
        $this->assertSame(['width', 'height'], $model1->effectDefinition('crop', 'required'));

        // hasMissingRequiredEffectDefinition
        $this->assertFalse($model1->hasMissingRequiredEffectDefinition('thumbnail'));
        $this->assertFalse($model1->hasMissingRequiredEffectDefinition('crop')); // even crop infos are the same and therefore true
        $this->assertTrue($model2->hasMissingRequiredEffectDefinition('thumbnail'));
        $this->assertTrue($model2->hasMissingRequiredEffectDefinition('crop'));


        // effectChainValue
        $this->assertSame(800, $model1->effectChainValue('thumbnail', 'width'));
        $this->assertSame(700, $model1->effectChainValue('thumbnail', 'height'));
        $this->assertSame(false, $model1->effectChainValue('thumbnail', 'doesnotexists'));
        $this->assertSame(2, $model1->effectChainValue('thumbnail', 'mode')); // call mode option not provided by the effect_json_values


        // auto encode
        $this->assertSame(['width' => 800, 'height' => 700], $model1->effect_json_values);

        // destroy and cleanup
        $effect->cleanup();
        $fixture->cleanup();
    }
}
