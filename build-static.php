<?php

/**
 * Twig build script for vscode GestyMVC static files
 * @author Federico Luis Lescano Carroll <federico.lescano@gestycontrol.com>
 * @version 1.1.0 07/09/2020
 */

// Paths
$workspace = __DIR__ . DIRECTORY_SEPARATOR;
$src = $workspace . 'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;
$public = $workspace . 'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;
$public_static = $public .  'static' . DIRECTORY_SEPARATOR;
include_once($workspace . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

define('INSTALL_PATH', $workspace);
define('CORE_PATH', INSTALL_PATH . 'vendor' . DIRECTORY_SEPARATOR . 'gestycontrol' . DIRECTORY_SEPARATOR . 'gestymvc-cli' . DIRECTORY_SEPARATOR);
define('PRIVATE_PATH', INSTALL_PATH . 'app' . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR);
define('PUBLIC_PATH', $public);
define('LOGS_PATH', PRIVATE_PATH . '.logs' . DIRECTORY_SEPARATOR);
define('CACHE_PATH', PRIVATE_PATH . '.cache' . DIRECTORY_SEPARATOR);
define('ENVIRONMENTS_PATH', PRIVATE_PATH . 'environments' . DIRECTORY_SEPARATOR);
define('INCLUDE_PATH', PRIVATE_PATH . 'include' . DIRECTORY_SEPARATOR);
define('DB_PATH', PRIVATE_PATH . 'migrations' . DIRECTORY_SEPARATOR);
define('ROOT_PATH', INSTALL_PATH);
define('CSS_FOLDER', 'static/css');
define('JS_FOLDER', 'static/js');
include_once(CORE_PATH . 'GestyMVC.php');
GestyMVC::initialize();
Router::addRoute(':controller/:action/*', []);

use Symfony\Component\Yaml\Yaml;

/**
 * Gets all files in directory
 *
 * @param string $path Directory path
 * @return array
 */
function getAllFiles(string $path): array
{
    $list = [];
    $path = os_path($path);

    if (!is_dir($path)) {
        return $list;
    }

    if (!ends_with($path, DIRECTORY_SEPARATOR)) {
        $path .= DIRECTORY_SEPARATOR;
    }

    $basenames = scandir($path);

    foreach ($basenames as $basename) {
        if (in_array($basename, ['.', '..'])) {
            continue;
        }

        if (is_dir($path .  $basename) && !is_link($path . $basename)) {
            $list = array_merge($list, getAllFiles($path .  $basename));
        } else {
            $list[] = $path .  $basename;
        }
    }

    return $list;
}

/**
 * Switches directory separators to os-specific one.
 *
 * @param string $path
 * @return void
 */
function os_path(string $path): string
{
    return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
}

/**
 * Combines a js folder into a file.
 *
 * @param string $source
 * @param string $dest
 * @return void
 */
function combineJsFolderIntoFile(string $source, string $dest): void
{
    $isolate = false;
    $isolate_self = true;

    if (!file_exists($source)) {
        if (file_exists($source . '[]')) {
            $source = $source . '[]';
            $isolate = true;
            $isolate_self = false;
        } elseif (file_exists($source . '[')) {
            $source = $source . '[';
            $isolate = false;
            $isolate_self = false;
        } else {
            return;
        }
    }

    $contents = [];
    $dir = dir($source);
    @unlink($dest);

    while (false !== $basename = $dir->read()) {
        if ($basename == '.' || $basename == '..') {
            continue;
        }

        if (is_dir($source . DIRECTORY_SEPARATOR . $basename)) {
            combineJsFolderIntoFile($source . DIRECTORY_SEPARATOR . $basename, $source . DIRECTORY_SEPARATOR . $basename . '.tmp');
            $content = file_get_contents($source . DIRECTORY_SEPARATOR . $basename . '.tmp');
            @unlink($source . DIRECTORY_SEPARATOR . $basename . '.tmp');
        } else {
            $content = file_get_contents($source . DIRECTORY_SEPARATOR . $basename);
        }

        if ($isolate) {
            $content = '(function() { ' . PHP_EOL . $content . PHP_EOL . '})();';
        }

        $contents[$basename] = $content;
    }

    ksort($contents);
    $dir->close();

    if (!file_exists(dirname($dest))) {
        mkdir(dirname($dest), 0755, true);
    }

    $full_content = implode(PHP_EOL, $contents);

    if ($isolate_self) {
        $full_content = '(function() { ' . $full_content . '})();';
    }

    $full_content = str_replace("\r", "\n", $full_content);
    $full_content = str_replace("\n\n", "\n", $full_content);
    $full_content = str_replace('\'use strict\';' . "\n", '"use strict"' . "\n", $full_content);

    if (strpos($full_content, '"use strict";' . "\n") !== false) {
        $full_content = '"use strict";' . "\n" . str_replace('"use strict";' . "\n", "", $full_content);
        $full_content = str_replace("\n\n", "\n", $full_content);
    }

    file_put_contents($dest, $full_content);
}

/**
 * Combines a list of js folders into a file for each.
 *
 * @param array $basenames
 * @param array $list
 * @return void
 */
function combineJsFoldersByBasename(array $basenames, array &$list = []): void
{
    foreach ($basenames as $basename) {
        if (ends_with($basename, '[]')) {
            $basename = substr($basename, 0, -2);
        }

        combineJsFolderIntoFile(os_path(PUBLIC_PATH . 'static/sjs/' . $basename), os_path(PUBLIC_PATH . 'static/js/' . $basename));
        $list[] = os_path(PUBLIC_PATH . 'static/js/' . $basename);
    }
}
/**
 * Minifies a js file
 *
 * @param string $path
 * @return void
 */
function minifyJavascriptFile(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    file_put_contents($path, JSMin\JSMin::minify(file_get_contents($path)));
}

/**
 * Minifies a css file
 *
 * @param string $path
 * @return void
 */
function minifyCssFile(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    file_put_contents($path, Minify_CSSmin::minify(file_get_contents($path), ['preserveComments' => false]));
}

/**
 * Autoprefix css file
 *
 * @param string $path
 * @return void
 */
function autoPrefixCssFile(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    file_put_contents($path, (new Padaliyajay\PHPAutoprefixer\Autoprefixer(file_get_contents($path)))->compile());
}

/**
 * Minifies a list of static files
 *
 * @param string $root_path
 * @param array $paths
 * @param array $validExtensions
 * @return void
 */
function minifyStaticFiles(string $root_path, array $paths, array $validExtensions): void
{
    $root_path = os_path($root_path);

    if (!is_dir($root_path)) {
        return;
    }

    if (!ends_with($root_path, DIRECTORY_SEPARATOR)) {
        $root_path .= DIRECTORY_SEPARATOR;
    }

    foreach ($paths as $path) {
        if ($path == '*') {
            minifyStaticFiles($root_path, getAllFiles($root_path), $validExtensions);
            continue;
        }

        $is_valid_extension = false;

        foreach ($validExtensions as $valid_extension) {
            if (ends_with($path, $valid_extension)) {
                $is_valid_extension = true;
            }
        }

        if (!$is_valid_extension) {
            continue;
        }

        if (!starts_with($path, $root_path)) {
            $path = $root_path . $path;
        }

        if (ends_with($path, '.css')) {
            minifyCssFile($path);
        } elseif (ends_with($path, '.js')) {
            minifyJavascriptFile($path);
        }
    }
}

/**
 * Autoprefixes a list of css files
 *
 * @param string $root_path
 * @param array $paths
 * @return void
 */
function autoPrefixCssFiles(string $root_path, array $paths): void
{
    $root_path = os_path($root_path);

    if (!is_dir($root_path)) {
        return;
    }

    if (!ends_with($root_path, DIRECTORY_SEPARATOR)) {
        $root_path .= DIRECTORY_SEPARATOR;
    }

    foreach ($paths as $path) {
        if ($path == '*') {
            autoPrefixCssFiles($root_path, getAllFiles($root_path));
            continue;
        }

        if (!ends_with($path, '.css')) {
            continue;
        }

        if (!starts_with($path, $root_path)) {
            $path = $root_path . $path;
        }

        autoPrefixCssFile($path);
    }
}

/**
 * Minifies a html file
 *
 * @param string $path
 * @return string destination path
 */
function compileScssFile(string $path): ?string
{
    if (!file_exists($path)) {
        return null;
    }

    $scss = new ScssPhp\ScssPhp\Compiler();
    $scss->setImportPaths(os_path(PUBLIC_PATH . 'static/scss/'));
    $destination_path = substr(str_replace(os_path(PUBLIC_PATH . 'static/scss'), os_path(PUBLIC_PATH . 'static/css'), $path), 0, -4) . 'css';

    if (!file_exists(dirname($destination_path))) {
        mkdir(dirname($destination_path), 0755, true);
    }

    file_put_contents($destination_path, $scss->compile(file_get_contents($path)));

    return $destination_path;
}

/**
 * Minifies a list of html files
 *
 * @param string $root_path
 * @param array $paths
 * @param array $validExtensions
 * @param array $list
 * @return void
 */
function compileStaticFiles(string $root_path, array $paths, array $validExtensions, array &$list = []): void
{
    $root_path = os_path($root_path);

    if (!is_dir($root_path)) {
        return;
    }

    if (!ends_with($root_path, DIRECTORY_SEPARATOR)) {
        $root_path .= DIRECTORY_SEPARATOR;
    }

    foreach ($paths as $path) {
        if ($path == '*') {
            compileStaticFiles($root_path, getAllFiles($root_path), $validExtensions, $list);
            continue;
        }

        $is_valid_extension = false;

        foreach ($validExtensions as $valid_extension) {
            if (ends_with($path, $valid_extension)) {
                $is_valid_extension = true;
            }
        }

        if (!$is_valid_extension) {
            continue;
        }

        if (!starts_with($path, $root_path)) {
            $path = $root_path . $path;
        }

        if (ends_with($path, '.scss')) {
            $css_path = compileScssFile($path);

            if ($css_path) {
                $list[] = $css_path;
            }
        }
    }
}

function blackListCssFiles($path, $basenames)
{
    foreach ($basenames as $basename) {
        blackListCssFile(os_path($path . '/' . $basename));
    }
}

function blackListCssFile($path)
{
    $css_blacklist_path = PUBLIC_PATH . 'static/scss/css-blacklist.json';
    $css_whitelist_path = PUBLIC_PATH . 'static/scss/css-whitelist.json';
    $basename = basename($path);

    if (!file_exists($path) || !file_exists($css_blacklist_path)) {
        return;
    }

    $selectorBlacklist = @json_decode(file_get_contents($css_blacklist_path), true)[$basename];
    $selectorBlacklist = $selectorBlacklist ? $selectorBlacklist : [];
    $selectorWhitelist = @json_decode(file_get_contents($css_whitelist_path), true)[$basename];
    $selectorWhitelist = $selectorWhitelist ? $selectorWhitelist : [];

    foreach ($selectorWhitelist as $selector) {
        $index = array_search($selector, $selectorBlacklist);

        if ($index !== false && $index !== -1) {
            unset($selectorBlacklist[$index]);
        }
    }


    $css_content = str_replace(['}', '{', ';'], ["\n}\n", "\n{\n", ";\n"], str_replace("\r", "\n", file_get_contents($path)));
    $cssLines = explode("\n", $css_content);

    $media_query_started = false;
    $selector_deepness = 0;
    $selector_is_valid = false;
    $newLines = [];
    $accumulated = '';

    foreach ($cssLines as $line_number => $css_line) {
        $css_line = trim($css_line);

        if (!$css_line) {
            continue;
        }

        if ($selector_deepness > 0) {
            if ($selector_is_valid) {
                $newLines[] = $css_line;
            }

            if ($css_line == '}') {
                $selector_deepness--;
            } elseif ($css_line == '{') {
                $selector_deepness++;
            }
        } else {
            if ($css_line == '{') {
                if ($media_query_started) {
                    $newLines[] = $accumulated;
                    $newLines[] = '{';
                    $media_query_started = false;
                    $accumulated = '';
                } else {
                    $selector = $accumulated;

                    $selector_is_valid = starts_with($selector, '@');
                    $selector_deepness++;
                    $accumulated = '';

                    if (!$selector_is_valid) {
                        $whitelistedSelectors = [];
                        $selectors = preg_split("/\s*,\s*/", $selector);

                        foreach ($selectors as $subselector) {
                            $subselector = trim($subselector);

                            if (!in_array($subselector, $selectorBlacklist)) {
                                if (!in_array($subselector, $selectorBlacklist)) {
                                    $whitelistedSelectors[] = $subselector;
                                }
                            }
                        }

                        $selector = implode(', ', $whitelistedSelectors);

                        if ($selector) {
                            $selector_is_valid = true;
                        }
                    }

                    if ($selector_is_valid) {
                        $newLines[] = $selector;
                        $newLines[] = '{';
                    }
                }
            } elseif ($css_line == '}') {
                $newLines[] = '}';
            } else {
                if (starts_with($css_line, '@media')) {
                    $media_query_started = true;
                }

                $accumulated .= trim("\n" . $css_line);
            }
        }
    }

    file_put_contents($path, implode("\n", $newLines));
}

$yamlConfig = Yaml::parseFile(ROOT_PATH . 'build-static.yml');

$routes = [];
$blacklist = [
    PUBLIC_PATH . 'index.php'
];

if (!empty($yamlConfig['operations'])) {
    foreach ($yamlConfig['operations'] as $operation => $info) {
        switch ($operation) {
            case 'combineJsFolders':
                combineJsFoldersByBasename($info, $blacklist);
                break;

            case 'compileScss':
                compileStaticFiles(PUBLIC_PATH . 'static/scss', $info, ['.scss'], $blacklist);
                break;

            case 'minifyJavascript':
                minifyStaticFiles(PUBLIC_PATH . 'static/js', $info, ['.js']);
                break;

            case 'autoPrefixCss':
                autoPrefixCssFiles(PUBLIC_PATH . 'static/css', $info);
                break;

            case 'minifyCss':
                minifyStaticFiles(PUBLIC_PATH . 'static/css', $info, ['.css']);
                break;

            case 'minifyHtml':
                minifyStaticFiles(PUBLIC_PATH, $info, ['.html']);
                break;

            case 'blackListCss':
                blackListCssFiles(PUBLIC_PATH . 'static/css', $info);
                break;
        }
    }
}
