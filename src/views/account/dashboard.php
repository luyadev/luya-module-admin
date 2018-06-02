<?php
use luya\admin\Module;

?>
<div class="luya-content" ng-controller="AccountController">
    <h1>
        <span><span ng-bind="profile.firstname"></span> <span ng-bind="profile.lastname"></span></span>
    </h1>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                	<h2><?= Module::t('mode_user_personal_info') ?></h2>
                    <form ng-submit="changePersonData(profile)">
                        <zaa-select fieldid="mode_user_title" model="profile.title" label="<?= Module::t('mode_user_title'); ?>" options="[{value:1, label:'<?= Module::t('model_user_title_mr'); ?>'}, {value:2, label:'<?= Module::t('model_user_title_mrs'); ?>'}]" />
                        <zaa-text autocomplete="given-name" fieldid="mode_user_firstname" label="<?= Module::t('mode_user_firstname'); ?>" model="profile.firstname" />
                        <zaa-text autocomplete="family-name" fieldid="mode_user_lastname" label="<?= Module::t('mode_user_lastname'); ?>" model="profile.lastname" />
                        <zaa-text autocomplete="email" fieldid="mode_user_email" label="<?= Module::t('mode_user_email'); ?>" model="profile.email" />
                        <button class="btn btn-save btn-icon" type="submit"><?= Module::t('layout_rightbar_savebtn'); ?></button>
                        
                    </form>
                    <div ng-show="activities.open_email_validation" class="mt-3">
                        <div class="alert alert-warning">
                            <p class="mb-0"><?= Module::t('account_changeemail_enterverificationtoken')?></p>
                        </div>
                        <form ng-submit="changeEmail()">
                            <zaa-text fieldid="mode_email_token" label="<?= Module::t('model_user_email_verification_token')?>" model="email.token" />
                            <button class="btn btn-save btn-icon" type="submit"><?= Module::t('layout_rightbar_savebtn'); ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h2><?= Module::t('mode_user_password'); ?></h2>
                    <form ng-submit="changePassword()">
                        <zaa-password autocomplete="current-password" fieldid="aws_changepassword_new_pass" label="<?= Module::t('aws_changepassword_new_pass'); ?>" model="pass.newpass" />
                        <zaa-password autocomplete="new-password" fieldid="aws_changepassword_new_pass_retry" label="<?= Module::t('aws_changepassword_new_pass_retry'); ?>" model="pass.newpassrepeat" />
                        <zaa-password autocomplete="off" fieldid="model_user_oldpassword" label="<?= Module::t('model_user_oldpassword'); ?>" model="pass.oldpass" />
                        <button class="btn btn-save btn-icon" type="submit"><?= Module::t('layout_rightbar_savebtn'); ?></button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h2><?= Module::t('settings_general') ?></h2>
                    <div class="form-group form-side-by-side">
                        <div class="form-side form-side-label">
                            <label for="lang-changer"><?= Module::t('layout_rightbar_languagelabel')?></label>
                        </div>
                        <div class="form-side">
                            <select id="lang-changer" class="form-control" ng-model="settings.luyadminlanguage">
                                <?php foreach ($this->context->module->interfaceLanguageDropdown as $key => $lang): ?>
                                    <option value="<?= $key; ?>"><?= $lang;?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group form-side-by-side">
                        <div class="form-side form-side-label">
                            <label for="userSettings.isDeveloper"><?= Module::t('settings_developer_mode') ?></label>
                        </div>
                        <div class="form-side">
                            <input type="checkbox" ng-model="settings.isDeveloper" id="userSettings.isDeveloper" />
                            <label for="userSettings.isDeveloper"></label>
                        </div>
                    </div>
                    <button type="button" class="btn btn-save btn-icon" ng-click="changeSettings(settings)"><?= Module::t('layout_rightbar_savebtn'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>