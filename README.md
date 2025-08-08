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

