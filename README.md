# Aplicación en GestyMVC 3.2 (CLI version)

Atención: la aplicación está publicada con una estructura de carpetas distinta a la de Docker por falta de soporte en la configuración de `vhost`.

## Guía de instalación y despliegue en Docker

### Prerrequisitos

#### Docker Engine

En Windows y Mac, instalar "Docker Desktop". [https://www.docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop) en su última versión.
En Linux, instalar Docker CE y docker-compose [https://docs.docker.com/install/linux/docker-ce/centos/](https://docs.docker.com/install/linux/docker-ce/centos/)[https://docs.docker.com/compose/install/](https://docs.docker.com/compose/install/)

#### Puertos

Para que la aplicación corra correctamente, los puertos `31514` y `31515` deben estar libres en el host.

#### PHP - opcional

Puede resultar útil tener PHP instalado en la máquina host y añadir el ejecutable al path.

### Instalación

#### 1. Carga de credenciales

Las credenciales no están incluidas en GIT, por seguridad.
Para configurar las credenciales de sistemas debe crearse un archivo `.env` en la carpeta `./server/`.
El archivo debe tener este formato:

```env
COMPOSE_PROJECT_NAME=poolcp.alimerka.con-voz-de-mujer
MYSQL_DATABASE=poolcp.alimerka.con-voz-de-mujer
GESTYMVC_DEBUG=True
SMTP_PASSWORD=
AMAZON_S3_SECRET=
```

Se deberá añadir, también, un archivo llamado `auth.json` en la raíz del proyecto con la clave de autorización de **github** para el repositorio de **GestyMVC** en solo lectura.

```json
{
    "github-oauth": {
        "github.com": "xxx"
    }
}
```

La aplicación no funcionará sin esta correcta configuración.

#### 2. Arranque de la aplicación en Docker

Tras asegurarse de que Docker está corriendo, abrir un terminal en la carpeta del proyecto, en Windows, usar preferiblemente powershell.
Ejecutar los siguientes comandos y esperar:

```bash
cd server
docker-compose up --build
```

#### 3. Composer

Abrir un nuevo terminal y ejecutar:

```bash
docker exec -it poolcp.alimerka.con-voz-de-mujer_httpd sh -c "cd /var/www && /bin/bash"

composer install --no-dev
```

**Nota:** En modo desarrollo, no debe usarse la opción --no-dev o no funcionarán los scripts de unificado y minificado de estáticos.

#### 4. Base de datos

Abrir un nuevo terminal y ejecutar:

```bash
docker exec -it poolcp.alimerka.con-voz-de-mujer_httpd sh -c "cd /var/www && /bin/bash"
php app/manage.php --run="migrate" --mysql-elevated-user="root" --mysql-elevated-password="root"
```

#### 5. Tests

Abrir un nuevo terminal y ejecutar:

```bash
docker exec -it poolcp.alimerka.con-voz-de-mujer_httpd sh -c "cd /var/www && /bin/bash"
vendor/bin/phpunit --group production --exclude-group slow,unstable
```

#### 6. Finalización

Apagar los contenedores en el primer terminal (2) utilizando `CTRL+C` y, tras esperar al "gracefull exit" cerrar todos los terminales.

### Actualización

Tras realizar `git pull` del repositorio, se deberán seguir los mismos pasos que en la instalación, pero serán mucho más rápidos.

### Arranque para pruebas

Abrir un terminal en la carpeta del proyecto y ejecutar:

```bash
cd server
docker-compose up -d
```

Abrir el navegador y acceder a la página [http://localhost:31514](http://localhost:31514) .

### Reinicio

Con el proyecto arrancado, abrir un terminal y ejecutar:

```bash
docker exec -it poolcp.alimerka.con-voz-de-mujer_httpd sh -c "cd /var/www && /bin/bash"
php app/manage.php --run="flushmigrate" --mysql-elevated-user="root" --mysql-elevated-password="root"
```

Esto borrará y reinstalará la aplicación.

### Crear un primer usuario administrador

Con el proyecto arrancado, abrir un terminal y ejecutar:

```bash
docker exec -it poolcp.alimerka.con-voz-de-mujer_httpd sh -c "cd /var/www && /bin/bash"

php app/manage.php --run="console"
if(!defined('ROOT_URL')) define('ROOT_URL', 'http://localhost:31514/');
$User = MySQLModel::getInstance('User');
$user = $User->addNew(['email' => 'devnotifications@gestycontrol.com', 'password' => 'MiContrasena1', 'first_name' => 'Admin', 'last_name' => 'Admin', 'acl_profile_id' => 1]);
if(!$user) var_dump($User->errorsByField); else echo 'Success';
```

## Guía de instalación y despliegue fuera de Docker

### Generalidades

Seguir los mismos pasos que en Docker obviando la instalación de la máquina.

El comando ` docker exec -it poolcp.alimerka.con-voz-de-mujer_httpd sh -c "cd /var/www && /bin/bash" ` debe sustituirse donde corresponda por la apertura de un terminal en la carpeta del proyecto (`cd /var/www` o equivalente).

Los comandos `cd server`, `docker-compose up` y `docker-compose up --build` deben obviarse.

### Cron

Las tareas cron deberán configurarse manualmente en el crontab de la máquina con PHP:

```bash
* * * * * php "/var/www/app/public/index.php" --document_root="/var/www/app/public" --url="/cron/worker/"
```

Donde `/var/www/` deberá sustituirse por la ruta de instalación de la aplicación.

### Restructuración para publicación

Debe subirse manualmente las carpetas `app/public/static` y `app/public/content` a la raíz del servidor vía FTP.

### Notas

Todos los comandos deben ejecutarse con un terminal en la carpeta del proyecto, nunca en subcarpetas.
Aunque sea contra-intuitivo las credenciales se indicarán en el archivo `.env` de la carpeta `./server/`. Será el único archivo de esa carpeta que se utilice cuando se trabaje sin Docker.

### Permisos en carpetas

El usuario de apache así como el que ejecuta las tareas cron deben tener permisos de escritura en:
 
- /var/www/app/private/.logs/ (recursivo)
- /var/www/app/private/.cache/ (recursivo)
- /var/www/app/public/content/ (recursivo)
- directorio de uploads del servidor
- directorio de sesiones del servidor