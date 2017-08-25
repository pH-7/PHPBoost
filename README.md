# PHPBoost CMS
This web application allows every body without any particular knowledge required in webmastering to create his own website.

It's an open-source project and it's totally free (under the GPL license).

See the project page [PHPBoost CMS](https://www.phpboost.com "link to PHPBoost CMS official Website") for [support](https://www.phpboost.com/forum "link to PHPBoost CMS Support"), [documentation](https://www.phpboost.com/wiki "link to PHPBoost CMS Documentation") and a [demonstration](http://demo.phpboost.com "link to PHPBoost CMS demo website").


# How can i download PHPBoost CMS ?
To download PHPBoost CMS, please follow this link : [Download](https://www.phpboost.com/download "link to PHPBoost CMS Download").
You will find the latest official package and some other modules or templates for customized your website.


# Organisation of github branch
We create one branch per version. 

| Branch | Description | Maintenance |
| --- | --- | --- |
| master | developpement and not stable | not yet |
| **5.1** | **latest stable version** | **fully** |
| 5.0 | previous stable version | fully |
| 4.1 | older stable version | no | 
| 4.0 | older stable version | no |
| 3.0 | older stable version | no |
| other | branchs for tests | - |


# How it's work ?
It requires a web server with a remote access. Then you install, configure and use it with your web browser.

It's composed by a kernel and modules which can use the little PHPBoost development framework. The structure is like a personal computer's one, with operating system which corresponds to PHPBoost's kernel and different software using the kernel that are modules. It works with a database. Now MySQL is required but we wish that we will be able to implement it also for SQLite, PostGreSQL and other DBMS.

PHPBoost can also be very useful to developers who don't want to program a full structure for their website. They can install PHPBoost kernel, maybe with modules already existing and if they don't find the module they need they can easily build their own module using PHPBoost's framework which contains many functions to make easier the development of modules.

To sum up, to build your website with PHPBoost you need a web server with Apache and a DataBase? Managing System.
