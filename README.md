[![SensioLabsInsight](https://insight.sensiolabs.com/projects/bd8b1a54-3386-4d04-b5fa-c00e315ebe42/big.png)](https://insight.sensiolabs.com/projects/bd8b1a54-3386-4d04-b5fa-c00e315ebe42)

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

#### Attention:
If you decide to install the package manually, you should install all packages which depend on its functionality, after you first installed this package. The reason is, that concrete5 loads the active packages in the same order they were installed.

### Install the package with the projects composer.json file (since v 0.2)

1. Add the following line to line to the 'require' section of the concrete5 composer.json.

        "kaapiii/concrete5_doctrine_behavioral_extensions": "^0.2"

2. Run the following command from the installation {root} folder

        composer install

3. Install the package
4. Navigate to **System & Settings -> Doctrine Behavioral Extensions** to see and activate the available extensions.

#### Note:
With this installation method, the installation order of the packages doesn't matter. All third party dependencies are installed in {root}/concrete/vendor and therefore loaded before the packages are initiated.
