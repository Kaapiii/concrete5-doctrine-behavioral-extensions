[![Latest Stable Version](https://poser.pugx.org/kaapiii/concrete5_doctrine_behavioral_extensions/v)](//packagist.org/packages/kaapiii/concrete5_doctrine_behavioral_extensions)
[![Total Downloads](https://poser.pugx.org/kaapiii/concrete5_doctrine_behavioral_extensions/downloads)](//packagist.org/packages/kaapiii/concrete5_doctrine_behavioral_extensions)
[![License](https://poser.pugx.org/kaapiii/concrete5_doctrine_behavioral_extensions/license)](//packagist.org/packages/kaapiii/concrete5_doctrine_behavioral_extensions)

# Doctrine2 Behavioral Extensions for concrete5 v8

Package adds the [doctrine behavioral extensions](https://github.com/Atlantic18/DoctrineExtensions) to concrete5 version >= 8.0.0

## Installation

```bash
require kaapiii/concrete5_doctrine_behavioral_extensions
```

- Install the package in concrete5
- Navigate to **System & Settings -> Doctrine Behavioral Extensions** to see the active and available extensions.

## Version Compatibility

| Package Version | Behavioral Extension | Minimum PHP | Minimum concrete5 |
| --- | --- | --- | --- |
| 2.* | 3.* | 7.2 | 8.5+ |
| 1.* | 2.4.* | 5.6 | 8.0+ |

## Supported Extensions

- **Blameable**
- **Loggable**
- **Sluggable**
- **Timestampable**
- **Translatable**
- **Tree**
- **Sortable**
- **SoftDeletable** (since package version 2.0.0)

## Usage

**Update Mapping Information and Entities** \
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
