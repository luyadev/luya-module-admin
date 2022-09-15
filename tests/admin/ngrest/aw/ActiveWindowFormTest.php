<?php

namespace admintests\admin\ngrest\aw;

use admintests\AdminTestCase;
use luya\admin\ngrest\aw\ActiveWindowFormWidget;

class ActiveWindowFormTest extends AdminTestCase
{
    public function testFieldConfig()
    {
        $form = ActiveWindowFormWidget::begin(['callback' => 'action', 'buttonValue' => 'Subnit', 'controllerName' => 'TestController']);
        echo $form->field('checkbox', 'checkboxLabel')->checkbox();
        echo $form->field('checkboxList', 'checkboxListLabel')->checkboxList(['foo' => 'bar']);
        echo $form->field('radioList', 'radioListLabel')->radioList(['foo' => 'bar']);
        echo $form->field('imageUpload', 'imageUploadLabel')->imageUpload();
        echo $form->field('fileupload', 'fileuploadLabel')->fileUpload();
        echo $form->field('datepicker', 'datepickerLabel')->datePicker();
        echo $form->field('datetimePicker', 'datetimepickerLabel')->datetimePicker();

        $content = <<<'EOT'
<script>
zaa.bootstrap.register('TestController', ['$scope', '$controller', '$injector', function($scope, $controller, $injector) {
    $scope.crud = $scope.$parent;
    $scope.params = {};
    $scope.responseData = {};
    $scope.callbackFunction = function() {};    $scope.clearOnError = 0;
    $scope.sendButton = function(callback) {
        $scope.crud.sendActiveWindowCallback(callback, $scope.params).then(function(success) {
            var data = success.data;
            var errorType = null;
            var message = false;
        	$scope.responseData = data.responseData;
            if ("error" in data) {
                errorType = data.error;
            }
        
            if ("message" in data) {
                message = data.message;
            }

            var response = $injector.invoke($scope.callbackFunction, this, { $scope : $scope, $response : data.responseData});
            
            if (errorType !== null) {
                if (errorType == true) {
                    $scope.crud.toast.error(message, 8000);
                    if ($scope.clearOnError) {
                        $scope.params = {};
                    }
                } else {
                    $scope.crud.toast.success(message, 8000);
                                    }
            }
		}, function(error) {
			$scope.crud.toast.error(error.data.message, 8000);
            if ($scope.clearOnError) {
                $scope.params = {};
            }
		});
    };
}]);
</script>
<div ng-controller="TestController">
    <form ng-submit="sendButton('action')">
    	<zaa-checkbox fieldid="model-zaa-checkbox" ng-init="" model="params.checkbox" label="checkboxLabel" fieldname="model"></zaa-checkbox>
<zaa-checkbox-array fieldid="model-zaa-checkbox-array" ng-init="" model="params.checkboxList" label="checkboxListLabel" options='{"items":[{"label":"bar","value":"foo"}]}' fieldname="model"></zaa-checkbox-array>
<zaa-radio fieldid="model-zaa-radio" ng-init="" model="params.radioList" label="radioListLabel" options='[{"label":"bar","value":"foo"}]' fieldname="model"></zaa-radio>
<zaa-image-upload fieldid="model-zaa-image-upload" ng-init="" model="params.imageUpload" label="imageUploadLabel" options='{"no_filter":0}' fieldname="model"></zaa-image-upload>
<zaa-file-upload fieldid="model-zaa-file-upload" ng-init="" model="params.fileupload" label="fileuploadLabel" fieldname="model"></zaa-file-upload>
<zaa-date fieldid="model-zaa-date" ng-init="" model="params.datepicker" label="datepickerLabel" fieldname="model"></zaa-date>
<zaa-datetime fieldid="model-zaa-datetime" ng-init="" model="params.datetimePicker" label="datetimepickerLabel" fieldname="model"></zaa-datetime>
        <button class="btn btn-save btn-icon" type="submit">Subnit</button>
    </form>
</div>
EOT;
        $this->assertSame($content, $form->run());
    }
}
