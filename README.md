# Advanced Custom Layout Bundle

Configure and create custom layouts without drag and drop.

## Usecase
Sometimes you might have to create multiple custom layouts for multiple dataObjects.
Additionally, you want to only adapt one field while keeping the rest always the same.
The current Pimcore implementation makes it necessary to adapt all custom layouts as soon as you ClassDefinition changes.
**Therefor we created this bundle.**

Here you can simply adapt visibility and editability of fields without having to drag and drop them again.

Main features:
- saving a ClassDefinition will also recreate the custom layouts
- decide wether you want to show all fields and adapt only mentioned ones or if you want to only show the mentioned fields
- define roles and workflow states which will automatically apply the custom layout

**Note:** this bundle will create custom layouts but taking the default class's layout definition and adapt the field-attributes.
Hidding all fields will still keep layout elements.

## Installation

```
composer require basilicom/pimcore-advanced-custom-layout-bundle --dev
```

Add a new configuration file `config/packages/custom_layouts.yaml`.
An example configuration can be found in `config/packages/example.custom_layouts.yaml`.

## Configuration

```
Pimcore\Model\DataObject\TestObject:
    # layouts
    custom_layout:
        label: "My custom layout"
        mode: !php/const Basilicom\AdvancedCustomLayoutBundle\Model\CustomLayoutConfig::MODE_EDIT
        auto_apply_roles: []
        auto_apply_workflow_states: []
        fields:
            title:
                title: "Overwritten Titel"
                editable: false
                visible: false
```

- `Pimcore\Model\DataObject\TestObject:` is the class name of the object you want to configure the custom layout for.
- `custom_layout` is the identifier of the custom layout. You can define multiple custom layouts for one class.
- `label` is the label of the custom layout which will be displayed in the object edit mode.
- `mode` is the mode of the custom layout. See below for more information.
- `auto_apply_roles` is an array of roles which will automatically apply this custom layout.
- `auto_apply_workflow_states` is an array of workflow states which will automatically apply this custom layout.
- `fields` is an array of fields which will be used for this custom layout.
- `fields.title` is the name of the field.
- `fields.title.title` is the new label of the field which will be displayed in the object edit mode.
- `fields.title.editable` is a boolean value which defines if the field is editable or not.
- `fields.title.visible` is a boolean value which defines if the field is visible or not.

## Custom Layout Modes

### Edit Mode

A custom layout with this setting will consist of all fields of the layout definition.
The provided settings will then overwrite the default settings.

```
Pimcore\Model\DataObject\TestObject:
    # layouts
    custom_layout:
        label: "My custom layout"
        mode: !php/const Basilicom\AdvancedCustomLayoutBundle\Model\CustomLayoutConfig::MODE_EDIT
        fields:
            title:
                title: "Overwritten Titel"
                editable: false
                visible: false
```

In this example we will keep all layout and field definitions but overwrite the title, lock and hide the field.

### Show Mode

A custom layout with this setting will hide all fields which are not defined in the configuration.
Additionally, the provided settings will overwrite the default settings regarding being editable or visible.

```
Pimcore\Model\DataObject\TestObject:
    # layouts
    custom_layout:
        label: "My custom layout"
        mode: !php/const Basilicom\AdvancedCustomLayoutBundle\Model\CustomLayoutConfig::MODE_SHOW
        fields:
            title:
                title: "Overwritten Titel"
                editable: false
```

In this example we will only show the title attribute which is not editable anymore.

## Utilization

### Updating Custom Layouts

To update the custom layouts you have to run the following command:

```
bin/console basilicom:custom-layouts:load
```

### For documentation purposes

In some cases you might want to generate an overview of all fields settings per layout.
In order to do so, you can run the following command:

```
bin/console basilicom:custom-layouts:create-excel
```

The file will be created in `project/var/bundles/AdvancedCustomLayouts/CustomLayouts.xlsx`.

## TODOs

- new mode to extend existing custom layouts, this is usefull if custom layouts are created by the user beforehand
    - allow to set layout reference to create a new custom layout by a reference to easily multiple custom layouts
- ObjectBricks are not supported yet
- add custom logic to also allow custom layouts for field collections
- hide layout elements as soon as all child-fields are hidden

# Author
Alexander Heidrich
