<?php
use luya\admin\Module;

?>
<div class="luya-content" ng-controller="AccountController">
    <div class="row">
        <div class="col-md-6">

            <p class="lead"><?= Module::t('mode_user_personal_info') ?></p>
            <div class="card">
                <div class="card-body">
                    <form ng-submit="changePersonData(profile)">
                        <zaa-select fieldid="mode_user_title" model="profile.title" label="<?= Module::t('mode_user_title'); ?>" options="[{value:1, label:'<?= Module::t('model_user_title_mr'); ?>'}, {value:2, label:'<?= Module::t('model_user_title_mrs'); ?>'}]" />
                        <zaa-text autocomplete="given-name" fieldid="mode_user_firstname" label="<?= Module::t('mode_user_firstname'); ?>" model="profile.firstname" />
                        <zaa-text autocomplete="family-name" fieldid="mode_user_lastname" label="<?= Module::t('mode_user_lastname'); ?>" model="profile.lastname" />
                        <zaa-text autocomplete="email" fieldid="mode_user_email" label="<?= Module::t('mode_user_email'); ?>" model="profile.email" />
                        <button class="btn btn-save btn-icon" type="submit"><?= Module::t('layout_rightbar_savebtn'); ?></button>
                        
                    </form>
                    <div ng-show="activities.open_email_validation" class="mt-3">
                        <hr />
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

            <p class="lead mt-3"><?= Module::t('settings_general') ?></p>
            <div class="card mb-3">
                <div class="card-body">
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
        <div class="col-md-6">
            <p class="lead"><?= Module::t('mode_user_password'); ?></p>
            <div class="card">
                <div class="card-body">
                    <form ng-submit="changePassword()">
                        <zaa-password autocomplete="current-password" fieldid="model_user_oldpassword" label="<?= Module::t('model_user_oldpassword'); ?>" model="pass.oldpass" />
                        <zaa-password autocomplete="new-password" fieldid="aws_changepassword_new_pass" label="<?= Module::t('aws_changepassword_new_pass'); ?>" model="pass.newpass" />
                        <zaa-password autocomplete="new-password" fieldid="aws_changepassword_new_pass_retry" label="<?= Module::t('aws_changepassword_new_pass_retry'); ?>" model="pass.newpassrepeat" />
                        <button class="btn btn-save btn-icon" type="submit"><?= Module::t('layout_rightbar_savebtn'); ?></button>
                    </form>
                </div>
            </div>
            <p class="lead mt-3">Devices</p>
            <div class="card">
                <div class="card-body">
                    <p>A list of devices you will be auto logged in without prompting for a password.</p>
                    <p ng-show="devices.length == 0" class="alert alert-info mb-0">There are no devices for your account.</p>
                    <table ng-show="devices.length > 0" class="table table-bordered table-striped mb-0">
                        <thead>
                            <th>Device</th>
                            <th>Last login</th>
                            <th colspan="2">First login</th>
                        </thead>
                        <tr ng-repeat="device in devices">
                            <td>
                                {{ device.userAgentName}}
                                <span class="badge badge-success" ng-show="device.isCurrentDevice">This device</span>
                            </td>
                            <td>
                                {{ device.updated_at | date:'short' }}
                            </td>
                            <td>
                                {{ device.created_at | date:'short' }}
                            </td>
                            <td class="text-center">
                                <button type="button" ng-click="removeDevice(device)" class="btn btn-icon btn-delete"></button>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>