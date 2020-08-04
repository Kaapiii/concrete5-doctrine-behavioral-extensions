[![Latest Stable Version](https://poser.pugx.org/kaapiii/concrete5_doctrine_behavioral_extensions/v)](//packagist.org/packages/kaapiii/concrete5_doctrine_behavioral_extensions)
[![Total Downloads](https://poser.pugx.org/kaapiii/concrete5_doctrine_behavioral_extensions/downloads)](//packagist.org/packages/kaapiii/concrete5_doctrine_behavioral_extensions)
[![License](https://poser.pugx.org/kaapiii/concrete5_doctrine_behavioral_extensions/license)](//packagist.org/packages/kaapiii/concrete5_doctrine_behavioral_extensions)

# Doctrine2 behavioral extensions for concrete5 v8

Package add the [doctrine behavioral extensions](https://github.com/Atlantic18/DoctrineExtensions) to concrete5 version >= 8.0.0

## Installation

```bash
require kaapiii/concrete5_doctrine_behavioral_extensions
```

- Install the package in concrete5
- Navigate to **System & Settings -> Doctrine Behavioral Extensions** to see and activate the available extensions.


## Usage

**Update mapping information and entities** \
Update your entities in your package with the desired behaviors. Here an example with yaml mapping files and the timestampable behavior. 

```yaml
---
Kaapiii\Example\MyEntity:
  type: entity
  table: myentity
  id:
    id:
      type: integer
      generator:
        strategy: AUTO
  fields:
    title:
      type: string
      length: 64
    created:
      type: date
      gedmo:
        timestampable:
          on: create
    updated:
      type: datetime
      gedmo:
        timestampable:
          on: update
```

**Update your package** \
Increase your concrete5 package version and update the package. Concrete5 will handle the upgarde of all the entities.

## Documentation
For more information, on how to use the behavioral extensions in your entities please consult [Atlantic18/DoctrineExtensions repository](https://github.com/Atlantic18/DoctrineExtensions)
