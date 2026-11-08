# Rastreo de Cambios en Configuracion desde el Admin.

Este módulo en su versión 1.3.0 presenta mejoras en su desarrollo, hay nuevas características y/o features 
con miras a brindar una mejor experiencia en rastreo a cambios de configuración en el admin.

### Propósito
El módulo para Magento2 tiene por objetivo el poder registrar los cambios hechos en configuración 
dentro del admin, en el cual registra datos como la sección, que configuración de dicha sección que
se hizo, si fue verificado o revisado, quien hizo el cambio, que usuarios
hicieron la revisión , cuando se configuró y se revisó y cuantas veces se revisó el reporte.

### CARACTERISTICAS
 * [Base de Datos](#base-de-datos)
 * [Admin Grid](#admin-grid)
 * [Deteccion Cambios Config](#deteccion-cambios-config)
 * [Revision en Cambio de Configuracion](#revision-en-cambio-de-configuracion)
 * [Desinstalar Modulo](#desinstalar-modulo)
 * [Mejoras](#mejoras)
 * [Bonus Info](#bonus-info)

## Base de Datos
En este módulo creamos la tabla en la base de datos llamada `devlat_settings_tracker` en el cual tiene 
como columnas creadas:
 * id (llave primaria).
 * section (Seccion del Config).
 * path (nombre del campo de texto del cual se asigna un valor config).
 * configurated_by (guarda el id del usuario quien hizo el cambio de configuración).
 * old_value (anterior valor del config).
 * new_value (nuevo valor del config).
 * verified (booleanf el cual confirma si el cambio fue revisado).
 * verified_by (guarda en un json array sobre que usuarios y cuantas veces vieron los detalles del cambio).
 * configurated_at (Momento en el cual se hizo el cambio en el config).
 * checked (Momento en el cual se reviso el cambio).

Tomar en cuenta que checked tiene como default `0000-00-00 00:00:00` debido a que cuando se crea 
este registro se debe esperar primero a que ese cambio sea revisado desde el admin.

Se tiene el model, resourceModel y Collection para esta tabla que 
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
Para esta grid agregamos un archivo CSS en el cual podremos agregar algunos estilos:
```xml
<head>
    <css src="Devlat_Settings::css/admin_tracker_styling.css" />
</head>
```
Esta grid tiene lo siguiente:
 * **Filters**: Puedes filtrar por: id, section, verified, si fue revisado (checked) 
y entre que tiempo fue configurado (configurated at).
 * **Search by keyword**: Este campo fulltext es usado para filtrar los items de la grid por la seccion (section).
 * **Mass Action (dropdown Actions)**:Tenemos la accion para el mass delete, en el cual puedes borrar 
entre uno o mas items a borrar.
 * **Column Actions**: Contiene opciones de:
   * **Verify & Update**: El cual consiste en direccionar al usuario para ver los detalles del cambio de configuración.
   * **Delete**: Borra el item del tracking que se registró.

Cabe tambien declarar que tenemos opción Columns en la parte superior para habilitar o deshabilitar columnas que
queremos visualizar.

**Save View As...** es para poder guardar la vista actual de la grid.

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
Precisamente en este plugin apunta a la clase `Magento\Config\Model\Config`, en el plugin 
empleamos el *before* al metodo Save (osea que antes de guardar la nueva configuracion 
detectamos el cambio y lo guardamos en nuestro tracking):
```php
public function beforeSave(
        Config $subject
    ): void
    {

        $section = $subject->getSection();
        $groups = $subject->getGroups();

        $userId = (int)$this->adminSession->getUser()->getId();

        $this->logger->info("Tracking config updates...");
        foreach ($groups as $groupId => $groupData) {
            $this->processGroup("{$section}/{$groupId}", $groupData, $section, $userId);
        }
    }
```
Método Save no tiene ningun parámetro, por lo que es un método **void** ya que no tiene 
ningún input que estemos alterando.

Dentro del **beforeSave** se obtiene la sección (**getSection()**), los grupos enviados desde 
el formulario (**getGroups()**) y el ID del admin logueado (adminSession) luego se recorre 
los grupos y llama a processGroup("seccion/grupo", ...) para cada uno.

Dentro de todo lo que es el método processGroup() se recorre los campos del grupo.
Para cada configPath:

- Llama a isSensitiveField() para saber si es un campo password u obscure, o si está cifrado.
- Si se marcó "Use system value" (inherit):
    - oldValue = getStoredValue(), lo que hay en la base de datos. Si es null, no había nada guardado
      y no se registra nada.
    - newValue = getSystemDefault(), el valor de config.xml.
    - Si son distintos, se enmascaran (si el campo es sensible) y se llama a saveTracker().
- Si se envió un valor normal:
    - normalizeConfigValue() convierte los multiselect (arrays) en un string separado por comas.
    - Si es sensible y el valor llega como ******, el usuario no lo tocó y se salta.
    - oldValue sale de scopeConfig->getValue(). Si el campo es sensible, ambos valores se enmascaran.
    - isSameValue() compara los valores (los multiselect como listas). Si cambió, o si el campo es
      sensible, llama a saveTracker().
- Al final se llama a sí misma con cada subgrupo (groups anidados). Es recursiva.

En método saveTracker(): crea un Tracker con section, path, usuario, old/new y verified = 0, y lo guarda en
devlat_settings_tracker. Si algo falla, lo escribe en el log.

```php
$oldValue = $this->scopeConfig->getValue($configPath);
$newValue = $data['value'];
if($oldValue != $newValue) {
    try {
        /** @var Tracker $tracker */
        $tracker = $this->trackerFactory->create();
        $tracker->setSection($section);
        $tracker->setPath($configPath);
        $tracker->setConfiguratedBy($userId);
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
Es de esta forma como se procede con el registro, éste plugin está ubicado en 
`Devlat\Settings\Plugin\Setting\Save` para que pueda revisarlo.

## Revision en Cambio de Configuracion
En el DataProvider se obtiene los datos del item que se ha accedido para su verificación 
en el cual se obtiene los datos del  tracker item en class
`Devlat\Settings\Ui\DataProvider\Config`.

Para poder visualizar en detalle el cambio de configuración, tenemos que crear un nuevo 
ui_component y lo tenemos declarado en el layout config_tracker_verify:

```xml
<referenceContainer name="content">
    <uiComponent name="devlat_settings_tracker_verify"/>
</referenceContainer>
```
Como en la grid incluimos un archivo CSS, de igual forma lo incluimos 
para esta sección para agregar estilos:
```xml
<head>
    <css src="Devlat_Settings::css/admin_tracker_styling.css" />
</head>
```

El ui_component `devlat_settings_tracker_verify` no mostrará un form con inputs, 
solo READONLY, no se efectuara un cambio en campos como *sections, path, configurated_at y verified*.

El único cambio que será actualizado sera el de Verified que se realizará mediante una llamada en un ajax:
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
Éste código se encuentra en un template que fue incorporado por medio de un bloque 
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

## Desinstalar Modulo
El proceso de desinstalación del módulo es sencillo, se puede deshabilitarlo  y borrarlo directamente o 
puede usar el comando module:uninstall, pero no olvide tomar para éste módulo en consideracion lo siguiente:

* Se ha desarrollado un nuevo comando en el cual el usuario puede ejecutar:
`bin/magento devlat:settings:cleanup`. Hace que se elimine la tabla `devlat_settings_tracker` con toda su data.
* El  nuevo comando le pregunta al usuario para confirmar dicha acción con el fin de evitar una ejecución accidental.
* Si el usuario elimina la tabla, ya no será posible acceder a la página de la grid y redireccionará al home del admin.

## Mejoras
**Plugin de guardado (Plugin/Setting/Save.php)**
- Ahora solo registra un cambio si el valor realmente cambió (isSameValue()). En los multiselect
  compara la lista de valores.
- Los multiselect se guardan como texto separado por comas (normalizeConfigValue()).
- Los campos password o cifrados se detectan con isSensitiveField() y en el tracker se guardan como
  ******. Si el valor llega como asteriscos, se entiende que no se tocó y no se registra.
- Cuando se marca "Use system value" (inherit), se registra el cambio del valor guardado al default de
  config.xml (getStoredValue() y getSystemDefault()).
- Se procesan también los subgrupos anidados, porque processGroup() es recursivo.

**Verificación**
- La lógica de "marcar como verificado" se movió a un servicio nuevo, Model/Service/Verifier.php.
  Antes estaba duplicada en el controlador AJAX.
- El controlador Tracker/Verify ahora verifica al abrir la página, así que el contador se actualiza
  solo. Se quitaron el bloque y la plantilla de confirmación que había antes.
- En verified_by, el último usuario que verificó queda primero, y el formulario muestra "Last time
  checked by: …".
- Si el ID no existe, se lanza NoSuchEntityException y la respuesta JSON trae un mensaje de error
  claro.

**Base de datos**
- old_value y new_value pasaron de varchar(128) a text, para que los valores largos no se corten.
- Se completó db_schema_whitelist.json con configurated_by y verified_by.

**Estilos**
- El CSS suelto se reemplazó por view/adminhtml/web/css/source/_module.less, y se quitaron los <css>
  de los layouts.

## Bonus Info
Se usa un propio logger en donde se hace seguimiendo a las acciones 
que se realizan dentro de este módulo:
 * Al actualizar y confirmar de que se haya verificado o revisado uno de los items de la sección del Tracking.
 * Al registrar el cambio de configuración dentro del Tracking.

Se incluyó una forma de dar algo de estilo para el Verified tanto desde la grid 
como en el otro ui_component donde se visualiza la info sobre el cambio de configuración:

1. Primero para la grid en column Verified se define el component:
```xml
<column name="verified" component="Devlat_Settings/js/grid/columns/custom">
    <settings>
        <label translate="true">Verified</label>
        <options class="Magento\Config\Model\Config\Source\Yesno"/>
        <dataType>select</dataType>
        <filter>select</filter>
    </settings>
</column> 
```
Justo aca es donde se define el bodyTempl y el script se valida el status del item.

2. En el ui_component `devlat_settings_tracker_verify` como hicimos en la grid, 
Definimos nuestro template en field verified:
```xml
<field name="verified" formElement="input">
    <settings>
        <elementTmpl>Devlat_Settings/form/element/verified_bool</elementTmpl>
        <label translate="true">Verified</label>
    </settings>
</field> 
...
<field name="verified_by" formElement="input">
<settings>
    <elementTmpl>Devlat_Settings/form/element/list_users_verified</elementTmpl>
    <label translate="true">Verified By</label>
</settings>
</field>
...
```
_verified_bool_ es el template es donde definimos  el mensaje a mostrar dependiendo 
el estado de verified con un "Yes" o "No".

_list_user_verified_ es el template donde mostramos la lista de usuarios que realizaron una 
anterior verificación.

3. Para las colunas de *Verified by* y *Configurated by* se customizaron para dar una 
mejor presentación a la grid.
```xml
<column name="configurated_by"
        component="Devlat_Settings/js/grid/columns/configuratedBy"
        class="Devlat\Settings\Ui\Component\Listing\Column\ConfiguratedBy">
    <settings>
        <label translate="true">Configurated By</label>
    </settings>
</column>
...
<column name="verified_by"
        component="Devlat_Settings/js/grid/columns/verifiedBy"
        class="Devlat\Settings\Ui\Component\Listing\Column\VerifiedBy">
    <settings>
        <label translate="true">Verified By</label>
    </settings>
</column>
```
Tomar encuenta de que ambas columnas en primera instancia son IDs por lo cual 
mostramos el username que se los obtienen directo en `class` por consultas a la BD.

El `component` es útil para la validacion de los datos y el estilo al que se le 
proporcionará.


---

Espero que éste README sea de tu ayuda y de mayor
utilidad para el desarrollo y entendimiento del módulo.

### **Ing. Oscar Rolando Gamboa Acho.** 🇧🇴
