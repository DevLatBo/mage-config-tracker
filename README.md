# Rastreo de Cambios en Configuracion desde el Admin.

Este módulo en su versión 1 está en su inicio para poder todavia seguir mejorándose 
e implementar nuevas características y/o features para brindar una mejor experiencia.

### Propósito
El módulo para Magento2 tiene por objetivo el poder registrar los cambios hechos en configuración 
dentro del admin, en el cual registra datos como la sección, que configuración de dicha sección que
se hizo, si fue verificado o revisado, cuando se configuró y se revisó.

### CARACTERISTICAS
 * [Base de Datos](#base-de-datos)
 * [Admin Grid](#admin-grid)
 * [Deteccion Cambios Config](#deteccion-cambios-config)
 * [Revision en Cambio de Configuracion](revision-en-cambio-de-configuracion)
 * [Bonus Info](bonus-info)

## Base de Datos
En este móodulo creamos la tabla en la base de datos llamada `devlat_settings_tracker` en el cual tiene 
como columnas creadas:
 * id (llave primaria).
 * section (Seccion del Config).
 * path (nombre del campo de texto del cual se asigna un valor config).
 * old_value (anterior valor del config).
 * new_value (nuevo valor del config).
 * verified (boolean el cual confirma si el cambio fue revisado).
 * configurated_at (Momento en el cual se hizo el cambio en el config).
 * checked (Momento en el cual se reviso el cambio).

Tomar en cuenta que checked tiene como default NULL debido a que cuando se crea 
este registro se debe esperar primero a que ese cambio sea revisado desde el admin.

Tomar en cuenta que se tiene el model, resourceModel y Collection para esta tabla que 
se lo requerirá para gestionar los datos de esta misma tabla.

## Admin Grid
El grid se encuentra ubicado en *Admin > System > Action Logs > Settings Track Logs*.
Se tuvo que crear un item en el menu declarandolo en el archivo **Devlat_Settings/etc/adminhtml/menu.xml**:
```xml
<add id="Devlat_Settings::config_track_logs"
             title="Settings Track Logs"
             translate="title"
             module="Devlat_Settings"
             sortOrder="30"
             parent="Magento_AsynchronousOperations::system_magento_logging"
             action="config/tracker"
             resource="Devlat_Settings::settings_track_logs"
        />
```
Se lo ubica directamente por medio del parent `parent="Magento_AsynchronousOperations::system_magento_logging"`.

Para consiguiente creamos el ui_component `devlat_settings_tracker_listing` declarado en 
el layout `config_tracker_index.xml`

```xml
    <referenceContainer name="content">
        <uiComponent name="devlat_settings_tracker_listing" />
    </referenceContainer>
```

Esta grid tiene lo siguiente:
 * **Filters**: Puedes filtrar por: id, section, verified, si fue revisado (checked) 
(puedes establecer un rango de tiempo) y lo cuando fue configurado (configurated at).
 * **Search by keyword**: Este campo fulltext es usado para filtrar los items de la grid por la seccion (section).
 * **Mass Action (dropdown Actions)**:Tenemos la accion para el mass delete, en el cual puedes borrar 
entre uno o mas items a borrar.
 * **Column Actions**: Contiene opciones de:
   * **Verify & Update**: El cual consiste en direccionar al usuario para ver los detalles del cambio de configuración.
   * **Delete**: Borra el item del tracking que se registró.

Cabe tambien declarar que tenemos opcion Columns en la parte superior para habilitar o deshabilitar columnas que
queremos visualizar.

El default view es para poder guardar la vista actual de la grid.

**IMPORTANTE:** Si hace un cambio en la grid o cambia el orden de las columnas de la misma, y no ve los cambios. Recuerde 
de que tiene que eliminar en la tabla `ui_bookmark` en la BD los items con namespace: `devlat_settings_tracker_listing`, 
luego proceda con un refresh a la página.

## Deteccion Cambios Config
Si queremos poder registrar los cambios que se hacen en el admin, necesitamos detectarlo mediante un 
plugin y para ello lo declaramos en el di.xml dentro de `etc/adminhtml`:
```xml
    <type name="Magento\Config\Model\Config">
        <plugin name="devlat_settings_save_plugin"
                type="Devlat\Settings\Plugin\Setting\Save"
                sortOrder="10" />
    </type>
```
Precisamente en este plugin apunta a la clase `Magento\Config\Model\Config` aquí 
empleamos el *before* al metodo Save (osea que antes de guardar la nueva configuracion 
detectamos el cambio y lo guardamos en nuestro tracking):
```php
public function beforeSave(
        Config $subject
    ): void
    {
       ...
}
```
Método Save no tiene ningun parámetro, por lo que es un método **void** ya que no tiene 
ningún input que estemos alterando.
Verificamos dentro de este plugin si la configuración se encuentra registrado primero en  
tabla core_config_data y si está, verifica si se ha realizado un cambio en su value, si es que 
se cambio de dato el config, se registra el cambio en tabla devlat_settings_tracker:

```php
$oldValue = $this->scopeConfig->getValue($configPath);
$newValue = $data['value'];
if($oldValue != $newValue) {
    try {
        /** @var Tracker $tracker */
        $tracker = $this->trackerFactory->create();
        $tracker->setSection($section);
        $tracker->setPath($configPath);
        $tracker->setOldValue($oldValue);
        $tracker->setNewValue($newValue);
        $tracker->setVerified(0);
        $this->trackerResource->save($tracker);

        $this->logger->info("Path value: {$configPath} tracked successfully.");

    } catch (\Exception $e) {
        $this->logger->info('Error: '. $$e->getMessage() );
    }
}
```
Es de esta forma se procede con el registro, éste plugin está ubicado en 
`Devlat\Settings\Plugin\Setting\Save` para que pueda revisarlo.

## Revision en Cambio de Configuracion
Para poder visualizar en detalle el cambio de configuracion, tenemos que crear un nuevo 
ui_component y lo tenemos declarado en el layout config_tracker_verify:

```xml
<referenceContainer name="content">
    <uiComponent name="devlat_settings_tracker_verify"/>
</referenceContainer>
```
EL ui_component `devlat_settings_tracker_verify` en todos sus datos a mostrar 
serán solo READONLY, no se efectuara un cambio en campos como *sections, path, configurated_at y verified*.

El único cambio que sera actualizado sera el de Verified que se realizará mediante una llamada en un ajax:
```js
$.ajax({
    url: "<?= $block->getUrl('config/ajax/verification')?>",
    type: 'POST',
    dataType: 'json',
    data: {
        id: <?= $block->getTrackerId() ?>,
        form_key: window.FORM_KEY
    },
    showLoader: false,
    success: function(response) {
        console.log(response);
    },
    error: function(xhr) {
        console.log("Error occurred.");
    }
})
```
Este código se encuentra en un template que fue incorporado por medio de un bloque 
dentro del ui_component:
```xml
<htmlContent name="group_title">
    <argument name="block" xsi:type="object">Devlat\Settings\Block\Adminhtml\Verification</argument>
</htmlContent>
```

De esta forma cada vez que el usuario ingresa a la página de "Verify & Update" el ajax se ejecuta.
El estado de _Verified_ se mantiene en su estado anterior confirmando al usuario de que ese item no fué 
revisado con anterioridad, por ejemplo:
Si el usuario ve por primera vez el item, el Verified mantiene su valor anterior **False**, si hace refresh 
el usuario pues Verified muestra el valor de **True** en _Verified_.

## Bonus Info
