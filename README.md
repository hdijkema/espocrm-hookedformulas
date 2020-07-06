# espocrm-hookedformulas
A module that creates more formula possibilities for EspoCRM

* [Introduction](#n1)
* [Simple example](#n2)
* [Provided functions](#n3) (-> [Functions](https://github.com/hdijkema/espocrm-hookedformulas/wiki/Functions))
* [Developing](#n4)

<a name="n1"></a>
## Introduction
This module for [EspoCRM](https://github.com/espocrm/espocrm) adds more sophisticated formula functionality to EspoCRM.
Instead of only having formulas at the `'beforeSave'` hook, this module will make it possible to implement formulas for other hooks.

The following hooks are supported:

* afterSave 
* afterRelate 
* afterUnrelate 
* afterRemove
* afterMassRelate

All hooks are called with the Entity the formula applies to. In addition, the following variables are provided in the formula for `afterRelate` and `afterUnrelate`:

* $relationName - refers to the name of the relation that has been created on this Entity
* $foreignId - refers to the id of the foreign entity that is provided by the relation
* $foreignEntity - is the instantiated foreign Entity for $foreignId.

For `afterMassRelate`, following variables are provider:

* $relationName - refers to the name of the relation that has been created on this Entity
* $relationParams - <not used>

See [EspoCRM Hooks](https://docs.espocrm.com/development/hooks/) for more information about the hooks.

<a name="n2"></a>
### Simple example

In your formula you create sections for the different hooks. The `afterSave` section is implicit, because EspoCRM already provides this in the formulas. It can however be made explicit. Below is a simple formula for a given 'Entity':

```javascript
   begin:beforeSave
     name = string\concatenate(name, ' - before save');
   end:beforeSave
   
   begin:afterSave
     record\recalculate('OtherEntity', 'entityId=', 'id');
   end:afterSave
   
   begin:afterRelate
     $nrecs = record\attribute('Entity', id, 'RelatedRecs');
     $nrecs = $nrecs + 1;
     record\update('Entity', id, 'RelatedRecs', $nrecs); 
   end:afterRelate
   
   begin:afterUnrelate
     $nrecs = record\attribute('Entity', id, 'RelatedRecs');
     $nrecs = $nrecs - 1;
     record\update('Entity', id, 'RelatedRecs', $nrecs); 
   end:afterUnrelate
```

<a name="n3"></a>
# Provided functions

HookedFormulas provides a lot of new functions to be used in EspoCRM. For more information see the [Functions](https://github.com/hdijkema/espocrm-hookedformulas/wiki/Functions) section.

<a name="n4"></a>
# Developing

1. Checkout espocrm-hookedformulas to ./HookedFormulas.
```
git clone https://github.com/hdijkema/espocrm-hookedformulas.git HookedFormulas
```
2. Start coding.



