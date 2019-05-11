<?php
use luya\admin\Module;

?>
<div ng-controller="ActiveWindowTagController">
    <div class="row">
        <div class="col-md-8">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <div class="input-group-text">
                        <i class="material-icons">search</i>
                    </div>
                </div>
                <input class="form-control" type="text" ng-model="searchString" placeholder="Enter search term...">
            </div>
            <span style="font-size:15px;" 
                ng-repeat="tag in tags | filter:searchString | orderBy: 'name'"
                ng-click="saveRelation(tag, relation[tag.id])"
                ng-class="{'badge-primary font-weight-bold text-bold': relation[tag.id] == 1, 'badge-secondary': relation[tag.id] != 1}"
                class="badge badge-pill mx-1 mb-2"
            >{{tag.name}}</span>
        </div>
        <div class="col-md-4">
            <form method="post" ng-submit="saveTag()">
                <div class="form-group mb-2">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <i class="material-icons">add</i>
                            </div>
                        </div>
                        <input class="form-control" maxlength="255" ng-model="newTagName" type="text" />
                    </div>
                </div>
                <button type="submit" class="btn btn-add btn-icon float-right"><?= Module::t('aws_tag_add')?></button>
            </form>
        </div>
    </div>
</div>