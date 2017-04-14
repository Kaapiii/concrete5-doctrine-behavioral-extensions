Concrete5 package for v8 - Package adds Doctrine2 behavioral extensions to concrete5 (work in progress)
======

Installation
------------------

### Install the package manually

1. Download the zip and the folder **concrete5_doctrine_behavioral_extensions-xxx** to {root}/packages
2. Rename the folder to **concrete5_doctrine_behavioral_extensions-xxx**
2. Use [Composer](https://getcomposer.org/) to install the third party dependencies

        composer install

4. Install the package
5. Navigate to **System & Settings -> Doctrine Behavioral Extensions** to see and activate the available extensions.

### Install the package with the projects composer.json file

1. Add the following line to line to the 'require' section of the concrete5 composer.json.
```

"kaapiii/concrete5_doctrine_behavioral_extensions": "^0.2"

```
2. Run 'composer install' from the installation {root}
3. Install the package
4. Navigate to **System & Settings -> Doctrine Behavioral Extensions** to see and activate the available extensions.
