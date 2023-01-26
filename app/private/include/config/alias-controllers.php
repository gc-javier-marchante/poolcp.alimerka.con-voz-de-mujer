<?php

use Symfony\Component\Yaml\Yaml;

$aliasControllerTypes = [
    ['path' => __DIR__ . DIRECTORY_SEPARATOR . 'crud.yml', 'class' => CRUDController::class, 'config' => 'crudControllers', 'prefix' => '', 'defaultRoute' => null],
    ['path' => __DIR__ . DIRECTORY_SEPARATOR . 'rest.yml', 'class' => RestController::class, 'config' => 'restControllers', 'prefix' => 'Rest', 'defaultRoute' => null],
];

foreach ($aliasControllerTypes as $aliasControllerType) {
    if (file_exists($aliasControllerType['path'])) {
        $config = Yaml::parseFile($aliasControllerType['path']);

        if (!empty($config['*'])) {
            unset($config['*']);
            $structureFiles = @array_diff(scandir(MODELS_PATH . 'structure'), ['.', '..']);

            if ($structureFiles) {
                foreach ($structureFiles as $structure_file) {
                    if (ends_with($structure_file, '.php')) {
                        $model_name = substr($structure_file, 0, -4);

                        if (!in_array($model_name, array_values($config))) {
                            $alias_class_name = Inflector::pascalcase(Inflector::pluralize(Inflector::snakeCase($model_name)));

                            if (!empty($aliasControllerType['prefix'])) {
                                $alias_class_name = $aliasControllerType['prefix'] . $alias_class_name;
                            }

                            $config[$alias_class_name] = $model_name;
                        }
                    }
                }
            }

            unset($model_name);
            unset($structureFiles);
        }

        if (!empty($config['!'])) {
            $disabledModels = $config['!'];
            unset($config['!']);

            if (!is_array($disabledModels)) {
                $disabledModels = [$disabledModels];
            }

            foreach ($disabledModels as $model_name) {
                $alias_class_name = array_search($model_name, $config);

                if ($alias_class_name) {
                    unset($config[$alias_class_name]);
                }
            }

            unset($disabledModels);
            unset($alias_class_name);
        }

        foreach ($config as $alias_class_name => $model_name) {
            if (
                !class_exists($alias_class_name . 'Controller')
                && !file_exists(INCLUDE_PATH . 'controllers' . DIRECTORY_SEPARATOR . $alias_class_name . 'Controller.php')
            ) {
                class_alias($aliasControllerType['class'], $alias_class_name . 'Controller');
            }
        }

        GestyMVC::setConfig($aliasControllerType['config'], $config);
    }

    unset($config);
    unset($aliasControllerType);
    unset($alias_class_name);
}
