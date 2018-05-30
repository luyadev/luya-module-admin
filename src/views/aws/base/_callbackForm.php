<script>
zaa.bootstrap.register('<?= $angularCrudControllerName; ?>', ['$scope', '$controller', '$injector', function($scope, $controller, $injector) {
    $scope.crud = $scope.$parent;
    $scope.params = {};
    $scope.responseData = {};
    $scope.callbackFunction = <?= $angularCallbackFunction; ?>
    $scope.clearOnError = <?= $clearOnError; ?>;
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
                    <?= $closeOnSuccess.$reloadListOnSuccess.$reloadWindowOnSuccess; ?>
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
<div ng-controller="<?= $angularCrudControllerName; ?>">
    <form ng-submit="sendButton('<?= $callbackName; ?>')">
    	<?= $form; ?>
        <button class="<?= $buttonClass; ?>" type="submit"><?= $buttonNameValue; ?></button>
    </form>
</div>