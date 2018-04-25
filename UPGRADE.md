# LUYA ADMIN MODULE UPGRADE

This document will help you upgrading from a LUYA admin module version into another. For more detailed informations about the breaking changes **click the issue detail link**, there you can examples of how to change your code.

## 1.2.x (in progress)

+ This release contains the new migrations. The migrations are requried in order to make the admin module more secure. Therefore make sure to run the `./vendor/bin/luya migrate` command after `composer update`.

## 1.1.x (26. March 2018)

+ This release contains the new migrations which are required for the user and file table. Therefore make sure to run the `./vendor/bin/luya migrate` command after `composer update`.
