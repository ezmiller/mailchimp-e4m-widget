
# mailchimp-e4m-widget

[![version](http://img.shields.io/badge/Version-_0.0.1-blue.svg?style=flat)]()
[![version](http://img.shields.io/badge/Wordpress-4.1-green.svg?style=flat)](http://sailsjs.org)

### Description

MailChimp E4M Widget is a Wordpress plugin that provides a form widget through which users can provide their email in exchange for a media file of some sort. It is in a super beta state and requires considerable manual installation. Files are delivered using AWS and in order to setup the plugin you must acquire an AWS account and so some setup.


### ToDo

* Ensure that the plugin deletes old files when new file uploaded
* Make sure that admin panel works when, on new install, user adds all new data, at once. I.e. file upload may not work if all AWS info not yet saved before file upload attempted.
* Set it up so it works with cloudfront?
* Add instructions about how to create the AWS Policy for S3